<?php

class Siteimprove_Mage_Block_Adminhtml_Overlay_Input_Catalog_Product
    extends Siteimprove_Mage_Block_Adminhtml_Overlay_Input_Abstract
{
    /**
     * @return string|null
     */
    public function getInputUrl()
    {
        /**
         * @var Mage_Catalog_Model_Product          $product
         * @var Siteimprove_Mage_Helper_Url_Catalog $helper
         */
        $product = $this->getData('current_product') ?: Mage::registry('current_product');
        $helper = $this->helper('siteimprove/url_catalog');

        if ($product->isObjectNew()) {
            return null;
        }

        $inputUrl = null;
        $storeId = $product->getData('store_id');
        if (Mage::app()->getStore($storeId)->isAdmin()) {
            $urls = $helper->getAllProductUrls($product);
            foreach ($urls as $urlStoreId => $url) {
                if ($this->isEnabled($urlStoreId)) {
                    $inputUrl = $url;
                    break;
                }
            }
        } elseif ($helper->canShowProductInFrontend($product) && $this->isEnabled($storeId)) {
            $inputUrl = $helper->getProductUrl($product, $storeId);
        }

        return $inputUrl;
    }
}
