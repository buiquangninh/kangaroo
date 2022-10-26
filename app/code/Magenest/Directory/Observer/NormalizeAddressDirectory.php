<?php


namespace Magenest\Directory\Observer;


use Magenest\Directory\Helper\DirectoryHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class NormalizeAddressDirectory implements ObserverInterface
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

    public function execute(Observer $observer)
    {
        $address = $observer->getAddress();
        $ward = $this->directoryHelper->getWardDefaultName($address->getWardId());
        if ($ward) {
            $address->setWard($ward);
        }
        $district = $this->directoryHelper->getDistrictDefaultName($address->getDistrictId());
        if ($district) {
            $address->setDistrict($district);
        }
    }
}
