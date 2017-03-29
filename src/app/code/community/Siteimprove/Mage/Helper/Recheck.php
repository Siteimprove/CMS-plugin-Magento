<?php


class Siteimprove_Mage_Helper_Recheck extends Mage_Core_Helper_Abstract
{
    /**
     * @param bool $clear
     *
     * @return string[]
     */
    public function getUrlsToFrontend($clear = true)
    {

        /** @var Siteimprove_Mage_Model_Url[] $urlModels */
        $urlModels = $this->filterUrls($this->getCheckUrls($clear)->getUrls());

        /** @var string[] $urls */
        $urls = array_map(function (Siteimprove_Mage_Model_Url $urlModel) {
            return $urlModel->getUrl();
        }, $urlModels);

        $urls = array_unique($urls);

        $urls = array_values($urls);

        return $urls;
    }

    /**
     * @param bool $clear
     *
     * @return Siteimprove_Mage_Model_Url_Collection
     */
    public function getCheckUrls($clear = true)
    {
        $session = $this->_getSession();
        $urls = $session->getCheckUrls($clear);
        return $urls;
    }

    /**
     * @param Siteimprove_Mage_Model_Url[] $urls
     *
     * @return Siteimprove_Mage_Model_Url[]
     */
    protected function filterUrls(array $urls)
    {
        $storeStatus = array();
        $helper = Mage::helper('siteimprove');
        return array_filter($urls, function (Siteimprove_Mage_Model_Url $url) use ($storeStatus, $helper) {
            $storeId = $url->getStoreId();
            if (!isset($storeStatus[$storeId])) {
                $storeStatus[$storeId] = $helper->isEnabled($storeId, false);
            }
            return $storeStatus[$storeId];
        });
    }

    /**
     * @return Siteimprove_Mage_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('siteimprove/session');
    }
}
