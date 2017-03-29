<?php


class Siteimprove_Mage_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * @param null|string|bool|int|Mage_Core_Model_Store $store
     * @param bool                                       $checkForToken
     *
     * @return bool
     */
    public function isEnabled($store = null, $checkForToken = true)
    {
        $store = Mage::app()->getStore($store);
        return $this->_isEnabled($store, $checkForToken);
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        return Mage::getStoreConfig('siteimprove/general/token', Mage_Core_Model_App::ADMIN_STORE_ID) ?: null;
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param bool                  $checkForToken
     *
     * @return bool
     */
    public function _isEnabled(Mage_Core_Model_Store $store, $checkForToken = false)
    {
        if ($checkForToken && !$this->getToken()) {
            return false;
        }
        return Mage::getStoreConfigFlag('siteimprove/general/enabled', $store);
    }

    /**
     * Help getting stored used to fetching website scoped EAV attributes
     *
     * @param array|null $websites
     * @param boolean    $withDefaultWebsite With default website
     *
     * @return Mage_Core_Model_Store[]
     */
    public function getWebsitesDefaultStores(array $websites = null, $withDefault = false)
    {
        if ($websites === null) {
            $websites = Mage::app()->getWebsites($withDefault);
        }
        $stores = array();
        foreach ($websites as $website) {
            $website = Mage::app()->getWebsite($website);
            if (!$withDefault && $website->getId() == 0) {
                continue;
            }
            $defaultStore = $website->getDefaultStore();
            $stores[$website->getId()] = $defaultStore;
        }
        return $stores;
    }

    /**
     * @param array|null $websites
     * @param boolean    $withDefaultWebsite With default website
     *
     * @return int[]
     */
    public function getWebsitesDefaultStoreIds(array $websites = null, $withDefault = false)
    {
        return array_map(function(Mage_Core_Model_Store $store) {
            return (int)$store->getId();
        }, $this->getWebsitesDefaultStores($websites, $withDefault));
    }

    /**
     * @param int  $timeout
     *
     * @return string
     *
     * @throws Mage_Core_Exception|Zend_Http_Client_Exception
     */
    public function fetchToken($timeout = 10)
    {
        $client = new Varien_Http_Client(
            $this->getTokenEndpoint(),
            array('timeout' => $timeout)
        );

        $response = $client->request();
        if ($response->isSuccessful()) {
            $body = Mage::helper('core')->jsonDecode($response->getBody());
            return $body['token'];
        }

        throw new Zend_Http_Client_Exception("Token request returned status code '{$response->getStatus()}'");
    }

    /**
     * @return string
     */
    protected function getTokenEndpoint()
    {
        return 'https://my2.siteimprove.com/auth/token';
    }
}
