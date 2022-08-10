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

namespace Lof\FlashSales\Plugin\Framework\Pricing\Render;

use Lof\FlashSales\Helper\CountDownData;
use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Helper\View;
use Magento\Bundle\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProviderInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Render\PriceBox as FrameworkPriceBox;

class PriceBox
{

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var CountDownData
     */
    protected $countDownData;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var View
     */
    protected $viewHelper;

    /**
     * @var ConfigurableOptionsProviderInterface
     */
    private $configurableOptionsProvider;

    /**
     * PriceBox constructor.
     * @param Data $helperData
     * @param View $viewHelper
     * @param CountDownData $countDownData
     * @param ConfigurableOptionsProviderInterface $configurableOptionsProvider
     */
    public function __construct(
        Data $helperData,
        View $viewHelper,
        CountDownData $countDownData,
        ConfigurableOptionsProviderInterface $configurableOptionsProvider
    ) {
        $this->viewHelper = $viewHelper;
        $this->helperData = $helperData;
        $this->countDownData = $countDownData;
        $this->configurableOptionsProvider = $configurableOptionsProvider;
    }

    /**
     * Modify PriceBox cache key.
     *
     * @param FrameworkPriceBox $subject
     * @param string $result
     * @return string
     */
    public function afterGetCacheKey(FrameworkPriceBox $subject, string $result): string
    {
        return sprintf(
            '%s-%s',
            $result,
            $subject->getSaleableItem()->getCanShowPrice() !== false ? 'allow_price' : 'deny_price'
        );
    }

    /**
     * @param FrameworkPriceBox $subject
     * @param callable $proceed
     * @param AmountInterface $amount
     * @param array $arguments
     * @return string
     */
    public function aroundRenderAmount(
        FrameworkPriceBox $subject,
        callable $proceed,
        AmountInterface $amount,
        array $arguments = []
    ) {
        $additionalHtml = '';
        $saleableItem = $subject->getSaleableItem();
        if ($saleableItem->getTypeId() != Type::TYPE_CODE) {
            $additionalHtml = $this->createDiscountAmountRender($subject, $saleableItem, $arguments);
        }
        return $proceed($amount, $arguments) . $additionalHtml;
    }

    /**
     * @param FrameworkPriceBox $subject
     * @param $saleableItem
     * @param $arguments
     * @return string
     */
    private function createDiscountAmountRender(
        FrameworkPriceBox $subject,
        $saleableItem,
        $arguments
    ) {
        $html = '';
        if ($this->canRender($saleableItem, $arguments) && $subject->getArea() != Area::AREA_ADMINHTML) {
            if ($subject->hasData('css_classes')) {
                $cssClasses = 'price-loffs_price';
                $subject->setData('css_classes', $cssClasses);
            }
            $html = $this->viewHelper->discountAmountHtml($saleableItem);
        }
        return $html;
    }

    /**
     * @param $saleableItem
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function canRender($saleableItem, $arguments)
    {
        $flashSale = $this->countDownData->getFlashSalesWithChildProduct(
            $saleableItem->getId()
        );
        $isNotFlashSale = $flashSale == null ? false : true;

        if ($saleableItem->getTypeId() === Configurable::TYPE_CODE) {
            $childConfigurableProductIds = $this->getChildConfigurableProductIds($saleableItem);
            foreach ($childConfigurableProductIds as $childProductId) {
                $flashSale = $this->countDownData->getFlashSalesWithChildProduct(
                    $childProductId
                );
                $isNotFlashSale = $flashSale == null ? false : true;
                if ($isNotFlashSale) {
                    break;
                }
            }
        }
        return $this->helperData->isEnabled()
            && $this->helperData->getEnableDiscountAmount()
            && ($saleableItem instanceof \Magento\Catalog\Model\Product)
            && $isNotFlashSale
            && isset($arguments['price_type'])
            && $arguments['price_type'] === 'oldPrice';
    }

    /**
     * @param $saleableItem
     * @return array
     */
    public function getChildConfigurableProductIds($saleableItem)
    {
        $childProductIds = [];
        foreach ($this->configurableOptionsProvider->getProducts(
            $saleableItem
        ) as $subProduct) {
            $childProductIds[] = $subProduct->getId();
        }
        return $childProductIds;
    }
}
