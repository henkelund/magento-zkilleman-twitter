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
class Zkilleman_Twitter_Block_Adminhtml_Twitter_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('twitterGrid');
        $this->setDefaultSort('tweet_id');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare grid collection
     *
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('twitter/tweet_collection');
        /* @var $collection Zkilleman_Twitter_Model_Mysql4_Tweet_Collection */
        $this->setCollection($collection);
        parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumn('tweet_id', array(
            'header'    => Mage::helper('twitter')->__('ID'),
            'width'     => '100px',
            'type'      => 'number',
            'align'     => 'left',
            'index'     => 'tweet_id',
        ));
        $this->addColumn('from_user', array(
            'header'    => Mage::helper('twitter')->__('User'),
            'width'     => '100px',
            'align'     => 'left',
            'index'     => 'from_user',
        ));
        $this->addColumn('search_term', array(
            'header'    => Mage::helper('twitter')->__('Search Term'),
            'width'     => '350px',
            'align'     => 'left',
            'index'     => 'search_term',
        ));
        $this->addColumn('text', array(
            'header'    => Mage::helper('twitter')->__('Text'),
            'align'     => 'left',
            'index'     => 'text',
        ));
        $this->addColumn('is_hidden', array(
            'header'    => Mage::helper('twitter')->__('Status'),
            'width'     => '75px',
            'index'     => 'is_hidden',
            'type'      => 'options',
            'options'   => array(
                0 => Mage::helper('twitter')->__('Visible'),
                1 => Mage::helper('twitter')->__('Hidden')
            ),
        ));
    }

    /**
     * Prepare mass action
     *
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('tweet_id');
        $this->getMassactionBlock()->setFormFieldName('tweet');
        $this->getMassactionBlock()->addItem('show', array(
             'label'   => Mage::helper('twitter')->__('Show'),
             'url'     => $this->getUrl('*/*/massShow')
        ));
        $this->getMassactionBlock()->addItem('hide', array(
             'label'   => Mage::helper('twitter')->__('Hide'),
             'url'     => $this->getUrl('*/*/massHide')
        ));
    }
}
