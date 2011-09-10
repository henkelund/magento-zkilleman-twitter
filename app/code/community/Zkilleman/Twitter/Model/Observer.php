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
 * Zkilleman_Twitter_Model_Observer
 *
 * @category   Zkilleman
 * @package    Zkilleman_Twitter
 * @author     Henrik Hedelund <henke.hedelund@gmail.com>
 */
class Zkilleman_Twitter_Model_Observer
{

    /**
     * Called from cron job to fetch new tweets and delete old ones
     *
     */
    public function fetchTweets()
    {
        $searchTerm = Mage::getStoreConfig('twitter/general/search_term');

        $tweetsSaved = false;
        foreach (Mage::getModel('twitter/tweet')
                ->requestTweets($searchTerm) as $tweet) {

            $tweet->save();
            $tweetsSaved = true;
        }

        if ($tweetsSaved) {
            // If there are new tweets we want to flush the block cache.
            // When no specific hash tag is set for the block, the general
            // search term is considered in the cache flushing
            Mage::app()->getLayout()
                ->createBlock('twitter/feed')
                    ->flushCache();
        }

        $lifetimeDays = (int) Mage::getStoreConfig('twitter/general/lifetime_days');
        if ($lifetimeDays > 0) {
            $tweets = Mage::getModel('twitter/tweet')->getCollection();
            $tweets->addFieldToFilter(
                'created_at',
                array('to' =>
                    date('Y-m-d H:i:s', time() - ($lifetimeDays*86400)))
                );
            $tweets->walk('delete');
        }
    }

}
