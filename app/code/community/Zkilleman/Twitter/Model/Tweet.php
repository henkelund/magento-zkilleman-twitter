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
 * Zkilleman_Twitter_Model_Tweet
 *
 * @category   Zkilleman
 * @package    Zkilleman_Twitter
 * @author     Henrik Hedelund <henke.hedelund@gmail.com>
 */
class Zkilleman_Twitter_Model_Tweet extends Mage_Core_Model_Abstract
{
    const CONFIG_SERVICE_URL = 'twitter/general/service_url';

    /**
     * Initialize this model
     */
    protected function _construct()
    {
        $this->_init('twitter/tweet');
    }

    /**
     *
     * @return Zkilleman_Twitter_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('twitter/config');
    }

    /**
     * Serialize complex types before save
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->setGeo(serialize($this->getGeo()));
        $this->setMetadata(serialize($this->getMetadata()));
    }

    /**
     * Unserialize complex types after load
     */
    protected function  _afterLoad()
    {
        parent::_afterLoad();
        $this->setGeo(unserialize($this->getGeo()));
        $this->setMetadata(unserialize($this->getMetadata()));
    }

    /**
     *
     * @param  array $params
     * @return boolean|array
     */
    protected function _getSearchResult($params = array())
    {
        $client = new Zend_Http_Client(
                        $this->_getConfig()->getApiUrl('1.1/search/tweets.json'));
        if ($token = $this->_getConfig()->getBearerToken()) {
            $client->setHeaders('Authorization', sprintf('Bearer %s', $token));
        }
        $client->setParameterGet($params);
        $response = $client->request(Zend_Http_Client::GET);
        if (!$response->isSuccessful()) {
            return false;
        }
        $body = Mage::helper('core')->jsonDecode($response->getBody());
        if (!is_array($body) ||
                !isset($body['statuses']) ||
                !is_array($body['statuses'])) {
            return false;
        }
        return $body;
    }

    /**
     * Downgrades API v1.1 result format to v1.0
     * for database structure compatibility.
     *
     * @param  array $apiData
     * @return array
     */
    protected function _prepareApiData($apiData)
    {
        $data = array();

        $commonKeys = array(
            'created_at', 'id_str', 'text', 'metadata', 'id', 'geo', 'source');
        foreach ($commonKeys as $key) {
            $data[$key] = $apiData[$key];
        }

        $data = array_merge($data, array(
            'profile_image_url' => $apiData['user']['profile_image_url'],
            'from_user_id_str'  => $apiData['user']['id_str'],
            'from_user'         => $apiData['user']['screen_name'],
            'to_user_id'        => $apiData['in_reply_to_user_id'],
            'from_user_id'      => $apiData['user']['id'],
            'iso_language_code' => $apiData['metadata']['iso_language_code'],
            'to_user_id_str'    => $apiData['in_reply_to_user_id_str']
        ));
        unset($data['metadata']['iso_language_code']);

        return $data;
    }

    /**
     * Request new messages from the Twitter Search API
     * https://dev.twitter.com/docs/api/1/get/search
     *
     * @param string $searchTerm
     * @return Varien_Data_Collection
     */
    public function requestTweets($searchTerm)
    {
        $tweets = new Varien_Data_Collection();

        $searchResult = $this->_getSearchResult(array(
            'q'           => $searchTerm,
            'result_type' => 'recent',
            'since_id'    => $this->_getMostRecentId($searchTerm)
        ));

        if ($searchResult) {
            foreach ($searchResult['statuses'] as $status) {

                $item = @$this->_prepareApiData($status);
                if (!is_array($item)) {
                    continue;
                }

                $tweet = Mage::getModel('twitter/tweet', $item);
                $tweet->setCreatedAt(
                    date('Y-m-d H:i:s',
                            strtotime($tweet->getCreatedAt())
                    )
                )->setSearchTerm($searchTerm);
                $tweets->addItem($tweet);
            }
        }

        return $tweets;
    }

    /**
     *
     * @return int
     */
    protected function _getMostRecentId($searchTerm = null)
    {
        $tweetCollection = Mage::getModel('twitter/tweet')->getCollection();
        if ($searchTerm) {
            $tweetCollection->addFieldToFilter('search_term', $searchTerm);
        }
        $tweetCollection->setOrder('id', 'DESC');
        $tweet = $tweetCollection->getFirstItem();
        return $tweet ? $tweet->getData('id') : 0;
    }
}
