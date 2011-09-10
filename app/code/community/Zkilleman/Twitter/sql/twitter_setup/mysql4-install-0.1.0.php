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

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('zkilleman_tweet')};
CREATE TABLE {$this->getTable('zkilleman_tweet')} (
  `tweet_id`            int(10) NOT NULL auto_increment,
  `created_at`          datetime default NULL,
  `profile_image_url`   varchar(255) NOT NULL default '',
  `from_user_id_str`    varchar(20) NOT NULL default '',
  `id_str`              varchar(20) NOT NULL default '',
  `from_user`           varchar(64) NOT NULL default '',
  `text`                text,
  `to_user_id`          bigint(20) unsigned NOT NULL default 0,
  `metadata`            text,
  `id`                  bigint(20) unsigned NOT NULL default 0,
  `geo`                 text,
  `from_user_id`        bigint(20) unsigned NOT NULL default 0,
  `iso_language_code`   varchar(2) NOT NULL default 'en',
  `source`              varchar(255) NOT NULL default '',
  `to_user_id_str`      varchar(20) NOT NULL default '',
  `search_term`         varchar(255) NOT NULL default '',
  PRIMARY KEY (`tweet_id`),
  UNIQUE KEY `TWITTER_ID_SEARCH_TERM_UNIQUE_KEY` (`id`, `search_term`),
  KEY `TWITTER_FROM_USER_ID_KEY` (`from_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tweets';

    ");

$installer->endSetup();
