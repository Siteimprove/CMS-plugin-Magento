<?php

/**
 * @see Mage_Index_Block_Adminhtml_Notifications
 */
class Siteimprove_Mage_Block_Adminhtml_Notification_Index extends Mage_Adminhtml_Block_Template
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

        /** @see Siteimprove_Mage_Helper_Data::isEnabled() */
        if ($this->helper('siteimprove')->isEnabled()) {
            /** @var Siteimprove_Mage_Helper_Url_Catalog $helper */
            $helper = $this->helper('siteimprove/url_catalog');
            if (!$helper->isUrlIndexRealTime() && $helper->notifyAboutBadIndexConfig()) {
                return parent::_toHtml();
            }
        }

        return '';
    }

    /**
     * @return bool
     */
    public function isConfigAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/index');
    }

    /**
     * @return string
     */
    public function getConfigUrl()
    {
        return $this->getUrl('adminhtml/process/list');
    }
}
