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

namespace Lof\FlashSales\Plugin\Catalog\Model\Category\Attribute\Source;

class Mode
{

    const DM_LOF_FLASHSALES = 'FLASHSALES';

    /**
     * @param \Magento\Catalog\Model\Category\Attribute\Source\Mode $subject
     * @param $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllOptions(
        \Magento\Catalog\Model\Category\Attribute\Source\Mode $subject,
        $result
    ) {
        $result[] = ['value' => self::DM_LOF_FLASHSALES, 'label' => __('Flash Sale & Private Sale Event Page')];
        return $result;
    }
}
