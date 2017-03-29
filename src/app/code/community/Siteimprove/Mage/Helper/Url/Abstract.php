<?php


abstract class Siteimprove_Mage_Helper_Url_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * @see Mage_Core_Model_Store::_updatePathUseRewrites()
     *
     * @param Mage_Core_Model_Store $store
     * @param bool                  $strict
     *
     * @return bool true if problem found
     */
    public function checkForRewritePathProblem(Mage_Core_Model_Store $store)
    {
        // Siteimprove won't crawl adminhtml so we will just ignore the check
        if ($store->isAdmin()) {
            return false;
        }

        // Is url rewrites disabled
        if (!$store->getConfig(Mage_Core_Model_Store::XML_PATH_USE_REWRITES)) {
            // If isCustomEntryPoint() gives null then we assume that somebody have forgotten give it a value
            if ($this->isCustomEntryPoint() === null) {
                // This only become a problem when SCRIPT_FILENAME is not "index.php" so in other cases just continue
                if (basename($_SERVER['SCRIPT_FILENAME']) !== 'index.php') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @see Mage_Core_Model_Store::_isCustomEntryPoint()
     *
     * @return bool|null
     */
    public function isCustomEntryPoint()
    {
        $value = Mage::registry('custom_entry_point');
        if ($value === null) {
            return $value;
        }
        return (bool)$value;
    }

    public function getAdminUrl($route='', $params=array())
    {
        return Mage::getModel('adminhtml/url')->getUrl($route, $params);
    }
}
