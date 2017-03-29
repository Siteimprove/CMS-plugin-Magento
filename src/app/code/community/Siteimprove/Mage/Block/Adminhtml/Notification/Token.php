<?php

class Siteimprove_Mage_Block_Adminhtml_Notification_Token extends Mage_Adminhtml_Block_Template
{
    /**
     * @return null|string
     */
    public function _toHtml()
    {
        if (!$this->isConfigAllowed()) {
            // Don't show any notice if the user don't have permissions to do anything about it
            return '';
        }

        /** @var Siteimprove_Mage_Helper_Data $helper */
        $helper = $this->helper('siteimprove');
        // Check if Siteimprove is enabled while no token is configured
        if ($helper->isEnabled(null, false) && $helper->getToken() === null) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isConfigAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/config/siteimprove');
    }

    /**
     * @return string
     */
    public function getConfigUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit/section/siteimprove');
    }
}
