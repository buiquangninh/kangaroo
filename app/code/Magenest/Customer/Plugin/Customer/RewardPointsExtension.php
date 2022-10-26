<?php

namespace Magenest\Customer\Plugin\Customer;

use Magenest\RewardPoints\Api\Data\AccountInterface;
use Magenest\RewardPoints\Api\Data\MembershipInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtension;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magenest\RewardPoints\Model\AccountFactory;
use Magenest\RewardPoints\Model\MembershipFactory;
use Magenest\RewardPoints\Model\MembershipCustomerFactory;
use Magento\Framework\App\ResourceConnection;

class RewardPointsExtension
{
    protected  $account;
    protected  $membership;
    protected  $membershipCustomer;
    protected  $resource;
    protected  $customerExtensionFactory;

    public function __construct(
        CustomerExtensionFactory $customerExtensionFactory,
        AccountFactory $account,
        MembershipFactory $membership,
        MembershipCustomerFactory $membershipCustomer,
        ResourceConnection $resource
    )
    {
        $this->account = $account;
        $this->membership = $membership;
        $this->membershipCustomer = $membershipCustomer;
        $this->customerExtensionFactory = $customerExtensionFactory;
        $this->resource = $resource;
    }

    public function afterGetById(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer
    ) {
        return $this->getExtensionsAttribute($customer);
    }

    /**
     * @param $customer
     * @return mixed
     */
    public function getExtensionsAttribute($customer){
        $extensionAttributes = $customer->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getRewardPoints() && $extensionAttributes->getMembership()) {
            return $customer;
        }

        /** @var CustomerExtension $customerExtension */
        $customerExtension = $extensionAttributes ?: $this->customerExtensionFactory->create();
        $data = $this->getExtensionAttributesData($customer);
        $customerExtension->setRewardPoints($data['points_current']);
        $customerExtension->setMembershipId($data['membership_id']);
        $customerExtension->setMembership($data['name']);
        $customer->setExtensionAttributes($customerExtension);
        return $customer;
    }

    /**
     * @param $customer
     * @return array
     */

    private function getExtensionAttributesData($customer){
        $customerId = $customer->getId();
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['ac' => 'magenest_rewardpoints_account']
            )->joinLeft(
                ['mc' => 'magenest_rewardpoints_membership_customer'],
                'ac.customer_id = mc.customer_id'
            )->joinLeft(
                ['mb' => 'magenest_rewardpoints_membership'],
                'mc.membership_id = mb.id'
            )->where('ac.customer_id ='.$customerId);
        return $connection->fetchRow($select);
    }
}
