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
 * Zkilleman_Twitter_AjaxController
 *
 * @category   Zkilleman
 * @package    Zkilleman_Twitter
 * @author     Henrik Hedelund <henke.hedelund@gmail.com>
 */
class Zkilleman_Twitter_AjaxController extends Mage_Core_Controller_Front_Action
{

    /**
     * Called by the feed widget to check if there are new tweets
     */
    public function requestTweetsAction()
    {
        $searchTerm = $this->getRequest()->getParam('searchTerm');
        $tweetArray = array();

        $newTweets = Mage::getModel('twitter/tweet')->requestTweets($searchTerm);
        if ($newTweets->count() > 0) {

            $newTweets->walk('save');
            // If there are new tweets we want to flush the block cache
            // for that specific search term
            $this->getLayout()
                ->createBlock('twitter/feed')
                    ->setHashTag($searchTerm)
                    ->flushCache();
        }

        $newTweetsArray = $newTweets->toArray();

        $this->getResponse()
            ->setHeader('Content-type', 'application/json')
            ->setBody(
                Mage::helper('core')->jsonEncode($newTweetsArray['items'])
            );
    }

}