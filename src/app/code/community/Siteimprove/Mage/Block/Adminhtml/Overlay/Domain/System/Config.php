<?php


class Siteimprove_Mage_Block_Adminhtml_Overlay_Domain_System_Config
    extends Siteimprove_Mage_Block_Adminhtml_Overlay_Domain
{
    /**
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        $store = $this->getRequest()->getParam('store');
        return Mage::app()->getStore($store);
    }
}
