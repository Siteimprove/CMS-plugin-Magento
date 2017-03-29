<?php


abstract class Siteimprove_Mage_Block_Adminhtml_Overlay_Abstract extends Mage_Adminhtml_Block_Template
{

    /**
     * @var string|null
     */
    protected $_defaultTemplate = null;

    protected function _prepareLayout()
    {
        if ($this->_defaultTemplate && !$this->getTemplate()) {
            $this->setTemplate($this->_defaultTemplate);
        }
        return parent::_prepareLayout();
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->helper('siteimprove')->getToken();
    }

    /**
     * @param @param null|string|bool|int|Mage_Core_Model_Store $store $store
     *
     * @return bool
     */
    public function isEnabled($store)
    {
        return $this->helper('siteimprove')->isEnabled($store);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->isEnabled(Mage_Core_Model_App::ADMIN_STORE_ID)) {
            return parent::_toHtml();
        }
        return '';
    }
}
