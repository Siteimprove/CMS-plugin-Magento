<?php


class Siteimprove_Mage_Model_Url
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var int
     */
    protected $storeId;

    /**
     * @param (int|string)[] $params
     */
    public function __construct(array $params)
    {
        $url = isset($params['url']) ? $params['url'] : isset($params[0]) ? $params[0] : null;
        if ($url == null) {
            throw new RangeException('Could not find url value');
        }

        $storeId = isset($params['store_id']) ? $params['store_id'] : isset($params[1]) ? $params[1] : null;
        if ($storeId === null) {
            throw new RangeException('Could not find store id value');
        }

        $this->setUrl($url);
        $this->setStoreId($storeId);
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        if (!is_string($url)) {
            $type = gettype($url);
            throw new UnexpectedValueException(sprintf('Expected url to be "string" but got "%s"', $type));
        }
        $this->url = (string)$url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if (!is_numeric($storeId)) {
            $type = gettype($storeId);
            throw new UnexpectedValueException(sprintf('Expected storeId of type "%s" to be numeric', $type));
        }
        $this->storeId = (int)$storeId;
        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeId;
    }
}
