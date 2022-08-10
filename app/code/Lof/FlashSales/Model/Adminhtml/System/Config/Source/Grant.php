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

namespace Lof\FlashSales\Model\Adminhtml\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Grant implements ArrayInterface
{

    /**
     * Retrieve Options Array
     *
     * @return array
     */
    public function toOptionArray()
    {

        return [
            [
                'value' => ConfigData::GRANT_ALL,
                'label' => __('Yes, for Everyone')
            ],
            [
                'value' => ConfigData::GRANT_CUSTOMER_GROUP,
                'label' => __('Yes, for Specified Customer Groups')
            ],
            [
                'value' => ConfigData::GRANT_NONE,
                'label' => __('No')
            ]
        ];
    }
}
