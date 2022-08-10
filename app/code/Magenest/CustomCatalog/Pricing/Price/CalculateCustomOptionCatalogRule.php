<?php

namespace Magenest\CustomCatalog\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\PriceModifierInterface;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Calculates prices of custom options of the product with catalog rules applied.
 */
class CalculateCustomOptionCatalogRule
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var PriceModifierInterface
     */
    private $priceModifier;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param PriceModifierInterface $priceModifier
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        PriceModifierInterface $priceModifier
    ) {
        $this->priceModifier = $priceModifier;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Calculate prices of custom options of the product with catalog rules applied.
     *
     * @param Product $product
     * @param float $optionPriceValue
     * @param bool $isPercent
     * @param bool $isApplyCatalogPriceRule
     * @return float|null
     */
    public function execute(
        Product $product,
        float $optionPriceValue,
        bool $isPercent,
        bool $isApplyCatalogPriceRule = true
    ): ?float {
        $regularPrice = (float)$product->getPriceInfo()
            ->getPrice(RegularPrice::PRICE_CODE)
            ->getValue();
        $catalogRulePrice = $this->priceModifier->modifyPrice(
            $regularPrice,
            $product
        );
        // Apply catalog price rules to product options only if catalog price rules are applied to product.
        if ($catalogRulePrice < $regularPrice) {
            $optionPrice = $this->getOptionPriceWithoutPriceRule($optionPriceValue, $isPercent, $regularPrice);

            if ($isApplyCatalogPriceRule) {
                $totalCatalogRulePrice = $this->priceModifier->modifyPrice(
                    $regularPrice + $optionPrice,
                    $product
                );
                $finalOptionPrice = $this->priceCurrency->convertAndRound($totalCatalogRulePrice - $catalogRulePrice);
            } else {
                $finalOptionPrice = $this->priceCurrency->convertAndRound($optionPrice);
            }

            return $finalOptionPrice;
        }

        return null;
    }

    /**
     * Calculate option price without catalog price rule discount.
     *
     * @param float $optionPriceValue
     * @param bool $isPercent
     * @param float $basePrice
     * @return float
     */
    private function getOptionPriceWithoutPriceRule(float $optionPriceValue, bool $isPercent, float $basePrice): float
    {
        return $isPercent ? $basePrice * $optionPriceValue / 100 : $optionPriceValue;
    }
}
