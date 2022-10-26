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

class CountDownMode implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * List Mode
     */
    const CDM_DISABLED = 'disabled';
    const CDM_DAYS_HOURS= 'days_hours';
    const CDM_HOURS_MINUTES_SECONDS = 'hours_minutes_seconds';
    const CDM_DAYS_HOURS_MINUTES_SECONDS = 'days_hours_minutes_seconds';

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::CDM_DISABLED,
                'label' => __('Disabled')
            ],
            [
                'value' => self::CDM_DAYS_HOURS,
                'label' => __('Count Down (Day & Hours)')
            ],
            [
                'value' => self::CDM_HOURS_MINUTES_SECONDS,
                'label' => __('Count Down (Hours/Minutes/Seconds)')
            ],
            [
                'value' => self::CDM_DAYS_HOURS_MINUTES_SECONDS,
                'label' => __('Count Down (Day/Hours/Minutes/Seconds)')
            ]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::CDM_DISABLED => __('Disabled'),
            self::CDM_DAYS_HOURS => __('Count Down (Day & Hours)'),
            self::CDM_HOURS_MINUTES_SECONDS => __('Count Down (Hours/Minutes/Seconds)'),
            self::CDM_DAYS_HOURS_MINUTES_SECONDS => __('Count Down (Day/Hours/Minutes/Seconds)')
        ];
    }
}
