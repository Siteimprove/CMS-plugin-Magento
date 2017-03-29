<?php

/**
 * Handle CMS page save, Siteimprove notification and CMS page overlay rendering
 */
class Siteimprove_Mage_Model_Observer_Cms extends Siteimprove_Mage_Model_Observer_Abstract
{
    /**
     * @param Varien_Event_Observer $observer
     */
    public function cmsPagePrepareSave(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Cms_Model_Page          $page
         * @var Siteimprove_Mage_Helper_Data $helper
         */
        $page = $observer->getData('page');
        $page->setData(self::OBJECT_SITEIMPROVE_PROCESS_KEY, true);
    }
    /**
     * @param Varien_Event_Observer $observer
     */
    public function cmsPageSaveCommitAfter(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Cms_Model_Page $page
         */
        $page = $observer->getData('data_object');
        if (!$page->getData(self::OBJECT_SITEIMPROVE_PROCESS_KEY)) {
            return;
        }

        if ($page->getIsActive()) {
            /** @var Siteimprove_Mage_Helper_Url_Cms $helper */
            $helper = Mage::helper('siteimprove/url_cms');
            $storeUrls = $helper->getAllPageUrls($page);

            if (empty($storeUrls)) {
                return;
            }

            $storeUrls = array_unique($storeUrls);

            $this->notifySiteimprove($storeUrls);
        }
    }

    /**
     * @see Mage_Adminhtml_Block_Cms_Page_Edit_Tab_Main::_prepareForm()
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlPageEditPrepareForm(Varien_Event_Observer $observer)
    {
        /**
         * @var Varien_Data_Form       $form
         * @var Mage_Cms_Model_Page    $page
         * @var Mage_Core_Model_Layout $layout
         */
        $form   = $observer->getData('form');
        $page   = Mage::registry('cms_page');
        $layout =  Mage::app()->getLayout();


        $block = $layout->createBlock(
            'siteimprove/adminhtml_overlay_input_cms_page',
            'siteimprove.overlay.input.cms_page',
            array('cms_page' => $page)
        );

        $form->addField('siteimprove_overlay', 'hidden', array(
            'disabled' => true,
            'after_element_html' => $block->toHtml(),
        ));
    }
}
