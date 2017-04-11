<?php

class Siteimprove_Mage_Adminhtml_Siteimprove_TokenController extends Mage_Adminhtml_Controller_Action
{

    public function fetchAction()
    {
        /**
         * @var Siteimprove_Mage_Helper_Data $helper
         */
        $helper = Mage::helper('siteimprove');

        $response = new Varien_Object();
        $response->setToken(null);
        $response->setError(null);

        $token = null;

        try {
            $token = $helper->fetchToken(10);
        } catch (Exception $e) {
            $response->setError($e->getMessage());
            Mage::logException($e);
        }

        if ($token) {
            $response->setToken($token);
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setBody(Mage::helper('core')->jsonEncode($response));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/system/config/siteimprove');
    }
}
