<?php


class Siteimprove_Mage_Model_Session extends Mage_Adminhtml_Model_Session
{

    const CHECK_URLS_COLLECTION_KEY = 'siteimprove_check_url_collection';

    /**
     * @param $url
     *
     * @return $this
     */
    public function addCheckUrl($url, $storeId)
    {
        $urlModel = Mage::getModel('siteimprove/url', array($url, $storeId));
        $this->getCheckUrls()->add($urlModel);
        return $this;
    }

    /**
     * @param bool $clear
     *
     * @return Siteimprove_Mage_Model_Url_Collection
     */
    public function getCheckUrls($clear = false)
    {
        if (!$this->getData(self::CHECK_URLS_COLLECTION_KEY)) {
            $this->setData(self::CHECK_URLS_COLLECTION_KEY, Mage::getModel('siteimprove/url_collection'));
        }

        /** @var Siteimprove_Mage_Model_Url_Collection $sessionUrlCollection */
        $sessionUrlCollection = $this->getData(self::CHECK_URLS_COLLECTION_KEY);
        if ($clear) {
            $urlCollection = clone $sessionUrlCollection;
            $sessionUrlCollection->clear();
            return $urlCollection;
        }
        return $sessionUrlCollection;
    }
}
