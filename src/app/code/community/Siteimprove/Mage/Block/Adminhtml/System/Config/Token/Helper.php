<?php

/**
 * @method string|null getHtmlId()
 * @method string|null getButtonLabelFetch()
 * @method string|null getTargetElementHtmlId()
 * @method string|null getAjaxFetchUrl()
 * @method string|null getTokenSetComment()
 * @method string|null getOnChangeFunction()
 */
class Siteimprove_Mage_Block_Adminhtml_System_Config_Token_Helper extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('siteimprove/system/config/token/helper.phtml');
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        if ($this->_getData('is_disabled') === null) {
            $this->setData('is_disabled', $this->helper('siteimprove')->getToken() !== null);
        }
        return $this->_getData('is_disabled');
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {

        $originalData = new Varien_Object($element->getOriginalData());

        $this->setData('html_id',  $element->getHtmlId());
        $this->setData('token_set_comment', $originalData->getData('token_set_comment'));

        $this->addData(array(
            'button_label_fetch' => $originalData->getData('button_label_fetch'),
            'ajax_fetch_url' => $this->getUrl('*/siteimprove_token/fetch')
        ));

        $html = $this->_toHtml();

        $element->setData('readonly', true);
        $element->setData('onchange', 'tokenConfigOnChange(this);');
        $html .=  parent::render($element);

        return $html;
    }
}
