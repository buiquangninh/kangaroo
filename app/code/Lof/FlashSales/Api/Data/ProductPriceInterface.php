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

interface ProductPriceInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const STORE_ID = 'store_id';
    const QTY_LIMIT = 'qty_limit';
    const FLASHSALES_ID = 'flashsales_id';
    const FLASH_SALE_PRICE = 'flash_sale_price';
    const PRODUCT_ID = 'product_id';
    const INDEX_ID = 'index_id';

    /**
     * Get index_id
     * @return string|null
     */
    public function getIndexId();

    /**
     * Set index_id
     * @param string $indexId
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setIndexId($indexId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\FlashSales\Api\Data\ProductPriceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Lof\FlashSales\Api\Data\ProductPriceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\FlashSales\Api\Data\ProductPriceExtensionInterface $extensionAttributes
    );

    /**
     * Get flashsales_id
     * @return string|null
     */
    public function getFlashsalesId();

    /**
     * Set flashsales_id
     * @param string $flashsalesId
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setFlashsalesId($flashsalesId);

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $productId
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setProductId($productId);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $storeId
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setStoreId($storeId);

    /**
     * Get qty_limit
     * @return string|null
     */
    public function getQtyLimit();

    /**
     * Set qty_limit
     * @param string $qtyLimit
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setQtyLimit($qtyLimit);

    /**
     * Get flash_sale_price
     * @return string|null
     */
    public function getFlashSalePrice();

    /**
     * Set flash_sale_price
     * @param string $flashSalePrice
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     */
    public function setFlashSalePrice($flashSalePrice);
}
