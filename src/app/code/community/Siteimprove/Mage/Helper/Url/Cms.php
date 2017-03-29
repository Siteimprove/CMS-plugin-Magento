<?php


class Siteimprove_Mage_Helper_Url_Cms extends Siteimprove_Mage_Helper_Url_Abstract
{
    /**
     * @param Mage_Cms_Model_Page $page
     *
     * @return array|null
     */
    public function getPageStoreIds(Mage_Cms_Model_Page $page)
    {
        $stores = $page->getData('stores');
        if ($stores === null) {
            $stores = $page->getResource()->lookupStoreIds($page->getId());
        }

        if (empty($stores)) {
            return null;
        }

        if (in_array(Mage_Core_Model_App::ADMIN_STORE_ID, $stores)) {
            /**
             * @var Varien_Db_Adapter_Interface $adapter
             * @var Mage_Cms_Model_Resource_Page $resource
             */
            $resource = $page->getResource();
            $adapter = $resource->getReadConnection();
            $select = $adapter->select()
                ->from(array('cp' => $resource->getMainTable()))
                ->join(
                    array('cps' => $resource->getTable('cms/page_store')),
                    'cp.page_id = cps.page_id',
                    array())
                ->where('cp.identifier = ?', $page->getIdentifier())
                ->where('cps.store_id NOT IN (?)', $stores)
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns('cps.store_id');

            $notStoreIds = $adapter->fetchCol($select);

            $stores = array();
            foreach (Mage::app()->getStores() as $storeId => $store) {
                if (in_array($storeId, $notStoreIds)) {
                    continue;
                }
                $stores[] = $storeId;
            }
        }

        return $stores;
    }

    /**
     * @param Mage_Cms_Model_Page $page
     *
     * @return string[] array
     *
     * @throws Exception
     */
    public function getAllPageUrls(Mage_Cms_Model_Page $page)
    {
        $stores = $this->getPageStoreIds($page);
        if (empty($stores)) {
            return array();
        }

        /** @var Mage_Core_Model_App_Emulation $appEmulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = null;
        $storeUrls = array();
        try {
            foreach ($stores as $storeId) {
                /**
                 * @todo Found out if good idea to test against $this->checkForRewritePathProblem()
                 */
                $pastEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);
                if ($initialEnvironmentInfo === null) {
                    $initialEnvironmentInfo = $pastEnvironmentInfo;
                }
                $storeUrls[$storeId] = $this->getPageUrl($page->getId());
            }
        } catch (Exception $e) {
            if ($initialEnvironmentInfo !== null) {
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
            throw $e;
        }
        if ($initialEnvironmentInfo !== null) {
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
        }

        return $storeUrls;
    }

    /**
     * @param string|Mage_Cms_Model_Page $page
     *
     * @return string
     */
    public function getPageUrl($page)
    {
        if ($page instanceof Mage_Cms_Model_Page) {
            $page = $page->getId();
        }
        return Mage::helper('cms/page')->getPageUrl($page);
    }
}
