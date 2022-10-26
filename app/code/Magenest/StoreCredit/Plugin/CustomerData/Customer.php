<?php
/**
 * Magenest
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magenest.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magenest.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magenest
 * @package     Magenest_StoreCredit
 * @copyright   Copyright (c) Magenest (https://www.magenest.com/)
 * @license     https://www.magenest.com/LICENSE.txt
 */

namespace Magenest\StoreCredit\Plugin\CustomerData;

use Magenest\StoreCredit\Helper\Calculation;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;

class Customer
{
    /**
     * @var Calculation
     */
    protected $helper;

    /**
     * @var CurrentCustomer
     */
    private $currentCustomer;

    /**
     * Customer constructor.
     *
     * @param CurrentCustomer $currentCustomer
     * @param Calculation $helper
     */
    public function __construct(CurrentCustomer $currentCustomer, Calculation $helper)
    {
        $this->currentCustomer = $currentCustomer;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param $result
     *
     * @return array
     * @throws \Throwable
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        $quote = $this->helper->getCheckoutSession()->getQuote();
        $creditData = [];

        try {
            $customer = $this->currentCustomer->getCustomer();
            $creditData = [
                'customer_id' => $customer->getId(),
                'phone_number' => $this->getCustomerTelephone($customer),
                'email' => $customer->getId() ? $customer->getEmail() : null,
            ];
        } catch (\Exception $exception) {
	        $creditData = [];
        }


        if ($this->helper->isEnabled($quote->getStoreId())) {
            $creditData = array_merge($creditData, [
                'isSpendingCredit' => !!floatval($quote->getMpStoreCreditSpent()),
                'balance' => $this->helper->getAccountHelper()->getBalance(),
                'convertedBalance' => $this->helper->convertPrice(
                    $this->helper->getAccountHelper()->getBalance(),
                    true,
                    false
                ),
                'isEnabledFor' => $this->helper->isEnabledForCustomer()
            ]);
        }

        return array_merge($result, $creditData);
    }

    /**
     * @param CustomerInterface $customer
     * @return string|null
     */
    private function getCustomerTelephone(CustomerInterface $customer)
    {
        if ($customer->getCustomAttribute('telephone')) {
            return $customer->getCustomAttribute('telephone')->getValue();
        }

        foreach ($customer->getAddresses() ?? [] as $address) {
            if ($address->getTelephone()) {
                return $address->getTelephone();
            }
        }

        return null;
    }
}
