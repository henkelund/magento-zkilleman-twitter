<?php
/**
 * Zkilleman_Twitter
 *
 * Copyright (C) 2011 Henrik Hedelund (henke.hedelund@gmail.com)
 *
 * This file is part of Zkilleman_Twitter.
 *
 * Zkilleman_Twitter is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Zkilleman_Twitter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Zkilleman_Twitter.  If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP Version 5.1
 *
 * @category  Zkilleman
 * @package   Zkilleman_Twitter
 * @author    Henrik Hedelund <henke.hedelund@gmail.com>
 * @copyright 2011 Henrik Hedelund (henke.hedelund@gmail.com)
 * @license   http://www.gnu.org/licenses/lgpl.html GNU LGPL
 * @link      https://github.com/henkelund/magento-zkilleman-twitter
 */

/**
 * Zkilleman_Twitter_Block_Feed
 *
 * @category   Zkilleman
 * @package    Zkilleman_Twitter
 * @author     Henrik Hedelund <henke.hedelund@gmail.com>
 */
class Zkilleman_Twitter_Block_Feed extends Mage_Core_Block_Template
{

    const CACHE_KEY = 'TWITTER_FEED_BLOCK_';
    const CHECKED_CACHE_KEY = 'CHECKED_SHOULD_REQUEST_';

    const CONFIG_USE_PRODUCT_TAG = 'twitter/general/product_hashtag';
    const CONFIG_TAG_PREFIX = 'twitter/general/hashtag_prefix';
    const CONFIG_GENERAL_SEARCH_TERM = 'twitter/general/search_term';
    const CONFIG_UPDATE_INTERVAL = 'twitter/general/update_interval';
    const CONFIG_SHOW_RETWEETS = 'twitter/general/show_retweets';

    /**
     *
     * @var Zkilleman_Twitter_Model_Mysql4_Tweet_Collection
     */
    protected $_tweets = null;

    /**
     * Returns a collection of tweets. Should not be called before applying
     * a hash tag to this block because the collection will be cached and the
     * given hash tag won't be considered
     *
     * @return Zkilleman_Twitter_Model_Mysql4_Tweet_Collection
     */
    public function getTweets()
    {
        if ($this->_tweets === null) {
            $this->_tweets = Mage::getModel('twitter/tweet')->getCollection();
            $this->_tweets
                ->addFieldToFilter('search_term', $this->getSearchTerm())
                ->addFieldToFilter('is_hidden', 0)
                ->setOrder('id', 'DESC')
                ->setPageSize((int) $this->getMaxLength());

            if (!Mage::getStoreConfigFlag(self::CONFIG_SHOW_RETWEETS)) {
                $this->_tweets
                            ->addFieldToFilter('text', array('nlike' => 'RT %'));
            }
        }
        return $this->_tweets;
    }

    /**
     * Returns a hashtag is one is set, either explicitly stated or auto generated
     *
     * @return mixed
     */
    public function getHashTag()
    {
        $hashTagKey = 'hash_tag';
        if (!$this->hasData($hashTagKey)) {

            if (Mage::getStoreConfig(self::CONFIG_USE_PRODUCT_TAG) &&
                    $product = Mage::registry('current_product')) {

                $hashTag = $product->getData($hashTagKey) ?
                    $product->getData($hashTagKey) :
                    '#' . preg_replace('/[^a-zA-Z0-9]/', '',
                        Mage::getStoreConfig(self::CONFIG_TAG_PREFIX) .
                        $product->getUrlKey()
                    );
                $this->setData($hashTagKey, $hashTag);
            } else {
                $this->setData($hashTagKey, null);
            }
        }
        return $this->getData($hashTagKey);
    }

    /**
     * Returns this blocks hashtag if set, otherwise the general search term
     * is returned
     *
     * @return string
     */
    public function getSearchTerm()
    {
        return $this->getHashTag() ?
            $this->getHashTag() :
            Mage::getStoreConfig(self::CONFIG_GENERAL_SEARCH_TERM);
    }

    /**
     * Not guaranteed to be unique but probably will be
     *
     * @return string
     */
    public function getFeedIdentifier()
    {
        return strtoupper(substr(md5($this->getSearchTerm()), 0, 4));
    }

    /**
     *
     * @return bool
     */
    public function shouldRequestTweets()
    {
        if (!$this->hasData('should_request_tweets')) {

            $shouldRequestTweets = false;

            if (!$this->_recentlyCheckedShouldRequest()) {
                $lastTweet = $this->getTweets()->getFirstItem();
                if ($lastTweet) {
                    // Magento default timezone is UTC, as is Twitters
                    $tweetAge = time() - strtotime($lastTweet->getCreatedAt());
                    $shouldRequestTweets =
                        $tweetAge > $this->_getRequestInterval();
                    $this->_checkedShouldRequest();
                }
            }

            $this->setData('should_request_tweets', $shouldRequestTweets);
        }

        return (bool) $this->getData('should_request_tweets');
    }

    /**
     * Request interval in seconds.
     * Currently hard coded to 5 mins, like the cron job
     *
     * @return int
     */
    protected function _getRequestInterval()
    {
        return (int) Mage::getStoreConfig(self::CONFIG_UPDATE_INTERVAL);
    }

    /**
     * Check if we have recently checked if we should refresh this feed ;)
     *
     * @return bool
     */
    protected function _recentlyCheckedShouldRequest()
    {
        return Mage::app()->loadCache(
                self::CHECKED_CACHE_KEY . $this->getFeedIdentifier()) == true;
    }

    /**
     * Called when a refresh check is made for the current feed.
     * This is done so that we can remember if we have already done this recently
     */
    protected function _checkedShouldRequest()
    {
        Mage::app()->saveCache(
            // checkedShouldRequest = true
            true,
            // identify this feed
            self::CHECKED_CACHE_KEY . $this->getFeedIdentifier(),
            // cache should be cleaned by block cache refresh
            $this->getCacheTags(),
            // define "recent"
            $this->_getRequestInterval()
        );
    }

    /**
     * Flush the cache for this feed.
     * Both our "recent check" memory and the output cache it self
     */
    public function flushCache()
    {
        Mage::app()
            ->removeCache(self::CACHE_KEY . $this->getFeedIdentifier())
            ->removeCache(self::CHECKED_CACHE_KEY . $this->getFeedIdentifier());
    }

    /**
     * A unique string for this specific feed
     *
     * @return string
     */
    public function getCacheKey()
    {
        return self::CACHE_KEY . $this->getFeedIdentifier();
    }

    /**
     * Don't bother to cache if we are requesting new tweets (we don't want the
     * update javascript to be cached). Otherwise, cache indefinitely. Tweet
     * recievers are responsible for flushing the cache
     *
     * @return mixed
     */
    public function getCacheLifetime()
    {
        // null is for never, false is for infinity
        return $this->shouldRequestTweets() ? null : false;
    }

}