<?php


class Siteimprove_Mage_Model_Url_Collection
{
    /**
     * @var Siteimprove_Mage_Model_Url[]
     */
    protected $_urls = array();

    /**
     * @param   Siteimprove_Mage_Model_Url $url
     *
     * @return  $this
     */
    public function add(Siteimprove_Mage_Model_Url $url)
    {
        $this->_urls[] = $url;
        return $this;
    }

    /**
     * Clear all urls
     *
     * @return Mage_Core_Model_Message_Collection
     */
    public function clear()
    {
        $this->_urls = array();
        return $this;
    }

    /**
     * @return Siteimprove_Mage_Model_Url[]
     */
    public function getUrls()
    {
        return $this->_urls;
    }

    /**
     * Retrieve url count
     *
     * @return int
     */
    public function count()
    {
        return count($this->_urls);
    }
}
