<?php

class Siteimprove_Mage_Model_Config_Backend_Token extends Mage_Core_Model_Config_Data
{
    /**
     * Check token value for changes
     *
     * @return $this
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $oldValue = $this->getOldValue();
        if ($oldValue && $oldValue !== $value) {
            Mage::throwException(Mage::helper('siteimprove')->__('Token value is immutable'));
        }

        return $this;
    }
}
