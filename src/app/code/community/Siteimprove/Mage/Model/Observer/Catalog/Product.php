<?php

/**
 * Handle product save and Siteimprove notification
 */
class Siteimprove_Mage_Model_Observer_Catalog_Product extends Siteimprove_Mage_Model_Observer_Catalog_Abstract
{

    /**
     * @param Varien_Event_Observer $observer
     */
    public function productPrepareSave(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product   $product */
        $product = $observer->getData('product');
        $product->setData(self::OBJECT_SITEIMPROVE_IS_NEW, $product->isObjectNew());
        $product->setData(self::OBJECT_SITEIMPROVE_PROCESS_KEY, true);
        if (!Mage::app()->isSingleStoreMode()) {
            $product->setOrigData('website_ids', $product->getResource()->getWebsiteIds($product));
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlProductSaveCommitAfter(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getData('product');
        if (!$product->getData(self::OBJECT_SITEIMPROVE_PROCESS_KEY)) {
            return;
        }
        $isNew = $product->getData(self::OBJECT_SITEIMPROVE_IS_NEW);
        $product->unsetData(self::OBJECT_SITEIMPROVE_IS_NEW);
        $product->unsetData(self::OBJECT_SITEIMPROVE_PROCESS_KEY);

        $allStoreIds = array_keys(Mage::app()->getStores());
        $storeId = $product->getStoreId();

        // If not global scope then only check for this store
        $isStoreScope = $storeId != Mage_Core_Model_App::ADMIN_STORE_ID;

        if (!$isStoreScope) {
            $storeId = null;
        }

        // Filter so we only work with attributes that is set to be monitored by Siteimprove
        $attributes = array_filter($product->getAttributes(), function(Varien_Object $attr) {
            return (bool)$attr->getData('is_monitored_by_siteimprove');
        });

        /** @var int[] $hasChanges store ids with changes */
        $hasChanges = array();

        // You can not fetch a value by website id so we need the default store for each website
        $websitesStoreIds = $this->helper()->getWebsitesDefaultStoreIds();
        $entityId = $product->getId();
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attr */
        foreach ($attributes as $attr) {
            $attrCode = $attr->getAttributeCode();
            if ($this->adminhtmlProductAttrHasChangesAfterSaveCommit($product, $attrCode, $storeId)) {
                if ($isStoreScope) {
                    $hasChanges = array($storeId);
                    // We are only working on one store scope so no need to check any more attributes
                    break;
                }

                $storesWithAttrChange = $this->getEffectedScope($entityId, $attr, $allStoreIds, $websitesStoreIds);
                $hasChanges = $this->mergeUnique($hasChanges, $storesWithAttrChange);

                if (count(array_diff($allStoreIds, $hasChanges)) === 0) {
                    // All store ids is confirmed to have changes so no need to test the rest
                    break;
                }
            }
        }

        $notifyAfterIndex = false;
        if (!Mage::app()->isSingleStoreMode()) {
            $websiteIds = $product->getData('website_ids');
            $websiteIdsOrig = $product->getOrigData('website_ids');
            $newlyAddedWebsites = array_diff($websiteIds, $websiteIdsOrig);
            if (count($newlyAddedWebsites)) {
                $notifyAfterIndex = true;
            }
            $newlyAddedStores = array();
            foreach ($newlyAddedWebsites as $newlyAddedWebsite) {
                $websiteStoreIds = Mage::app()->getWebsite($newlyAddedWebsite)->getStoreIds();
                $newlyAddedStores = array_merge($newlyAddedStores, $websiteStoreIds);
            }
            $hasChanges = $this->mergeUnique($hasChanges, $newlyAddedStores);
        }

        if (!$hasChanges) {
            // If empty array
            return;
        }

        if ($isNew || $product->getOrigData('url_key') !== $product->getData('url_key')) {
            $notifyAfterIndex = true;
        }

        if ($notifyAfterIndex) {
            $this->notifyAboutChangesAfterIndex($product, $hasChanges);
        } else {
            $this->notifyAboutProductChanges($product, $hasChanges);
        }
    }

    public function adminhtmlControllerActionPostDispatch(Varien_Event_Observer $observer = null)
    {
        if ($product = Mage::registry('current_product')) {
            /** @var Mage_Catalog_Model_Product $product */
            $storesHasChanges = $product->getData(self::OBJECT_SITEIMPROVE_STORES_CHANGED);
            if ($storesHasChanges !== null) {
                $product->unsetData(self::OBJECT_SITEIMPROVE_STORES_CHANGED);
                $this->notifyAboutProductChanges($product, $storesHasChanges);
            }
        }
    }

    protected function notifyAboutProductChanges(Mage_Catalog_Model_Product $product, array $storeIds)
    {
        /** @var Siteimprove_Mage_Helper_Url_Catalog $helper */
        $urlHelper = Mage::helper('siteimprove/url_catalog');
        $storeUrls = $urlHelper->getAllProductUrls($product, $storeIds);

        if (empty($storeUrls)) {
            return;
        }

        $this->notifySiteimprove($storeUrls);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string                     $attrCode
     * @param string|int                 $storeId
     *
     * @return bool
     */
    protected function adminhtmlProductAttrHasChangesAfterSaveCommit(
        Mage_Catalog_Model_Product $product,
        $attrCode,
        $storeId
    ) {
        if (in_array($attrCode, array('created_at', 'updated_at'))) {
            return false;
        }
        $value = $product->getData($attrCode);
        $origValue = $product->getOrigData($attrCode);

        // If not admin store then check if value is switching to use default and if yes
        // then get the real value from "AttributeDefaultValue"
        if ($storeId && $value === false) {
            $value = $product->getAttributeDefaultValue($attrCode);
        }

        $value = $this->normalize($value);
        $origValue = $this->normalize($origValue);

        return $value !== $origValue;
    }
}
