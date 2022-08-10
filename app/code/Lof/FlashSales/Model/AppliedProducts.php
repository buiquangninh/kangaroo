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

namespace Lof\FlashSales\Model;

use Lof\FlashSales\Api\Data\AppliedProductsExtensionInterface;
use Lof\FlashSales\Api\Data\AppliedProductsInterface;
use Lof\FlashSales\Api\Data\AppliedProductsInterfaceFactory;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\Collection;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class AppliedProducts extends \Magento\Framework\Model\AbstractModel implements AppliedProductsInterface
{

    const CACHE_TAG = 'lof_flashsales_appliedproducts';

    /**
     * @var string
     */
    protected $_eventPrefix = 'lof_flashsales_appliedproducts';

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var AppliedProductsInterfaceFactory
     */
    protected $appliedproductsDataFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param AppliedProductsInterfaceFactory $appliedproductsDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceModel\AppliedProducts $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AppliedProductsInterfaceFactory $appliedproductsDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceModel\AppliedProducts $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->appliedproductsDataFactory = $appliedproductsDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve appliedproducts model with appliedproducts data
     * @return AppliedProductsInterface
     */
    public function getDataModel()
    {
        $appliedproductsData = $this->getData();

        $appliedproductsDataObject = $this->appliedproductsDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $appliedproductsDataObject,
            $appliedproductsData,
            AppliedProductsInterface::class
        );

        return $appliedproductsDataObject;
    }

    /**
     * @return array|mixed|string|null
     */
    public function getFlashsalesId()
    {
        return $this->getData(self::FLASHSALES_ID);
    }

    /**
     * @param string $flashsalesId
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setFlashsalesId($flashsalesId)
    {
        return $this->setData(self::FLASHSALES_ID, $flashsalesId);
    }

    /**
     * @return AppliedProductsExtensionInterface|\Magento\Framework\Api\ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @param AppliedProductsExtensionInterface $extensionAttributes
     * @return $this|AppliedProducts
     */
    public function setExtensionAttributes(
        AppliedProductsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Framework\Api\ExtensionAttributesInterface $extensionAttributes
     * @return $this
     */
    protected function _setExtensionAttributes(\Magento\Framework\Api\ExtensionAttributesInterface $extensionAttributes)
    {
        $this->_data[self::EXTENSION_ATTRIBUTES_KEY] = $extensionAttributes;
        return $this;
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Framework\Api\ExtensionAttributesInterface
     */
    protected function _getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @param string $productId
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getTypeId()
    {
        return $this->getData(self::TYPE_ID);
    }

    /**
     * @param string $typeId
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setTypeId($typeId)
    {
        return $this->setData(self::TYPE_ID, $typeId);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getOriginalPrice()
    {
        return $this->getData(self::ORIGINAL_PRICE);
    }

    /**
     * @param string $originalPrice
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setOriginalPrice($originalPrice)
    {
        return $this->setData(self::ORIGINAL_PRICE, $originalPrice);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getDiscountAmount()
    {
        return $this->getData(self::DISCOUNT_AMOUNT);
    }

    /**
     * @param string $discountAmount
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setDiscountAmount($discountAmount)
    {
        return $this->setData(self::DISCOUNT_AMOUNT, $discountAmount);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getQtyLimit()
    {
        return $this->getData(self::QTY_LIMIT);
    }

    /**
     * @param string $qtyLimit
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setQtyLimit($qtyLimit)
    {
        return $this->setData(self::QTY_LIMIT, $qtyLimit);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getQtyOrdered()
    {
        return $this->getData(self::QTY_ORDERED);
    }

    /**
     * @param string $qtyOrdered
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setQtyOrdered($qtyOrdered)
    {
        return $this->setData(self::QTY_ORDERED, $qtyOrdered);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @param string $position
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * @param string $sku
     * @return AppliedProductsInterface|AppliedProducts
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getFlashSalePrice()
    {
        return $this->getData(self::FLASH_SALE_PRICE);
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
     * @return array|mixed|string|null
     */
    public function getPriceType()
    {
        return $this->getData(self::PRICE_TYPE);
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
     * @return array|mixed|string|null
     */
    public function getName()
    {
        return $this->getData(self::NAME);
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
