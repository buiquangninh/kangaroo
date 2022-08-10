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

namespace Lof\FlashSales\Ui\Component\Form;

use Magento\Framework\Option\ArrayInterface;

class PriceType implements ArrayInterface
{

    /*
     *  Price Type
     */
    const TYPE_DECREASE_FIXED = 0;
    const TYPE_DECREASE_PERCENTAGE = 1;

    /**
     * @return array|array[]
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TYPE_DECREASE_FIXED,
                'label' => __('Decrease Fixed')
            ], [
                'value' => self::TYPE_DECREASE_PERCENTAGE,
                'label' => __('Decrease Percentage')
            ]
        ];
    }
}
