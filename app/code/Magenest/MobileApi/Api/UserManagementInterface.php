<?php

namespace Magenest\MobileApi\Api;

use Magenest\MobileApi\Api\Data\WardInterface;

interface UserManagementInterface
{
    /**
     * Add customer address
     *
     * @param int $customerId
     * @param \Magenest\MobileApi\Api\Data\AddressInterface $address
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addDeliveryAddress($customerId, $address);

    /**
     * Get VN available city
     *
     * @param string $countryId Country code, default is 'vn'
     * @return string[]
     */
    public function getVNAvailableLocations($countryId = 'vn');

    /**
     * Get VN district by city
     *
     * @param int $cityId
     * @return string[]
     */
    public function getVNDistrictsByCity($cityId);

    /**
     * Get VN ward by district
     *
     * @param int $districtId
     * @return string[]
     */
    public function getVNWardsByDistrict($districtId);

    /**
     * Get VN district by district id
     *
     * @param int $districtId
     * @return \Magenest\MobileApi\Api\Data\DistrictInterface
     */
    public function getVNDistrictByDistrictId($districtId);

    /**
     * Get VN ward by ward id
     *
     * @param  int $wardId
     * @return \Magenest\MobileApi\Api\Data\WardInterface
     */
    public function getVNWardByWardId($wardId);

    /**
     * Get VN city by city id
     *
     * @param int $cityId
     * @return \Magenest\MobileApi\Api\Data\CityInterface
     */
    public function getVNCityByCityId($cityId);
}
