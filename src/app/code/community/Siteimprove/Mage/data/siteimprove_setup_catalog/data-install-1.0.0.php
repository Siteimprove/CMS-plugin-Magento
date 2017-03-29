<?php

/**
 * @var Mage_Catalog_Model_Resource_Setup $installer
 * @var Varien_Db_Adapter_Interface       $conn
 */
$installer = $this;

$installer->startSetup();

// General
$installer->updateAttribute('catalog_product', 'name',        'is_monitored_by_siteimprove', '1');
$installer->updateAttribute('catalog_product', 'status',      'is_monitored_by_siteimprove', '1');
$installer->updateAttribute('catalog_product', 'url_key',     'is_monitored_by_siteimprove', '1');
$installer->updateAttribute('catalog_product', 'visibility',  'is_monitored_by_siteimprove', '1');
$installer->updateAttribute('catalog_product', 'description', 'is_monitored_by_siteimprove', '1');

// Meta information
$installer->updateAttribute('catalog_product', 'meta_title',       'is_monitored_by_siteimprove', '1');
$installer->updateAttribute('catalog_product', 'meta_keyword',     'is_monitored_by_siteimprove', '1');
$installer->updateAttribute('catalog_product', 'meta_description', 'is_monitored_by_siteimprove', '1');

// Design
$installer->updateAttribute('catalog_product', 'page_layout',          'is_monitored_by_siteimprove', '1');
$installer->updateAttribute('catalog_product', 'custom_design',        'is_monitored_by_siteimprove', '1');
$installer->updateAttribute('catalog_product', 'custom_layout_update', 'is_monitored_by_siteimprove', '1');

$installer->endSetup();
