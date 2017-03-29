<?php


class Siteimprove_Mage_Block_Adminhtml_Overlay_Input_Cms_Page
    extends Siteimprove_Mage_Block_Adminhtml_Overlay_Input_Abstract
{
    /**
     * @return string|null
     */
    public function getInputUrl()
    {
        /**
         * @var Mage_Cms_Model_Page             $page
         * @var Siteimprove_Mage_Helper_Url_Cms $helper
         */
        $page = $this->getData('cms_page') ?: Mage::registry('cms_page');
        $helper = $this->helper('siteimprove/url_cms');

        if ($page->isObjectNew()) {
            return null;
        }

        $inputUrl = null;
        $urls = $helper->getAllPageUrls($page);
        foreach ($urls as $urlStoreId => $url) {
            if ($this->isEnabled($urlStoreId)) {
                $inputUrl = $url;
                break;
            }
        }

        return $inputUrl;
    }
}
