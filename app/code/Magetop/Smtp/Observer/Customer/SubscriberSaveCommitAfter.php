<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Smtp\Observer\Customer;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use Magetop\Smtp\Helper\EmailMarketing;
use Psr\Log\LoggerInterface;

/**
 * Class SubscriberSaveCommitAfter
 * @package Magetop\Smtp\Observer\Customer
 */
class SubscriberSaveCommitAfter implements ObserverInterface
{
    /**
     * @var EmailMarketing
     */
    protected $helperEmailMarketing;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SubscriberSaveCommitAfter constructor.
     *
     * @param EmailMarketing $helperEmailMarketing
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        EmailMarketing $helperEmailMarketing,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->helperEmailMarketing = $helperEmailMarketing;
        $this->logger               = $logger;
        $this->customerRepository   = $customerRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $subscriber = $observer->getEvent()->getDataObject();
        if ($this->helperEmailMarketing->isEnableEmailMarketing() &&
            $this->helperEmailMarketing->getSecretKey() &&
            $this->helperEmailMarketing->getAppID() &&
            !$this->helperEmailMarketing->isSyncedCustomer() &&
            (!$subscriber->getOrigObject() || $subscriber->isStatusChanged())
        ) {
            try {

                $data = [
                    'email'         => $subscriber->getSubscriberEmail(),
                    'firstName'     => '',
                    'lastName'      => '',
                    'phoneNumber'   => '',
                    'description'   => '',
                    'source'        => 'Magento',
                    'isSubscriber'  => $subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED,
                    'customer_type' => 'new_subscriber',
                    'is_utc'        => true,
                    'updated_at'    => $this->helperEmailMarketing->formatDate($subscriber->getChangeStatusAt())
                ];

                /**
                 * @var Customer $customer
                 */
                $customer = $this->getCustomerByEmail($subscriber->getSubscriberEmail());
                if ($customer && $customer->getId()) {
                    $data['firstName'] = $customer->getFirstname();
                    $data['lastName']  = $customer->getLastname();
                }

                $this->helperEmailMarketing->syncCustomer($data, false);
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }

    /**
     * @param string $email
     *
     * @return CustomerInterface|string
     */
    public function getCustomerByEmail($email)
    {
        try {
            return $this->customerRepository->get($email);
        } catch (Exception $e) {
            return '';
        }
    }
}
