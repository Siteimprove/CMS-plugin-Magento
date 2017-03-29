<?php

/**
 * @var Mage_Core_Model_Resource_Setup $installer
 * @var Varien_Db_Adapter_Interface    $conn
 */
$installer = $this;

$installer->startSetup();

// We know this script is only run when you expect a clean install, let's make sure it's one
$installer->deleteConfigData('siteimprove/general/token');
$installer->deleteConfigData('siteimprove/general/enabled');
$installer->deleteConfigData('siteimprove/catalog/notify_about_url_rewrite_config');

// Fetch token
try {
    $token = Mage::helper('siteimprove')->fetchToken(5);
    $installer->setConfigData('siteimprove/general/token', $token);
} catch (Exception $e) {
    // We don't want to stop the installation process for this
    Mage::logException($e);
}

$installer->endSetup();
