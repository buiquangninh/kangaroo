<?php
namespace Magenest\Directory\Model\Plugin\Customer\Model;

use Magenest\Directory\Helper\DirectoryHelper;
use Magento\Customer\Api\Data\AddressInterface;

class Address
{
    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * Address constructor.
     * @param DirectoryHelper $directoryHelper
     */
    public function __construct(
        DirectoryHelper $directoryHelper
    ) {
        $this->directoryHelper = $directoryHelper;
    }

    public function afterUpdateData(\Magento\Customer\Model\Address $subject, $result, AddressInterface $address)
    {
       $data = $address->__toArray();
       if (isset($data['city_id'], $data['district_id'], $data['ward_id'])) {
           $result->setCity($data['city'] ?? '');
           $result->setCityId($data['city_id'] ?? '');
           $result->setDistrict($data['district'] ?? '');
           $result->setDistrictId($data['district_id'] ?? '');
           $result->setWardId($data['ward_id'] ?? '');
           $result->setWard($data['ward'] ?? '');
       }

        $ward = $this->directoryHelper->getWardDefaultName($result->getWardId());
        if ($ward) {
            $result->setWard($ward);
        }
        $district = $this->directoryHelper->getDistrictDefaultName($result->getDistrictId());
        if ($district) {
            $result->setDistrict($district);
        }

        return $result;
    }
}
