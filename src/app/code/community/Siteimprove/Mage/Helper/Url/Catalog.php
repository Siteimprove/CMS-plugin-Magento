<?php


class Siteimprove_Mage_Helper_Url_Catalog extends Siteimprove_Mage_Helper_Url_Abstract
{

    /**
     * @return bool
     */
    public function isUrlIndexRealTime()
    {
        $indexer = Mage::getSingleton('index/indexer');
        $process = $indexer->getProcessByCode('catalog_url');
        return $process->getMode() === Mage_Index_Model_Process::MODE_REAL_TIME;
    }

    /**
     * @return bool
     */
    public function notifyAboutBadIndexConfig()
    {
        return Mage::getStoreConfigFlag('siteimprove/catalog/notify_about_url_rewrite_config');
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param array|null                 $storeIds
     * @param array|null                 $params
     * @param bool                       $checkCanShowInFrontend
     * @param bool                       $checkRewriteProblems
     *
     * @return string[]
     */
    public function getAllProductUrls(
        Mage_Catalog_Model_Product $product,
        array $storeIds         = null,
        array $params           = null,
        $checkCanShowInFrontend = true,
        $checkRewriteProblems   = true
    ) {
        if ($params === null) {
            $params = array();
        } else {
            unset($params['_store']);
        }

        if ($storeIds === null) {
            $storeIds = array_keys(Mage::app()->getStores());
        } else {
            $storeIds = $this->filterAdminStoreId($storeIds);
        }

        if (!isset($params['_ignore_category'])) {
            $params['_ignore_category'] = true;
        }

        $storeUrls = array();
        $productWebsiteIds = $product->getWebsiteIds();
        $isSingleStoreMode = Mage::app()->isSingleStoreMode();
        foreach ($storeIds as $storeId) {
            $storeParams = $params;
            $storeProduct = clone $product;
            $storeProduct->setData('store_id', $storeId);

            if ($checkCanShowInFrontend) {
                if (!$isSingleStoreMode) {
                    $storeProduct->load($product->getId());
                }
                if (!$this->canShowProductInFrontend($storeProduct)) {
                    continue;
                }
            } elseif (!$isSingleStoreMode && !in_array($storeProduct->getStore()->getWebsiteId(), $productWebsiteIds)) {
                // If disabled in website the "_ignore_category" will be show in the url. We don't want that
                unset($storeParams['_ignore_category']);
            }

            if ($checkRewriteProblems) {
                if ($this->checkForRewritePathProblem($storeProduct->getStore())) {
                    continue;
                }
            }

            $storeUrls[$storeId] = $this->getProductUrl($product, $storeId, $storeParams, false);
        }

        return $storeUrls;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @param string|int                 $storeId
     * @param array                      $params
     * @param bool                       $clone
     *
     * @return string
     */
    public function getProductUrl(
        Mage_Catalog_Model_Product $product,
        $storeId,
        array $params = array(),
        $clone = true
    ) {
        if (!is_numeric($storeId)) {
            throw new InvalidArgumentException('Invalid argument. $storeId must be numeric');
        }
        if ($clone) {
            $product = clone $product;
        }
        $product->unsetData('request_path');
        $product->unsetData('url');
        $product->setData('store_id', $storeId);
        return $product->getUrlInStore($params);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     *
     * @return bool
     */
    public function canShowProductInFrontend(Mage_Catalog_Model_Product $product)
    {
        if ($product->isObjectNew()) {
            return false;
        }

        if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return false;
        }

        if (!in_array($product->getData('visibility'), $product->getVisibleInSiteVisibilities())) {
            return false;
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $store = $product->getStore();

            if ($store->isAdmin()) {
                return false;
            }

            if (!in_array($store->getWebsiteId(), $product->getWebsiteIds())) {
                return false;
            }

            if (!$store->getIsActive()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param array|null                  $storeIds
     * @param array|null                  $params
     * @param bool                        $checkCanShowInFrontend
     * @param bool                        $checkRewriteProblems
     * @param bool                        $cloneCategory
     *
     * @return array
     */
    public function getAllCategoryUrls(
        Mage_Catalog_Model_Category $category,
        array $storeIds         = null,
        array $params           = null,
        $checkCanShowInFrontend = true,
        $checkRewriteProblems   = true,
        $cloneCategory          = true
    ) {
        if ($params === null) {
            $params = array();
        }

        if ($cloneCategory) {
            $category = clone $category;
        }

        if ($storeIds === null) {
            $storeIds = $category->getStoreIds();
        } else {
            $storeIds = $this->filterAdminStoreId($storeIds);
        }

        if (!isset($params['_store_to_url'])) {
            $params['_store_to_url'] = true;
        }

        $storeUrls = array();
        foreach ($storeIds as $storeId) {
            if ($checkCanShowInFrontend) {
                if (!$this->_canShowCategoryInFrontend($category, $storeId)) {
                    continue;
                }
            }

            if ($checkRewriteProblems) {
                $store = Mage::app()->getStore($storeId);
                if ($this->checkForRewritePathProblem($store)) {
                    continue;
                }
            }
            $storeUrls[$storeId] = $this->getCategoryUrl($category, $storeId, $params, false);
        }

        return $storeUrls;
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param string|int                  $storeId
     * @param array                       $params
     * @param bool                        $clone
     *
     * @return string
     */
    public function getCategoryUrl(
        Mage_Catalog_Model_Category $category,
        $storeId,
        array $params = array(),
        $clone        = true
    ) {
        if (!is_numeric($storeId)) {
            throw new InvalidArgumentException('Invalid argument. $storeId must be numeric');
        }
        if ($clone) {
            $category = clone $category;
        }
        $category->unsetData('request_path');
        $category->unsetData('url');
        $category->setData('store_id', $storeId);
        if (!$category->getData('url_key')) {
            return $category->getUrlModel()->getCategoryUrl($category);
        }
        $params['_store'] = $storeId;
        $requestPath = $category->getRequestPath();
        return $category->getUrlModel()->getUrlInstance()->getDirectUrl($requestPath, $params);
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     *
     * @return bool
     */
    public function canShowCategoryInFrontend(Mage_Catalog_Model_Category $category)
    {
        return $this->_canShowCategoryInFrontend($category, $category->getStore()->getId());
    }

    /**
     * @param Mage_Catalog_Model_Category $category
     * @param int|string                  $store
     *
     * @return bool
     */
    protected function _canShowCategoryInFrontend(Mage_Catalog_Model_Category $category, $storeId) {

        if ($category->isObjectNew()) {
            return false;
        }

        $notSingleStoreMode = !Mage::app()->isSingleStoreMode();

        if ($notSingleStoreMode) {
            $catStoreId = $category->getData('store_id');
            if ($catStoreId && $catStoreId == $storeId) {
                $isActive = $category->getData('is_active');
            } else {
                $resource = $category->getResource();
                $isActive = $resource->getAttributeRawValue($category->getId(), 'is_active', $storeId);
            }
        } else {
            $isActive = $category->getData('is_active');
        }

        if (!$isActive) {
            return false;
        }

        if ($notSingleStoreMode) {
            $storeIds = $category->getStoreIds();
            if (!in_array($storeId, $storeIds)) {
                return false;
            }
        }

        $store = Mage::app()->getStore($storeId);

        if ($notSingleStoreMode) {
            if ($store->isAdmin()) {
                return false;
            }
        }

        if (!$store->getIsActive()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $stores
     *
     * @return array
     */
    protected function filterAdminStoreId(array $storeIds)
    {
        return array_filter($storeIds, function($storeId){
            return (int)$storeId !== Mage_Core_Model_App::ADMIN_STORE_ID;
        });
    }
}
