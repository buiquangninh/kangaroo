<?php


namespace Magenest\CustomizeSalesRules\Model\Rule\Action\Discount;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;

class MaxDiscountAmountCalculator extends Rule\Action\Discount\AbstractDiscount
{
    /**
     * @var CartFixedDiscount
     */
    private $cartFixedDiscountHelper;

    /**
     * @var DeltaPriceRound
     */
    private $deltaPriceRound;

    /**
     * @var string
     */
    private $discountType;

    /**
     * ByPercentWithMaxDiscountAmount constructor.
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param CartFixedDiscount $cartFixedDiscount
     * @param DeltaPriceRound $deltaPriceRound
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        CartFixedDiscount $cartFixedDiscount,
        DeltaPriceRound $deltaPriceRound
    ) {
        $this->deltaPriceRound = $deltaPriceRound;
        $this->cartFixedDiscountHelper = $cartFixedDiscount;
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }
    public function setType($type)
    {
        $this->discountType = $type;
    }

    public function calculate($rule, $item, $qty)
    {
        /** @var Data $discountData */
        $discountData = $this->discountFactory->create();

        $ruleTotals = $this->validator->getRuleItemTotalsInfo($rule->getId());
        $baseRuleTotals = $ruleTotals['base_items_price'] ?? 0.0;

        $address = $item->getAddress();
        $shippingMethod = $address->getShippingMethod();
        $isAppliedToShipping = (int) $rule->getApplyToShipping();
        $quote = $item->getQuote();
        $ruleDiscount = (float) $rule->getMaximumDiscountAmount();

        $isMultiShipping = $this->cartFixedDiscountHelper->checkMultiShippingQuote($quote);
        $itemPrice = $this->validator->getItemPrice($item);
        $baseItemPrice = $this->validator->getItemBasePrice($item);
        $itemOriginalPrice = $this->validator->getItemOriginalPrice($item);
        $baseItemOriginalPrice = $this->validator->getItemBaseOriginalPrice($item);

        $cartRules = $quote->getMaximumDiscountRules();
        if (!isset($cartRules[$rule->getId()])) {
            $cartRules[$rule->getId()] = $rule->getMaximumDiscountAmount();
        }
        $availableDiscountAmount = (float) $cartRules[$rule->getId()];
        $discountType = $this->discountType . $rule->getId();

        if ($availableDiscountAmount > 0) {
            $store = $quote->getStore();
            $baseRuleTotals = $shippingMethod ?
                $this->cartFixedDiscountHelper
                    ->getBaseRuleTotals(
                        $isAppliedToShipping,
                        $quote,
                        $isMultiShipping,
                        $address,
                        $baseRuleTotals
                    ) : $baseRuleTotals;
            $maximumItemDiscount =$this->cartFixedDiscountHelper
                ->getDiscountAmount(
                    $ruleDiscount,
                    $qty,
                    $baseItemPrice,
                    $baseRuleTotals,
                    $discountType
                );
            $quoteAmount = $this->priceCurrency->convert($maximumItemDiscount, $store);
            $baseDiscountAmount = min($baseItemPrice * $qty, $maximumItemDiscount);
            if ($ruleTotals['items_count'] <= 1) {
                $this->deltaPriceRound->reset($discountType);
            } else {
                $this->validator->decrementRuleItemTotalsCount($rule->getId());
            }

            $baseDiscountAmount = $this->priceCurrency->roundPrice($baseDiscountAmount);

            $availableDiscountAmount = $this->cartFixedDiscountHelper
                ->getAvailableDiscountAmount(
                    $rule,
                    $quote,
                    $isMultiShipping,
                    $cartRules,
                    $baseDiscountAmount,
                    $availableDiscountAmount
                );
            $cartRules[$rule->getId()] = $availableDiscountAmount;
            if ($isAppliedToShipping &&
                $isMultiShipping &&
                $ruleTotals['items_count'] <= 1) {
                $estimatedShippingAmount = (float) $address->getBaseShippingInclTax();
                $shippingDiscountAmount = $this->cartFixedDiscountHelper->
                getShippingDiscountAmount(
                    $rule,
                    $estimatedShippingAmount,
                    $baseRuleTotals
                );
                $cartRules[$rule->getId()] -= $shippingDiscountAmount;
                if ($cartRules[$rule->getId()] < 0.0) {
                    $baseDiscountAmount += $cartRules[$rule->getId()];
                    $quoteAmount += $cartRules[$rule->getId()];
                }
            }
            if ($availableDiscountAmount <= 0) {
                $this->deltaPriceRound->reset($discountType);
            }

            $discountData->setAmount($this->priceCurrency->roundPrice(min($itemPrice * $qty, $quoteAmount)));
            $discountData->setBaseAmount($baseDiscountAmount);
            $discountData->setOriginalAmount(min($itemOriginalPrice * $qty, $quoteAmount));
            $discountData->setBaseOriginalAmount($this->priceCurrency->roundPrice($baseItemOriginalPrice));
        }
        $quote->setMaximumDiscountRules($cartRules);

        return $discountData;
    }
}
