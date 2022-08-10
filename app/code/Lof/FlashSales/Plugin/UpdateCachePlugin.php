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

namespace Lof\FlashSales\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Registry;

/**
 * Class UpdateCachePlugin to update Context with data
 */
class UpdateCachePlugin
{

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @param Registry $coreRegistry
     * @param Session $customerSession
     */
    public function __construct(
        Registry $coreRegistry,
        Session $customerSession
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
    }

    /**
     * Update the context with current category and customer group id
     *
     * @param HttpContext $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetData(HttpContext $subject, array $result)
    {
        $category = $this->coreRegistry->registry('current_category');
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        if ($customerGroupId) {
            $result['customer_group'] = $customerGroupId;
        }

        if ($category && $category->getId()) {
            $result['category'] = $category->getId();
        }

        return $result;
    }
}
