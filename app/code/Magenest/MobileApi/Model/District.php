<?php
namespace Magenest\MobileApi\Model;

use Magenest\MobileApi\Api\Data\DistrictInterface;

class District extends \Magenest\Directory\Model\District implements DistrictInterface
{

    /**
     * @inheritDoc
     */
    public function getDistrictId()
    {
        return $this->_getData(self::DISTRICT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setDistrictId($district_id)
    {
        return $this->setData(self::DISTRICT_ID, $district_id);
    }

    /**
     * @inheritDoc
     */
    public function getCityId()
    {
        return $this->_getData(self::CITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCityId($city_id)
    {
        return $this->setData(self::CITY_ID, $city_id);
    }

    /**
     * @inheritDoc
     */
    public function getCode()
    {
        return $this->_getData(self::CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->_getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getDefaultName()
    {
        return $this->_getData(self::DEFAULT_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setDefaultName($defaultName)
    {
        return $this->setData(self::DEFAULT_NAME, $defaultName);
    }

    /**
     * @inheritDoc
     */
    public function getDisableOnStoreFront()
    {
        return $this->_getData(self::DISABLE_ON_STOREFRONT);
    }

    /**
     * @inheritDoc
     */
    public function setDisableOnStoreFront($disable_on_storefront)
    {
        return $this->setData(self::DISABLE_ON_STOREFRONT, $disable_on_storefront);
    }
}
