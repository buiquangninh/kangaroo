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

namespace Magetop\Smtp\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DaysRange
 * @package Magetop\Smtp\Model\Config\Source
 */
class DaysRange implements ArrayInterface
{
    const CUSTOM = 'custom';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = ['label' => $label, 'value' => $value];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'lifetime'   => __('Lifetime'),
            '90'         => __('90'),
            '365'        => __('1 Year'),
            '730'        => __('2 Years'),
            self::CUSTOM => __('Choose Date Range')
        ];
    }
}
