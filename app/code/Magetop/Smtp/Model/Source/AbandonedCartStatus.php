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

namespace Magetop\Smtp\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AbandonedCartStatus
 * @package Magetop\Smtp\Model\Source
 */
class AbandonedCartStatus implements OptionSourceInterface
{
    const SENT          = 1;
    const WAIT_FOR_SEND = 0;

    /**
     * @return array
     */
    public static function getOptionArray()
    {
        return [
            self::SENT          => __('Sent'),
            self::WAIT_FOR_SEND => __('Wait for send')
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
