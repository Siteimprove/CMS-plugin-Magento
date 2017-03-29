<?php

class Siteimprove_Mage_Block_Adminhtml_Overlay_Domain_Sales_Order
    extends Siteimprove_Mage_Block_Adminhtml_Overlay_Domain
{
    /**
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        $order = Mage::registry('current_order');
        $storeId = $order->getStoreId();
        return Mage::app()->getStore($storeId);
    }
}
