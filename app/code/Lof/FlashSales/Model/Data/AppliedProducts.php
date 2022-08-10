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

use Lof\FlashSales\Api\Data\AppliedProductsInterface;

class AppliedProducts extends \Magento\Framework\Api\AbstractExtensibleObject implements AppliedProductsInterface
{

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
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     */
    public function setFlashsalesId($flashsalesId)
    {
        return $this->setData(self::FLASHSALES_ID, $flashsalesId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\FlashSales\Api\Data\AppliedProductsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Lof\FlashSales\Api\Data\AppliedProductsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\FlashSales\Api\Data\AppliedProductsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
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
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * Get type_id
     * @return string|null
     */
    public function getTypeId()
    {
        return $this->_get(self::TYPE_ID);
    }

    /**
     * Set type_id
     * @param string $typeId
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     */
    public function setTypeId($typeId)
    {
        return $this->setData(self::TYPE_ID, $typeId);
    }

    /**
     * Get original_price
     * @return string|null
     */
    public function getOriginalPrice()
    {
        return $this->_get(self::ORIGINAL_PRICE);
    }

    /**
     * Set original_price
     * @param string $originalPrice
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     */
    public function setOriginalPrice($originalPrice)
    {
        return $this->setData(self::ORIGINAL_PRICE, $originalPrice);
    }

    /**
     * Get discount_amount
     * @return string|null
     */
    public function getDiscountAmount()
    {
        return $this->_get(self::DISCOUNT_AMOUNT);
    }

    /**
     * Set discount_amount
     * @param string $discountAmount
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     */
    public function setDiscountAmount($discountAmount)
    {
        return $this->setData(self::DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
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
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     */
    public function setQtyLimit($qtyLimit)
    {
        return $this->setData(self::QTY_LIMIT, $qtyLimit);
    }

    /**
     * Get qty_ordered
     * @return string|null
     */
    public function getQtyOrdered()
    {
        return $this->_get(self::QTY_ORDERED);
    }

    /**
     * Set qty_limit
     * @param string $qtyOrdered
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     */
    public function setQtyOrdered($qtyOrdered)
    {
        return $this->setData(self::QTY_ORDERED, $qtyOrdered);
    }

    /**
     * get sku
     * @return mixed|string|null
     */
    public function getSku()
    {
        return $this->_get(self::SKU);
    }

    /**
     * Set sku
     * @param string $sku
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * get position
     * @return mixed|string|null
     */
    public function getPosition()
    {
        return $this->_get(self::POSITION);
    }

    /**
     * Set position
     * @param string $position
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
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
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setFlashSalePrice($flashSalePrice)
    {
        return $this->setData(self::FLASH_SALE_PRICE, $flashSalePrice);
    }

    /**
     * @return mixed|string|null
     */
    public function getPriceType()
    {
        return $this->_get(self::PRICE_TYPE);
    }

    /**
     * @param string $priceType
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setPriceType($priceType)
    {
        return $this->setData(self::PRICE_TYPE, $priceType);
    }

    /**
     * @return mixed|string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * @param string $name
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }
}
