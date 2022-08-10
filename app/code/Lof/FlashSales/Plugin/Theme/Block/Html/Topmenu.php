<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\FlashSales\Plugin\Theme\Block\Html;

use Lof\FlashSales\Helper\Data;
use Magento\Customer\Model\Session\Storage as CustomerSessionStorage;

class Topmenu
{

    /**
     * @var CustomerSessionStorage
     */
    private $customerSessionStorage;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param Data $helperData
     * @param CustomerSessionStorage $customerSessionStorage
     */
    public function __construct(
        Data $helperData,
        CustomerSessionStorage $customerSessionStorage
    ) {
        $this->helperData = $helperData;
        $this->customerSessionStorage = $customerSessionStorage;
    }

    /**
     * Add Customer Group identifier to cache key.
     *
     * If Permissions are enabled, we must append a Customer Group ID to the cache key so that menu block
     * caches are not shared between Customer Groups.
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCacheKeyInfo(\Magento\Theme\Block\Html\Topmenu $subject, $result)
    {
        if ($this->helperData->isEnabled()) {
            $result['customer_group_id'] = $this->customerSessionStorage->getCustomerGroupId();
        }

        return $result;
    }
}
