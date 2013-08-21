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

class Zkilleman_Twitter_Adminhtml_TwitterController
    extends Mage_Adminhtml_Controller_Action
{

    /**
     * Index action
     */
    public function indexAction()
    {
        $helper = Mage::helper('twitter');
        /* @var $helper Zkilleman_Twitter_Helper_Data */
        $this->loadLayout();
        $this->_setActiveMenu('cms/twitter');
        $this->_addBreadcrumb($helper->__('CMS'), $helper->__('CMS'));
        $this->_addBreadcrumb($helper->__('Twitter'), $helper->__('Twitter'));
        $this->_title($helper->__('CMS'))->_title($helper->__('Twitter'));
        $this->renderLayout();
    }

    /**
     *
     * @param array $tweets
     * @param bool  $isHidden
     */
    protected function _updateVisibility($tweets, $isHidden)
    {
        $tweets = array_map('intval', array_filter($tweets, 'is_numeric'));

        if (count($tweets) > 0) {
            $collection = Mage::getResourceModel('twitter/tweet_collection');
            /* @var $collection Zkilleman_Twitter_Model_Mysql4_Tweet_Collection */
            $collection->addFieldToFilter('tweet_id',  array('in'  => $tweets));
            $collection->addFieldToFilter('is_hidden', array('neq' => $isHidden));

            $updated = 0;
            foreach ($collection as $tweet) {
                /* @var $tweet Zkilleman_Twitter_Model_Tweet */
                $tweet->setIsHidden($isHidden)->save();
                ++$updated;
            }
            $this->_getSession()->addSuccess(
                    Mage::helper('twitter')->__(
                            '%d tweet(s) changed visibility', $updated));
        }
    }

    /**
     * Mass Hide action
     *
     */
    public function massHideAction()
    {
        $this->_updateVisibility((array) $this->getRequest()->getPost('tweet'), 1);
        $this->_redirect('*/*/index');
    }

    /**
     * Mass Show action
     *
     */
    public function massShowAction()
    {
        $this->_updateVisibility((array) $this->getRequest()->getPost('tweet'), 0);
        $this->_redirect('*/*/index');
    }
}
