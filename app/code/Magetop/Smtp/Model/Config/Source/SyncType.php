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
 * Class SyncType
 * @package Magetop\Smtp\Model\Config\Source
 */
class SyncType implements ArrayInterface
{
    const ALL         = 'all';
    const CUSTOMERS   = 1;
    const ORDERS      = 2;
    const SUBSCRIBERS = 3;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::CUSTOMERS,
                'label' => __('Customers')
            ],
            [
                'value' => self::ORDERS,
                'label' => __('Orders')
            ],
            [
                'value' => self::SUBSCRIBERS,
                'label' => __('Subscribers')
            ],
            [
                'value' => self::ALL,
                'label' => __('Everything')
            ]
        ];

        return $options;
    }
}
