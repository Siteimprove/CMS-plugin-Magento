<?php

/**
 * Handle category save, Siteimprove notification and category overlay rendering
 */
class Siteimprove_Mage_Model_Observer_Catalog_Category extends Siteimprove_Mage_Model_Observer_Catalog_Abstract
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function catalogCategoryPrepareSave(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Category $category */
        $category = $observer->getData('category');
        $category->setData(self::OBJECT_SITEIMPROVE_IS_NEW, $category->isObjectNew());
        $category->setData(self::OBJECT_SITEIMPROVE_PROCESS_KEY, true);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlCategorySaveCommitAfter(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Category $category */
        $category = $observer->getData('category');
        if (!$category->getData(self::OBJECT_SITEIMPROVE_PROCESS_KEY)) {
            return;
        }
        $isNew = $category->getData(self::OBJECT_SITEIMPROVE_IS_NEW);
        $category->unsetData(self::OBJECT_SITEIMPROVE_IS_NEW);
        $category->unsetData(self::OBJECT_SITEIMPROVE_PROCESS_KEY);

        $allStoreIds = $category->getStoreIds();
        $storeId = $category->getStoreId();

        // If not global scope then only check for this store
        $isStoreScope = $storeId != Mage_Core_Model_App::ADMIN_STORE_ID;

        $globalValues = null;
        if ($isStoreScope) {
            /** @var Mage_Catalog_Model_Category $globalValues */
            $globalValues = Mage::getModel('catalog/category')->setData('store_id', 0);
            $globalValues->load($category->getId());
        }

        /** @var int[] $hasChanges store ids with changes */
        $hasChanges = array();

        // You can not fetch a value by website id so we need the default store for each website
        $websitesStoreIds = $this->helper()->getWebsitesDefaultStoreIds();
        $entityId = $category->getId();
        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attr */
        foreach ($category->getAttributes() as $attr) {
            $attrCode = $attr->getAttributeCode();
            if ($this->adminhtmlCategoryAttrHasChangesAfterSaveCommit($category, $globalValues, $attrCode)) {
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


        if (!$hasChanges) {
            return;
        }

        if ($isNew || $category->getOrigData('url_key') !== $category->getData('url_key')) {
            $this->notifyAboutChangesAfterIndex($category, $hasChanges);
        } else {
            $this->notifyAboutCategoryChanges($category, $hasChanges);
        }
    }

    public function adminhtmlControllerActionPostDispatch(Varien_Event_Observer $observer = null)
    {
        if ($category = Mage::registry('current_category')) {
            /** @var Mage_Catalog_Model_Category $category */
            $storesHasChanges = $category->getData(self::OBJECT_SITEIMPROVE_STORES_CHANGED);
            if ($storesHasChanges !== null) {
                $category->unsetData(self::OBJECT_SITEIMPROVE_STORES_CHANGED);
                $this->notifyAboutCategoryChanges($category, $storesHasChanges);
            }
        }
    }

    protected function notifyAboutCategoryChanges(Mage_Catalog_Model_Category $category, array $storeIds)
    {
        /** @var Siteimprove_Mage_Helper_Url_Catalog $helper */
        $urlHelper = Mage::helper('siteimprove/url_catalog');
        $storeUrls = $urlHelper->getAllCategoryUrls($category, $storeIds);

        if (empty($storeUrls)) {
            return;
        }

        $this->notifySiteimprove($storeUrls);
    }

    /**
     * @param Mage_Catalog_Model_Category      $category
     * @param Mage_Catalog_Model_Category|null $globalValues
     * @param string                           $attrCode
     *
     * @return bool
     */
    protected function adminhtmlCategoryAttrHasChangesAfterSaveCommit(
        Mage_Catalog_Model_Category $category,
        Mage_Catalog_Model_Category $globalValues = null,
        $attrCode
    ) {
        if (in_array($attrCode, array('created_at', 'updated_at'))) {
            return false;
        }

        $value = $category->getData($attrCode);
        $origValue = $category->getOrigData($attrCode);
        if ($globalValues && $value === false) {
            $value = $globalValues->getData($attrCode);
        }

        $value = $this->normalize($value);
        $origValue = $this->normalize($origValue);

        return $value !== $origValue;
    }

    /**
     * @see Mage_Adminhtml_Block_Catalog_Category_Tab_Attributes::_prepareForm()
     *
     * Attach Siteimprove to layout
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlCategoryEditPrepareForm(Varien_Event_Observer $observer)
    {
        /**
         * @var Varien_Data_Form            $form
         * @var Mage_Catalog_Model_Category $category
         * @var Mage_Core_Model_Layout      $layout
         */
        $form     = $observer->getData('form');
        $category = $form->getDataObject();
        $layout   =  Mage::app()->getLayout();


        $block = $layout->createBlock(
            'siteimprove/adminhtml_overlay_input_catalog_category',
            'siteimprove.overlay.input.catalog_category',
            array('current_category' => $category)
        );

        $form->addField('siteimprove_overlay', 'hidden', array(
            'disabled' => true,
            'after_element_html' => $block->toHtml(),
        ));

        // If page is dynamic loaded with ajax fetch the "check_urls" block from the layout and add it to the form
        if (Mage::app()->getRequest()->isAjax()) {
            $checkUrlBlock = $layout->getBlock('siteimprove.check_urls');
            if ($checkUrlBlock) {
                $form->addField('siteimprove_check_url', 'hidden', array(
                    'disabled' => true,
                    'after_element_html' => $checkUrlBlock->toHtml(),
                ));
            }
        }
    }
}
