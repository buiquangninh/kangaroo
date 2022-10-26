<?php

namespace Magenest\MobileApi\Model;

use Magenest\MobileApi\Api\Data\AddressInterface;
use Magenest\MobileApi\Api\Data\CityInterface;
use Magenest\MobileApi\Api\UserManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magenest\Directory\Model\CityFactory;
use Magenest\Directory\Model\DistrictFactory;
use Magenest\Directory\Model\WardFactory;
use Magenest\Directory\Model\ResourceModel\City\CollectionFactory as CityCollection;
use Magenest\Directory\Model\ResourceModel\District\CollectionFactory as DistrictCollection;
use Magenest\Directory\Model\ResourceModel\Ward\CollectionFactory as WardCollection;
use Magenest\MobileApi\Api\Data\DataObjectInterfaceFactory;

class UserManagement implements UserManagementInterface
{
    /** @var AddressRepositoryInterface */
    private $_addressRepository;

    /** @var CityCollection */
    private $_cityCollection;

    /** @var DistrictCollection */
    private $_districtCollection;

    /** @var WardCollection */
    private $_wardCollection;
    /**
     * @var CityFactory
     */
    private $city;
    /**
     * @var DistrictFactory
     */
    private $district;
    /**
     * @var WardFactory
     */
    private $ward;
    /**
     * @var DataObjectInterfaceFactory
     */
    private $dataObjectInterfaceFactory;

    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param DataObjectInterfaceFactory $dataObjectInterfaceFactory
     * @param CityFactory $city
     * @param DistrictFactory $district
     * @param WardFactory $ward
     * @param CityCollection $cityCollection
     * @param DistrictCollection $districtCollection
     * @param WardCollection $wardCollection
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        DataObjectInterfaceFactory $dataObjectInterfaceFactory,
        CityFactory                $city,
        DistrictFactory            $district,
        WardFactory                $ward,
        CityCollection             $cityCollection,
        DistrictCollection         $districtCollection,
        WardCollection             $wardCollection
    )
    {
        $this->_addressRepository = $addressRepository;
        $this->dataObjectInterfaceFactory = $dataObjectInterfaceFactory;
        $this->city = $city;
        $this->district = $district;
        $this->ward = $ward;
        $this->_cityCollection = $cityCollection;
        $this->_districtCollection = $districtCollection;
        $this->_wardCollection = $wardCollection;
    }

    /**
     * Add customer address
     *
     * @param int $customerId
     * @param AddressInterface $address
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws LocalizedException
     */
    public function addDeliveryAddress($customerId, $address)
    {
        $address->setCustomerId($customerId);
        return $this->_addressRepository->save($address);
    }

    /**
     * Get city by country id
     *
     * @param string $countryId
     * @return string[]
     */
    public function getVNAvailableLocations($countryId = 'vn')
    {
        $cities = $this->_cityCollection->create()
                                       ->addFieldToFilter('country_id', $countryId);

        return $this->formatAddressData($cities->prepareOptionArray());
    }

    /**
     * Get district by city id
     *
     * @param int $cityId
     * @return string[]
     */
    public function getVNDistrictsByCity($cityId)
    {
        $districts = $this->_districtCollection->create()->addFieldToFilter('city_id', $cityId);

        return $this->formatAddressData($districts->prepareOptionArray());
    }

    /**
     * Get wards by district id
     *
     * @param int $districtId
     * @return string[]
     */
    public function getVNWardsByDistrict($districtId)
    {
        $wards = $this->_wardCollection->create()->addFieldToFilter('district_id', $districtId);

        return $this->formatAddressData($wards->prepareOptionArray());
    }

    /**
     * Format output address
     *
     * @param array $addresses
     *
     * @return array
     */
    private function formatAddressData($addresses)
    {
        $data = [];
        foreach ($addresses as $address) {
            $data[$address['value']]['id'] = $address['value'];
            $data[$address['value']]['default_name'] = $address['label'];
            $data[$address['value']]['name'] = $address['name'];
            $data[$address['value']]['full_name'] = $address['full_name'];
        }

        return $data;
    }

    /**
     * @param $districtId
     * @return \Magenest\Directory\Model\District|\Magenest\MobileApi\Api\Data\DistrictInterface
     */
    public function getVNDistrictByDistrictId($districtId)
    {
        return $this->district->create()->load($districtId,'district_id');
    }

    /**
     * @param $wardId
     * @return \Magenest\Directory\Model\Ward|\Magenest\MobileApi\Api\Data\WardInterface
     */
    public function getVNWardByWardId($wardId)
    {
        return $this->ward->create()->load($wardId,'ward_id');
    }

    /**
     * @param $cityId
     * @return \Magenest\Directory\Model\City|CityInterface
     */
    public function getVNCityByCityId($cityId)
    {
        return $this->city->create()->load($cityId,'city_id');
    }
}
