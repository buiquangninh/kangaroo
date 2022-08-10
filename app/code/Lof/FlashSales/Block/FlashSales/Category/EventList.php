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

namespace Lof\FlashSales\Block\FlashSales\Category;

use Magento\Framework\DataObject\IdentityInterface;

class EventList extends \Lof\FlashSales\Block\FlashSales\AbstractEvent implements IdentityInterface
{

    /**
     * @return bool|mixed
     */
    public function canDisplay()
    {
        return $this->helperData->isEnabled()
            && !$this->getFlashSalesCollection()->count();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->getCurrentCategory()->getIdentities();
    }
}
