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

namespace Lof\FlashSales\Pricing\Price;

use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\ResourceModel\FlashSales;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

class FlashSalesPrice extends AbstractPrice implements BasePriceProviderInterface
{

    /**
     * Price type identifier string
     */
    const PRICE_CODE = 'flash_sales_price';

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FlashSales
     */
    private $flashSalesResource;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * FlashSalesPrice constructor.
     * @param SaleableInterface $saleableItem
     * @param float $quantity
     * @param CalculatorInterface $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     * @param FlashSales $flashSalesResource
     * @param Data $helperData
     */
    public function __construct(
        SaleableInterface $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        TimezoneInterface $localeDate,
        DateTime $dateTime,
        StoreManagerInterface $storeManager,
        FlashSales $flashSalesResource,
        Data $helperData
    ) {
        parent::__construct($saleableItem, $quantity, $calculator, $priceCurrency);
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
        $this->flashSalesResource = $flashSalesResource;
        $this->helperData = $helperData;
    }

    /**
     * @return bool|float
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getValue()
    {
        if (null === $this->value) {
            if ($this->product->hasData(self::PRICE_CODE)) {
                $value = $this->product->getData(self::PRICE_CODE);
                $this->value = $value ? (float)$value : false;
            } else {
                $dataTime = $this->helperData->getCurrentDateTime();
                $appliedProduct = $this->helperData->getAppliedProductCollection()
                    ->addFieldToFilter('sku', $this->product->getSku())
                    ->getFirstItem();

                $this->value = $this->flashSalesResource->getFlashSalesPrice(
                    $this->storeManager->getStore()->getId(),
                    $this->product->getId(),
                    $dataTime
                );
                if ($this->helperData->getSellOverQuantityLimit()) {
                    if ($appliedProduct->getQtyLimit() == null || $appliedProduct->getQtyLimit() == 0) {
                        $this->value = null;
                    }
                }
                $this->value = $this->value ? (float)$this->value : false;
            }
            if ($this->value) {
                $this->value = $this->priceCurrency->convertAndRound($this->value);
            }
        }

        return $this->value;
    }
}
