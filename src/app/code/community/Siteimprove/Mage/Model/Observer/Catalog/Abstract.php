<?php


abstract class Siteimprove_Mage_Model_Observer_Catalog_Abstract extends Siteimprove_Mage_Model_Observer_Abstract
{

    const OBJECT_SITEIMPROVE_STORES_CHANGED = 'notify_siteimprove_stores_that_has_changes';

    /**
     * @param array $a1
     * @param array $a2
     *
     * @return array
     */
    protected function mergeUnique(array $a1, array $a2)
    {
        return array_unique(array_merge($a1, $a2));
    }

    /**
     * @param Mage_Catalog_Model_Product|Mage_Catalog_Model_Category $object
     * @param array                                                  $hasChanges
     */
    protected function notifyAboutChangesAfterIndex(Mage_Catalog_Model_Abstract $object, array $hasChanges)
    {
        /** @var Siteimprove_Mage_Helper_Url_Catalog $helper */
        $helper = Mage::helper('siteimprove/url_catalog');
        if ($helper->isUrlIndexRealTime()) {
            $object->setData(self::OBJECT_SITEIMPROVE_STORES_CHANGED, $hasChanges);
        } elseif ($helper->notifyAboutBadIndexConfig()) {
            $session = Mage::getSingleton('siteimprove/session');
            $session->addWarning($helper->__('"Catalog URL Rewrites" need to be configured to "Update on Save" for Siteimprove to work optimally'));
            $session->addNotice($helper->__('Skipping Siteimprove check'));
        }
    }

    /**
     * @param array $storeIds
     *
     * @return array store ids that share the same website
     */
    protected function getStoreIdsWithSameWebsite(array $storeIds)
    {
        $relatedStores = array();
        foreach ($storeIds as $storeId) {
            $websiteIds = Mage::app()->getStore($storeId)->getWebsite()->getStoreIds();
            $relatedStores = $this->mergeUnique($relatedStores, $websiteIds);
        }
        return $relatedStores;
    }

    /**
     * @param int                                      $entity_id
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     *
     * @return int[]
     */
    public function storesNotUsingDefault($entity_id, Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        $table   = $attribute->getBackend()->getTable();
        $adapter = $attribute->getResource()->getReadConnection();
        $idField = $adapter->quoteIdentifier($attribute->getEntityIdField());

        $select = $adapter->select()
            ->from($table, 'store_id')
            ->where('store_id != ?', Mage_Core_Model_App::ADMIN_STORE_ID)
            ->where('entity_type_id = ?', $attribute->getEntityTypeId())
            ->where('attribute_id = ?', $attribute->getId())
            ->where("{$idField} = ?", $entity_id);

        return array_map(function($store_id) {
            return (int)$store_id;
        }, $adapter->fetchCol($select));
    }

    public function getEffectedScope(
        $entityId,
        Mage_Catalog_Model_Resource_Eav_Attribute $attr,
        array $checkStoreIds,
        array $websitesStoreIds
    ) {
        if ($attr->isScopeGlobal()) {
            $storesWithAttrChange = $checkStoreIds;
        } else {
            $storesNotUsingDefault = $this->storesNotUsingDefault($entityId, $attr);
            if ($attr->isScopeWebsite()) {
                $attrStoreIds = $websitesStoreIds;
                // Calculate which websites default store ids that is affected by the change
                $websitesDefaultStoresWithAttrChange = array_diff($attrStoreIds, $storesNotUsingDefault);
                // Find all stores that belongs to the changed websites
                $storesWithAttrChange = $this->getStoreIdsWithSameWebsite($websitesDefaultStoresWithAttrChange);
            } else { // Scope store
                $attrStoreIds = $checkStoreIds;
                // Calculate which store ids that is affected by the change
                $storesWithAttrChange = array_diff($attrStoreIds, $storesNotUsingDefault);
            }
        }

        return array_filter($storesWithAttrChange, function($storeId) use ($checkStoreIds) {
            return in_array($storeId, $checkStoreIds);
        });
    }

    /**
     * @param int|float|string|null|bool $value
     *
     * @return null|string|bool
     */
    protected function normalize($value)
    {
        if (is_numeric($value)) {
            return (string)$value;
        } elseif ($value === '' || $value === false) {
            return null;
        }
        return $value;
    }
}
