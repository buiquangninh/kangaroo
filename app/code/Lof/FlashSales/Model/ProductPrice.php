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

use Lof\FlashSales\Api\Data\ProductPriceInterface;
use Lof\FlashSales\Api\Data\ProductPriceInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class ProductPrice extends \Magento\Framework\Model\AbstractModel
{

    /**
     * @var ProductPriceInterfaceFactory
     */
    protected $productpriceDataFactory;

    /**
     * @var string
     */
    protected $_eventPrefix = 'lof_flashsales_productprice';

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ProductPriceInterfaceFactory $productpriceDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Lof\FlashSales\Model\ResourceModel\ProductPrice $resource
     * @param \Lof\FlashSales\Model\ResourceModel\ProductPrice\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ProductPriceInterfaceFactory $productpriceDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Lof\FlashSales\Model\ResourceModel\ProductPrice $resource,
        \Lof\FlashSales\Model\ResourceModel\ProductPrice\Collection $resourceCollection,
        array $data = []
    ) {
        $this->productpriceDataFactory = $productpriceDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve productprice model with productprice data
     * @return ProductPriceInterface
     */
    public function getDataModel()
    {
        $productpriceData = $this->getData();

        $productpriceDataObject = $this->productpriceDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $productpriceDataObject,
            $productpriceData,
            ProductPriceInterface::class
        );

        return $productpriceDataObject;
    }
}
