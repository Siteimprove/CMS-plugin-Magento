<?php


class Siteimprove_Mage_Adminhtml_Siteimprove_RecheckController extends Mage_Adminhtml_Controller_Action
{
    public function urlsAction()
    {
        /**
         * @var Siteimprove_Mage_Helper_Recheck $helper
         */
        $helper = Mage::helper('siteimprove/recheck');

        $clear = $this->getRequest()->getParam('clear', true);
        if (!$clear || $clear === 'false' || $clear === 'no') {
            $clear = false;
        } else {
            $clear = true;
        }

        $response = $helper->getUrlsToFrontend($clear);

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
