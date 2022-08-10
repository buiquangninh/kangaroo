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

namespace Lof\FlashSales\Api\Data;

interface AppliedProductsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const FLASHSALES_ID = 'flashsales_id';
    const NAME ='name';
    const TYPE_ID = 'type_id';
    const ENTITY_ID = 'entity_id';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const ORIGINAL_PRICE = 'original_price';
    const PRODUCT_ID = 'product_id';
    const QTY_LIMIT = 'qty_limit';
    const QTY_ORDERED = 'qty_ordered';
    const POSITION = 'position';
    const SKU = 'sku';
    const FLASH_SALE_PRICE = 'flash_sale_price';
    const PRICE_TYPE = 'price_type';

    /**
     * Get flashsales_id
     * @return string|null
     */
    public function getFlashsalesId();

    /**
     * Set flashsales_id
     * @param string $flashsalesId
     * @return AppliedProductsInterface
     */
    public function setFlashsalesId($flashsalesId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\FlashSales\Api\Data\AppliedProductsExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Lof\FlashSales\Api\Data\AppliedProductsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\FlashSales\Api\Data\AppliedProductsExtensionInterface $extensionAttributes
    );

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return AppliedProductsInterface
     */
    public function setName($name);

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $productId
     * @return AppliedProductsInterface
     */
    public function setProductId($productId);

    /**
     * Get type_id
     * @return string|null
     */
    public function getTypeId();

    /**
     * Set type_id
     * @param string $typeId
     * @return AppliedProductsInterface
     */
    public function setTypeId($typeId);

    /**
     * Get original_price
     * @return string|null
     */
    public function getOriginalPrice();

    /**
     * Set original_price
     * @param string $originalPrice
     * @return AppliedProductsInterface
     */
    public function setOriginalPrice($originalPrice);

    /**
     * Get discount_amount
     * @return string|null
     */
    public function getDiscountAmount();

    /**
     * Set discount_amount
     * @param string $discountAmount
     * @return AppliedProductsInterface
     */
    public function setDiscountAmount($discountAmount);

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return AppliedProductsInterface
     */
    public function setEntityId($entityId);

    /**
     * Get qty_limit
     * @return string|null
     */
    public function getQtyLimit();

    /**
     * Set qty_limit
     * @param string $qtyLimit
     * @return AppliedProductsInterface
     */
    public function setQtyLimit($qtyLimit);

    /**
     * Get qty_ordered
     * @return string|null
     */
    public function getQtyOrdered();

    /**
     * Set qty_ordered
     * @param string $qtyOrdered
     * @return AppliedProductsInterface
     */
    public function setQtyOrdered($qtyOrdered);

    /**
     * Get position
     * @return string|null
     */
    public function getPosition();

    /**
     * Set position
     * @param string $position
     * @return AppliedProductsInterface
     */
    public function setPosition($position);

    /**
     * Get sku
     * @return string|null
     */
    public function getSku();

    /**
     * Set sku
     * @param string $sku
     * @return AppliedProductsInterface
     */
    public function setSku($sku);

    /**
     * Get Flash Sale Price
     * @return string|null
     */
    public function getFlashSalePrice();

    /**
     * Set Flash Sale Price
     * @param string $flashSalePrice
     * @return AppliedProductsInterface
     */
    public function setFlashSalePrice($flashSalePrice);

    /**
     * Get Price Type
     * @return string|null
     */
    public function getPriceType();

    /**
     * Set Price Type
     * @param string $priceType
     * @return AppliedProductsInterface
     */
    public function setPriceType($priceType);
}
