<?php

namespace Magenest\Customer\Block;

use Magenest\CustomerAvatar\Block\Attributes\Avatar;
use Magenest\RewardPoints\Helper\MembershipData;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;

class AccountInfo extends Template
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var Avatar
     */
    private $avatar;

    /**
     * @var MembershipData
     */
    private $membershipHelper;

    /**
     * @param Template\Context $context
     * @param MembershipData $membershipHelper
     * @param Session $customerSession
     * @param Avatar $avatar
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        MembershipData $membershipHelper,
        Session $customerSession,
        Avatar $avatar,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->membershipHelper = $membershipHelper;
        $this->avatar = $avatar;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getCustomerFullName()
    {
        try {
            if ($this->customerSession->isLoggedIn()) {
                return $this->customerSession->getCustomer()->getName();
            }
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }
        return '';
    }

    /**
     * @return string
     */
    public function getCustomerAvatarUrl()
    {
        try {
            $customerId = $this->customerSession->getCustomerId();
            return $this->avatar->getCustomerAvatarById($customerId);
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }
        return '';
    }

    /**
     * @return string
     */
    public function getUrlCustomerEdit()
    {
        return $this->getUrl('customer/account/edit');
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getMembershipData()
    {
        $membershipTier = new DataObject();
        try {
            $customerId = $this->customerSession->getCustomerId();
            $membershipTier = $this->membershipHelper->getCustomerTier($customerId);
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return $membershipTier;
    }
}
