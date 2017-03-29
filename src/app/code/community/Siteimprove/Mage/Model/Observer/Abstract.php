<?php


class Siteimprove_Mage_Model_Observer_Abstract
{
    const OBJECT_SITEIMPROVE_PROCESS_KEY = 'process_and_notify_siteimprove_change_made';
    const OBJECT_SITEIMPROVE_IS_NEW      = 'siteimprove_process_item_is_new_in_the_system';

    /**
     * @return Siteimprove_Mage_Helper_Data
     */
    protected function helper()
    {
        return Mage::helper('siteimprove');
    }

    /**
     * @param string[]        $storeUrls
     *
     */
    protected function notifySiteimprove(array $storeUrls) {
        /** @var Siteimprove_Mage_Helper_Data $helper */
        $helper = $this->helper();

        /** @var Siteimprove_Mage_Model_Session $session */
        $session = Mage::getSingleton('siteimprove/session');
        try {
            foreach ($storeUrls as $storeId => $url) {
                $session->addCheckUrl($url, $storeId);
            }
        } catch (Exception $e) {
            $session->addWarning($helper->__('Exception raised while trying to notify Siteimprove about changes'));
            Mage::logException($e);
            if (Mage::getIsDeveloperMode()) {
                throw $e;
            }
        }
    }
}
