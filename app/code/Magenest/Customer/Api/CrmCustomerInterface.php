<?php
namespace Magenest\Customer\Api;

interface CrmCustomerInterface
{
    /**
     * Get Customer Token using only phone number
     * @param string $telephone
     * @return string
     */
    public function phoneLogin(string $telephone): string;

    /**
     * Get Customer Token using only phone number
     * @param string $telephone
     * @param string $password
     * @return bool
     */
    public function changePassword(string $telephone, string $password): bool;

    /**
     * Inactivate a customer account.
     *
     * @api
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function inactivateById($customerId): bool;
}
