<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * User: leo
 * Date: 27/06/2022
 * Time: 17:03
 */
declare(strict_types=1);

namespace Lof\FlashSales\Observer\Product;

use Lof\FlashSales\Api\AppliedProductsRepositoryInterface;
use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Helper\UpdateAppliedProductPrice;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateDataProductAppliedProduct
 */
class UpdateDataAppliedProduct implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var UpdateAppliedProductPrice
     */
    protected $productPriceHelper;

    /**
     * @var AppliedProductsRepositoryInterface
     */
    protected $appliedProductsRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * UpdateDataAppliedProduct constructor.
     * @param Data $helperData
     * @param UpdateAppliedProductPrice $productPriceHelper
     * @param AppliedProductsRepositoryInterface $appliedProductsRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helperData,
        UpdateAppliedProductPrice $productPriceHelper,
        AppliedProductsRepositoryInterface $appliedProductsRepository,
        LoggerInterface $logger
    )
    {
        $this->helperData                = $helperData;
        $this->productPriceHelper        = $productPriceHelper;
        $this->appliedProductsRepository = $appliedProductsRepository;
        $this->logger                    = $logger;
    }

    /**
     * @param Observer $observer
     * @return \Lof\FlashSales\Observer\CatalogProductSaveAfterObserver|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        try {
            $product = $observer->getProduct();
            if (!$this->helperData->isEnabled()) {
                return;
            }
            foreach ($this->productPriceHelper->getFlashSales()->getItems() as $flashSale) {
                $validateProduct  = $flashSale->getConditions()->validate($product);
                $appliedProductId = $this->appliedProductsRepository->hasProduct(
                    $flashSale->getFlashsalesId(),
                    $product->getId()
                );
                if (!$validateProduct && $appliedProductId) {
                    $this->appliedProductsRepository->deleteById($appliedProductId);
                }
                if ($validateProduct) {
                    $this->productPriceHelper->updateAppliedProductPrice($flashSale, $product);
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
