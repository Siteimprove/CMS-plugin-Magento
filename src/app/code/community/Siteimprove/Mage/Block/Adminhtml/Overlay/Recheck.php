<?php


class Siteimprove_Mage_Block_Adminhtml_Overlay_Recheck extends Siteimprove_Mage_Block_Adminhtml_Overlay_Abstract
{
    protected $_defaultTemplate = 'siteimprove/overlay/recheck.phtml';

    /**
     * @param bool $clear
     *
     * @return string[]
     */
    public function getCheckUrls($clear = true)
    {
        /** @var Siteimprove_Mage_Helper_Recheck $helper */
        $helper = $this->helper('siteimprove/recheck');
        return $helper->getUrlsToFrontend($clear);
    }

    /**
     * @param bool $clear
     *
     * @return string
     */
    public function getJsonCheckUrls($clear = true)
    {
        $urls = $this->getCheckUrls($clear);
        return $this->helper('core')->jsonEncode($urls);
    }

    /**
     * @return bool|null
     */
    public function getAjax()
    {
        return $this->_getData('ajax');
    }

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setAjax($value)
    {
        if (!$value || $value === 'no' || $value === 'false') {
            $value = false;
        } else {
            $value = true;
        }
        $this->setData('ajax', $value);
        return $this;
    }

    /**
     * @return string
     */
    public function getFetchRecheckUrlsEndpoint()
    {
        return $this->getUrl('*/siteimprove_recheck/urls');
    }
}
