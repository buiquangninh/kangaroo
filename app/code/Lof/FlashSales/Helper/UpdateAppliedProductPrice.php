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

namespace Lof\FlashSales\Helper;

use Lof\FlashSales\Api\Data\AppliedProductsInterface;
use Lof\FlashSales\Model\Indexer\ProductPriceIndexer;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory as AppliedProductCollectionFactory;
use Lof\FlashSales\Model\ResourceModel\FlashSales\CollectionFactory as FlashSalesCollectionFactory;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;

class UpdateAppliedProductPrice extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var FlashSalesCollectionFactory
     */
    protected $flashSalesCollectionFactory;

    /**
     * @var AppliedProductCollectionFactory
     */
    protected $appliedProductCollectionFactory;

    /**
     * @var ProductPriceIndexer
     */
    protected $productPriceIndexer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * UpdateAppliedProductPrice constructor.
     * @param Context $context
     * @param Data $helperData
     * @param LoggerInterface $logger
     * @param AppliedProductCollectionFactory $appliedProductCollectionFactory
     * @param FlashSalesCollectionFactory $flashSalesCollectionFactory
     * @param ProductPriceIndexer $productPriceIndexer
     */
    public function __construct(
        Context $context,
        Data $helperData,
        LoggerInterface $logger,
        AppliedProductCollectionFactory $appliedProductCollectionFactory,
        FlashSalesCollectionFactory $flashSalesCollectionFactory,
        ProductPriceIndexer $productPriceIndexer
    ) {
        $this->logger = $logger;
        $this->helperData = $helperData;
        $this->productPriceIndexer = $productPriceIndexer;
        $this->appliedProductCollectionFactory = $appliedProductCollectionFactory;
        $this->flashSalesCollectionFactory = $flashSalesCollectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Lof\FlashSales\Model\ResourceModel\FlashSales\Collection
     */
    public function getFlashSales()
    {
        return $this->flashSalesCollectionFactory->create();
    }

    /**
     * @param $flashSale
     * @param $product
     * @throws \Throwable
     */
    public function updateAppliedProductPrice($flashSale, $product)
    {
        try {
            $appliedProduct = $this->appliedProductCollectionFactory->create()
                ->addFieldToFilter('flashsales_id', $flashSale->getFlashSalesId())
                ->addFieldToFilter('product_id', $product->getId())
                ->getFirstItem();
            if ($appliedProduct->isObjectNew()) {
                $appliedProduct->setData(
                    [
                        AppliedProductsInterface::FLASHSALES_ID => $flashSale->getFlashSalesId(),
                        AppliedProductsInterface::PRODUCT_ID => $product->getId(),
                        AppliedProductsInterface::NAME => $product->getName(),
                        AppliedProductsInterface::TYPE_ID => $product->getTypeId(),
                        AppliedProductsInterface::ORIGINAL_PRICE => $product->getPrice(),
                        AppliedProductsInterface::SKU => $product->getSku()
                    ]
                );
            } else {
                $appliedProduct->setName($product->getName());
                $appliedProduct->setTypeId($product->getTypeId());
                $appliedProduct->setOriginalPrice($product->getPrice());
                $appliedProduct->setSku($product->getSku());
            }
            $priceType = $appliedProduct->getPriceType();
            if (!empty($priceType)) {
                $discountAmount = $appliedProduct->getDiscountAmount();
                $originalPrice = $appliedProduct->getOriginalPrice();
                $flashSalePrice = $this->helperData->calcPriceRule(
                    $priceType,
                    $discountAmount,
                    $originalPrice
                );
                $appliedProduct->setFlashSalePrice($flashSalePrice);
            }
            $appliedProduct->save();

            if ($flashSale->getIsActive()) {
                if (!$this->productPriceIndexer->isIndexerScheduled()) {
                    $this->helperData->reindexProductPrice();
                    $this->productPriceIndexer->executeByFlashSalesId($flashSale->getFlashsalesId());
                } else {
                    $this->productPriceIndexer->getIndexer()->invalidate();
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
