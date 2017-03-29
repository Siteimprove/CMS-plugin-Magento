<?php


class Siteimprove_Mage_Block_Adminhtml_Overlay_Domain extends Siteimprove_Mage_Block_Adminhtml_Overlay_Abstract
{
    protected $_defaultTemplate = 'siteimprove/overlay/domain.phtml';

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore();
    }

    /**
     * @return null|string
     */
    public function getDomain()
    {
        $store = $this->getStore();

        if ($store->isAdmin()) {
            return '';
        }

        if (!$this->isEnabled($store)) {
            return null;
        }

        $baseUrl = $store->getBaseUrl();
        if (!$baseUrl) {
            return '';
        }

        $fragments = parse_url($baseUrl);
        $scheme = $fragments['scheme'];
        $host   = $fragments['host'];
        $port   = isset($fragments['port']) ? ":{$fragments['port']}" : '';
        return "{$scheme}://{$host}{$port}/";
    }
}
