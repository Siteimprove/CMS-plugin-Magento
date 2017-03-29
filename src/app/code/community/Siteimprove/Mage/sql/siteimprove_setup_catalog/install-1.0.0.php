<?php

/**
 * @var Mage_Catalog_Model_Resource_Setup $installer
 * @var Varien_Db_Adapter_Interface       $conn
 */
$installer = $this;

$installer->startSetup();

$catalogEavAttrTable = $installer->getTable('catalog/eav_attribute');
$desc = $conn->describeTable($catalogEavAttrTable);
if (!isset($desc['is_monitored_by_siteimprove'])) {
    $conn->addColumn($catalogEavAttrTable, 'is_monitored_by_siteimprove', array(
        'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable' => false,
        'default'  => 0,
        'comment'  => 'Is Monitored By Siteimprove'
    ));
}

$installer->endSetup();
