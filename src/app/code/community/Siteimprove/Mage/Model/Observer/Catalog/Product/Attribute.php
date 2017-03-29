<?php

/**
 * Handle rendering if product attribute field "is_monitored_by_siteimprove"
 */
class Siteimprove_Mage_Model_Observer_Catalog_Product_Attribute extends Siteimprove_Mage_Model_Observer_Catalog_Abstract
{
    /**
     * @see Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Main::_prepareForm()
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlProductAttributeEditPrepareForm(Varien_Event_Observer $observer)
    {
        /**
         * @var Varien_Data_Form                  $form
         * @var Varien_Data_Form_Element_Fieldset $fieldset
         */
        $form = $observer->getData('form');
        $fieldset = $form->getElement('base_fieldset');
        $helper = $this->helper();
        $fieldset->addField('is_monitored_by_siteimprove', 'select', array(
            'name' => 'is_monitored_by_siteimprove',
            'label' => $helper->__('Is monitored by Siteimprove'),
            'note' => $helper->__('Notify Siteimprove when value is changed'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ), 'is_configurable');
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function addColumnToProductAttributeGrid(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid $block */
        $block = $observer->getData('block');
        if (!$block) {
            return;
        }

        if ($block->getData('type') === 'adminhtml/catalog_product_attribute_grid') {
            $helper = $this->helper();
            $block->addColumnAfter('is_monitored_by_siteimprove', array(
                'header' => $helper->__('Monitored By Siteimprove'),
                'index' => 'is_monitored_by_siteimprove',
                'type' => 'options',
                'options' => array(
                    '1' => $helper->__('Yes'),
                    '0' => $helper->__('No'),
                )
            ), 'is_comparable');
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function sortProductAttributeGridColumns(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Block_Catalog_Product_Attribute_Grid $block */
        $block = $observer->getData('block');
        if (!$block) {
            return;
        }

        if ($block->getData('type') === 'adminhtml/catalog_product_attribute_grid') {
            $block->sortColumnsByOrder();
        }
    }
}
