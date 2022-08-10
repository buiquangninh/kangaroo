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

namespace Lof\FlashSales\Observer;

use Lof\FlashSales\Api\AppliedProductsRepositoryInterface;
use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Helper\UpdateAppliedProductPrice;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CatalogProductSaveAfterObserver implements ObserverInterface
{

    /**
     * @var AppliedProductsRepositoryInterface
     */
    protected $appliedProductsRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var UpdateAppliedProductPrice
     */
    protected $productPriceHelper;

    /**
     * CatalogProductSaveAfterObserver constructor.
     * @param Data $helperData
     * @param UpdateAppliedProductPrice $productPriceHelper
     * @param AppliedProductsRepositoryInterface $appliedProductsRepository
     */
    public function __construct(
        Data $helperData,
        UpdateAppliedProductPrice $productPriceHelper,
        AppliedProductsRepositoryInterface $appliedProductsRepository
    ) {
        $this->productPriceHelper = $productPriceHelper;
        $this->helperData = $helperData;
        $this->appliedProductsRepository = $appliedProductsRepository;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        if (!$this->helperData->isEnabled()) {
            return $this;
        }
        foreach ($this->productPriceHelper->getFlashSales()->getItems() as $flashSale) {
            $validateProduct = $flashSale->getConditions()->validate($product);
            $appliedProductId = $this->appliedProductsRepository->hasProduct(
                $flashSale->getFlashsalesId(),
                $product->getId()
            );
            if (!$validateProduct && $appliedProductId) {
                $this->appliedProductsRepository->deleteById($appliedProductId);
                return $this;
            }
            if ($validateProduct) {
                $this->productPriceHelper->updateAppliedProductPrice($flashSale, $product);
            }
        }
        return $this;
    }
}
