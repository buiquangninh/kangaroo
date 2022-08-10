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

namespace Lof\FlashSales\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IsPrivate implements OptionSourceInterface
{

    /**
     * @var \Lof\FlashSales\Model\FlashSales
     */
    protected $flashSales;

    /**
     * IsPrivate constructor.
     * @param \Lof\FlashSales\Model\FlashSales $flashSales
     */
    public function __construct(\Lof\FlashSales\Model\FlashSales $flashSales)
    {
        $this->flashSales = $flashSales;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->flashSales->getAvailableEventTypes();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
