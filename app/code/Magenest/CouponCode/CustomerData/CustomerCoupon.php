<?php


namespace Magenest\CouponCode\CustomerData;


use Magento\Customer\CustomerData\SectionSourceInterface;
use Magenest\CouponCode\Model\ResourceModel\ClaimCoupon\CollectionFactory;
use Magento\Customer\Model\Session;

class CustomerCoupon extends \Magento\Framework\DataObject implements SectionSourceInterface
{
    protected $customerSession;

    protected $couponListingCollection;

    public function __construct(
        CollectionFactory $collectionFactory,
        Session $session,
        array $data = []
    ) {
        $this->couponListingCollection = $collectionFactory;
        $this->customerSession = $session;
        parent::__construct($data);
    }

    public function getSectionData()
    {
        $collection = $this->couponListingCollection->create()
        ->addFieldToFilter('is_active', 1)
        ->addFieldtoFilter('customer_id', $this->customerSession->getCustomerId())
        ->setOrder('claimed_at', 'DESC');

        return [
            'customer_coupon' => $collection->getColumnValues('code')
        ];
    }
}
