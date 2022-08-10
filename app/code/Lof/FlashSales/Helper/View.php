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

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class View extends AbstractHelper
{

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CountDownData
     */
    protected $countDownData;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * View constructor.
     * @param Context $context
     * @param Data $helperData
     * @param CountDownData $countDownData
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Data $helperData,
        CountDownData $countDownData,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->countDownData = $countDownData;
        $this->storeManager = $storeManager;
        $this->helperData = $helperData;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param $priceType
     * @param $saleableItem
     * @return string|string[]|null
     * @throws NoSuchEntityException
     */
    public function convertDiscount($priceType, $saleableItem)
    {
        $originPrice = $saleableItem->getPrice();
        $finalPrice = $saleableItem->getFinalPrice();
        switch ($priceType) {
            case \Lof\FlashSales\Ui\Component\Form\PriceType::TYPE_DECREASE_PERCENTAGE:
                $form = $this->helperData->getDiscountAmountTypePercent();
                $amount = (($originPrice - $finalPrice) / $originPrice) * 100;
                return str_replace('{discount_amount}', $amount, $form);
            case \Lof\FlashSales\Ui\Component\Form\PriceType::TYPE_DECREASE_FIXED:
                $form = $this->helperData->getDiscountAmountTypeFixed();
                $discount = $originPrice - $finalPrice;
                return str_replace(
                    '{discount_amount}',
                    $this->priceCurrency->convertAndFormat($discount),
                    $form
                );
        }
        return null;
    }

    /**
     * @param $saleableItem
     * @return string
     * @throws NoSuchEntityException
     */
    public function discountAmountHtml($saleableItem)
    {
        return '<span class="loffs-discount-amount">
                    ' . $this->getDiscountAmount($saleableItem) . '
                </span>';
    }

    /**
     * @param $saleableItem
     * @return string|string[]|null
     * @throws NoSuchEntityException
     */
    public function getDiscountAmount($saleableItem)
    {
        if ($saleableItem->getTypeId() === Configurable::TYPE_CODE) {
            return null;
        }

        $priceType = $this->helperData->getAppliedProductCollection()
            ->addFieldToFilter('product_id', $saleableItem->getId())
            ->getFirstItem()
            ->getPriceType();

        return $this->convertDiscount(
            $priceType,
            $saleableItem,
        );
    }
}
