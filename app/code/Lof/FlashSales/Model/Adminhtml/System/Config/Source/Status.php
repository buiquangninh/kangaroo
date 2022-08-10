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

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{

    /**#@+
     * Status values
     */
    const STATUS_UPCOMING = 0;

    const STATUS_COMING_SOON = 1;

    const STATUS_ACTIVE = 2;

    const STATUS_ENDING_SOON = 3;

    const STATUS_ENDED = 4;

    /**#@-*/

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_UPCOMING => __('Upcoming'),
            self::STATUS_COMING_SOON => __('Coming Soon'),
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_ENDING_SOON => __('Ending Soon'),
            self::STATUS_ENDED => __('Ended')
        ];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }
}
