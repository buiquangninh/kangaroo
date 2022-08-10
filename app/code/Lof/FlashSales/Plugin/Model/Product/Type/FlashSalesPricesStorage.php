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

namespace Lof\FlashSales\Plugin\Model\Product\Type;

class FlashSalesPricesStorage
{

    /**
     * @var array
     */
    private $flashSalesPrices = [];

    /**
     * @param string $id
     * @return false|float
     */
    public function getFlashSalesPrice($id)
    {
        return $this->flashSalesPrices[$id] ?? false;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function hasFlashSalesPrice($id)
    {
        return isset($this->flashSalesPrices[$id]);
    }

    /**
     * @param string $id
     * @param float $price
     * @return void
     */
    public function setFlashSalesPrice($id, $price)
    {
        $this->flashSalesPrices[$id] = $price;
    }
}
