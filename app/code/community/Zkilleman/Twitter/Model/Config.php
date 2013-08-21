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

class Zkilleman_Twitter_Model_Config
{

    const API_BASE_URL = 'https://api.twitter.com/';

    const XML_PATH_CONSUMER_KEY    = 'twitter/general/consumer_key';
    const XML_PATH_CONSUMER_SECRET = 'twitter/general/consumer_secret';

    /**
     *
     * @param  string $path
     * @return string
     */
    public function getApiUrl($path = '')
    {
        return self::API_BASE_URL . ltrim($path, '/');
    }

    /**
     *
     * @param  Mage_Core_Model_Store $store
     * @return string
     */
    public function getConsumerKey($store = null)
    {
        return urlencode(Mage::getStoreConfig(
                    self::XML_PATH_CONSUMER_KEY, $store));
    }

    /**
     *
     * @param  Mage_Core_Model_Store $store
     * @return string
     */
    public function getConsumerSecret($store = null)
    {
        return urlencode(Mage::getStoreConfig(
                    self::XML_PATH_CONSUMER_SECRET, $store));
    }

    /**
     * Request a bearer token from the API
     *
     * @param  Mage_Core_Model_Store $store
     * @return boolean|string
     */
    public function getBearerToken($store = null)
    {
        $client = new Zend_Http_Client($this->getApiUrl('oauth2/token'));
        $client->setAuth(
                    $this->getConsumerKey($store), $this->getConsumerSecret($store));
        $client->setHeaders(
                'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        $client->setParameterPost('grant_type', 'client_credentials');
        $response = $client->request(Zend_Http_Client::POST);
        if (!$response->isSuccessful()) {
            return false;
        }
        $body = Mage::helper('core')->jsonDecode($response->getBody());
        if (is_array($body) && isset($body['access_token'])) {
            return $body['access_token'];
        }
        return false;
    }
}
