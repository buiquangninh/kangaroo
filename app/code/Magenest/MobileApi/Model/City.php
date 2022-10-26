<?php
namespace Magenest\MobileApi\Model;

use Magenest\MobileApi\Api\Data\CityInterface;

class City extends \Magenest\Directory\Model\City implements CityInterface
{

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
    public function getCountryId()
    {
        return $this->_getData(self::COUNTRY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCountryId($country_id)
    {
        return $this->setData(self::COUNTRY_ID, $country_id);
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
