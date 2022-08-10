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
declare(strict_types=1);

namespace Lof\FlashSales\Model\Data;

use Lof\FlashSales\Api\Data\ProductPriceInterface;

class ProductPrice extends \Magento\Framework\Api\AbstractExtensibleObject implements ProductPriceInterface
{

    /**
     * Get index_id
     * @return string|null
     */
    public function getIndexId()
    {
        return $this->_get(self::INDEX_ID);
    }

    /**
     * Set index_id
     * @param string $indexId
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setIndexId($indexId)
    {
        return $this->setData(self::INDEX_ID, $indexId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\FlashSales\Api\Data\ProductPriceExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Lof\FlashSales\Api\Data\ProductPriceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\FlashSales\Api\Data\ProductPriceExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get flashsales_id
     * @return string|null
     */
    public function getFlashsalesId()
    {
        return $this->_get(self::FLASHSALES_ID);
    }

    /**
     * Set flashsales_id
     * @param string $flashsalesId
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setFlashsalesId($flashsalesId)
    {
        return $this->setData(self::FLASHSALES_ID, $flashsalesId);
    }

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId()
    {
        return $this->_get(self::PRODUCT_ID);
    }

    /**
     * Set product_id
     * @param string $productId
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Set store_id
     * @param string $storeId
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get qty_limit
     * @return string|null
     */
    public function getQtyLimit()
    {
        return $this->_get(self::QTY_LIMIT);
    }

    /**
     * Set qty_limit
     * @param string $qtyLimit
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setQtyLimit($qtyLimit)
    {
        return $this->setData(self::QTY_LIMIT, $qtyLimit);
    }

    /**
     * @return mixed|string|null
     */
    public function getFlashSalePrice()
    {
        return $this->_get(self::FLASH_SALE_PRICE);
    }

    /**
     * @param string $flashSalePrice
     * @return ProductPriceInterface|ProductPrice
     */
    public function setFlashSalePrice($flashSalePrice)
    {
        return $this->setData(self::FLASH_SALE_PRICE, $flashSalePrice);
    }
}
