<?php
namespace Magenest\MobileApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface CityInterface extends ExtensibleDataInterface {

    const CITY_ID = 'city_id';
    const COUNTRY_ID = 'country_id';
    const CODE = 'code';
    const NAME = 'name';
    const DEFAULT_NAME = 'default_name';
    const DISABLE_ON_STOREFRONT = 'disable_on_storefront';

    /**
     * @return int
     */
    public function getCityId();

    /**
     * @param int $city_id
     * @return $this
     */
    public function setCityId($city_id);

    /**
     * @return int
     */
    public function getCountryId();

    /**
     * @param  int $country_id
     * @return $this
     */
    public function setCountryId($country_id);

    /**
     * @return int
     */
    public function getCode();

    /**
     * @param int $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return $this
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getDefaultName();

    /**
     * @param string $defaultName
     * @return $this
     */
    public function setDefaultName($defaultName);

    /**
     * @return int
     */
    public function getDisableOnStoreFront();

    /**
     * @param int $disable_on_storefront
     * @return $this
     */
    public function setDisableOnStoreFront($disable_on_storefront);
}
