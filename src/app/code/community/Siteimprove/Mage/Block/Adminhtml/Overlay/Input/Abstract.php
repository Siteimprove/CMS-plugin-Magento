<?php

abstract class Siteimprove_Mage_Block_Adminhtml_Overlay_Input_Abstract
    extends Siteimprove_Mage_Block_Adminhtml_Overlay_Abstract
{
    protected $_defaultTemplate = 'siteimprove/overlay/input.phtml';

    /**
     * @return string|null
     */
    abstract public function getInputUrl();
}
