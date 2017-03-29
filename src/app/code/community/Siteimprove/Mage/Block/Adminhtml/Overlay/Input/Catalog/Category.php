<?php

class Siteimprove_Mage_Block_Adminhtml_Overlay_Input_Catalog_Category
    extends Siteimprove_Mage_Block_Adminhtml_Overlay_Input_Abstract
{
    /**
     * @return string|null
     */
    public function getInputUrl()
    {
        /**
         * @var Mage_Catalog_Model_Category         $category
         * @var Siteimprove_Mage_Helper_Url_Catalog $helper
         */
        $category = $this->getData('current_category') ?: Mage::registry('current_category');
        $helper = $this->helper('siteimprove/url_catalog');

        if ($category->isObjectNew() || !$category->getData('is_active')) {
            return null;
        }

        $inputUrl = null;
        $storeId = $category->getData('store_id');
        if (Mage::app()->getStore($storeId)->isAdmin()) {
            $urls = $helper->getAllCategoryUrls($category);
            foreach ($urls as $urlStoreId => $url) {
                if ($this->isEnabled($urlStoreId)) {
                    $inputUrl = $url;
                    break;
                }
            }
        } elseif ($helper->canShowCategoryInFrontend($category) && $this->isEnabled($storeId)) {
            $inputUrl = $helper->getCategoryUrl($category, $storeId);
        }

        return $inputUrl;
    }
}
