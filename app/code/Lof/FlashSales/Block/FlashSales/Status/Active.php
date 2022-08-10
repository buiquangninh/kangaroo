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

namespace Lof\FlashSales\Block\FlashSales\Status;

use Lof\FlashSales\Model\ResourceModel\FlashSales\Collection;

class Active extends \Lof\FlashSales\Block\FlashSales\AbstractEvent
{

    /**
     * @return bool|mixed
     */
    public function canDisplay()
    {
        return $this->helperData->isEnabled()
            && $this->getFlashSalesActiveCollection()->count();
    }

    /**
     * @return Collection
     */
    public function getFlashSalesActiveCollection()
    {
        return $this->flashSalesCollectionFactory->create()
            ->addFieldToFilter('status', 2);
    }
}
