<?php

namespace Magenest\CustomizeSalesRules\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Helper\CartFixedDiscount;
use Magento\SalesRule\Model\Rule;

class Validator extends \Magento\SalesRule\Model\Validator
{
    /**
     * @var CartFixedDiscount
     */
    private $cartFixedDiscountHelper;

    public function initTotals($items, Address $address)
    {
        $address->setCartFixedRules([]);

        if (!$items) {
            return $this;
        }

        /** @var Rule $rule */
        foreach ($this->_getRules($address) as $rule) {
            if ((Rule::CART_FIXED_ACTION == $rule->getSimpleAction()
                    || (in_array($rule->getSimpleAction(), [Rule::BY_PERCENT_ACTION, Rule::BY_FIXED_ACTION]) && $rule->getMaximumDiscountAmount() > 0))
                && $this->validatorUtility->canProcessRule($rule, $address)
            ) {
                $ruleTotalItemsPrice = 0;
                $ruleTotalBaseItemsPrice = 0;
                $validItemsCount = 0;

                foreach ($items as $item) {
                    //Skipping child items to avoid double calculations
                    if (!$this->isValidItemForRule($item, $rule)) {
                        continue;
                    }
                    $qty = $this->validatorUtility->getItemQty($item, $rule);
                    $ruleTotalItemsPrice += $this->getItemPrice($item) * $qty;
                    $ruleTotalBaseItemsPrice += $this->getItemBasePrice($item) * $qty;
                    $validItemsCount++;
                }

                $this->_rulesItemTotals[$rule->getId()] = [
                    'items_price' => $ruleTotalItemsPrice,
                    'base_items_price' => $ruleTotalBaseItemsPrice,
                    'items_count' => $validItemsCount,
                ];
            }
        }

        return $this;
    }

    /**
     * Determine if quote item is valid for a given sales rule
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    private function isValidItemForRule(AbstractItem $item, Rule $rule)
    {
        if ($item->getParentItemId()) {
            return false;
        }
        if ($item->getParentItem()) {
            return false;
        }
        if (!$rule->getActions()->validate($item)) {
            return false;
        }
        if (!$this->canApplyDiscount($item)) {
            return false;
        }
        return true;
    }

    /**
     * Apply discounts to shipping amount
     *
     * @param Address $address
     * @return \Magento\SalesRule\Model\Validator
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Zend_Db_Select_Exception
     */
    public function processShippingAmount(Address $address)
    {
        $this->initCartFixedDiscount();
        $shippingAmount = $address->getShippingAmountForDiscount();
        if (!empty($shippingAmount)) {
            $baseShippingAmount = $address->getBaseShippingAmountForDiscount();
        } else {
            $shippingAmount = $address->getShippingAmount();
            $baseShippingAmount = $address->getBaseShippingAmount();
        }
        $quote = $address->getQuote();
        $appliedRuleIds = [];
        foreach ($this->_getRules($address) as $rule) {
            /* @var Rule $rule */
            if (!$rule->getApplyToShipping() || !$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            $rulePercent = min(100, $rule->getDiscountAmount());
            switch ($rule->getSimpleAction()) {
                case Rule::TO_PERCENT_ACTION:
                    $rulePercent = max(0, 100 - $rule->getDiscountAmount());
                // break is intentionally omitted
                // no break
                case Rule::BY_PERCENT_ACTION:
                    $total = $address->getSubtotal() + $address->getShippingAmount();
                    $possibleDiscountAmount = $total * $rulePercent/100;
                    if ($possibleDiscountAmount < $rule->getMaximumDiscountAmount() || $rule->getMaximumDiscountAmount() <= 0) {
                        $discountAmount = ($shippingAmount - $address->getShippingDiscountAmount()) * $rulePercent / 100;
                        $baseDiscountAmount = ($baseShippingAmount -
                                $address->getBaseShippingDiscountAmount()) * $rulePercent / 100;
                        $discountPercent = min(100, $address->getShippingDiscountPercent() + $rulePercent);
                        $address->setShippingDiscountPercent($discountPercent);
                    } else {
                        [$discountAmount, $baseDiscountAmount] = $this->maxDiscountAmountCalculator($address, $rule, $quote, $baseShippingAmount);
                    }

                    break;
                case Rule::TO_FIXED_ACTION:
                    $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $quote->getStore());
                    $discountAmount = $shippingAmount - $quoteAmount;
                    $baseDiscountAmount = $baseShippingAmount - $rule->getDiscountAmount();
                    break;
                case Rule::BY_FIXED_ACTION:
                    $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $quote->getStore());
                    $possibleDiscountAmount = $quoteAmount * ($quote->getItemsQty() + 1);
                    if ($possibleDiscountAmount < $rule->getMaximumDiscountAmount() || $rule->getMaximumDiscountAmount() <= 0) {
                        $discountAmount = $quoteAmount;
                        $baseDiscountAmount = $rule->getDiscountAmount();
                    } else {
                        [$discountAmount, $baseDiscountAmount] = $this->maxDiscountAmountCalculator($address, $rule, $quote, $baseShippingAmount);
                    }

                    break;
                case Rule::CART_FIXED_ACTION:
                    $cartRules = $address->getCartFixedRules();
                    $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $quote->getStore());
                    $isAppliedToShipping = (int) $rule->getApplyToShipping();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }
                    if ($cartRules[$rule->getId()] > 0) {
                        $shippingAmount = $address->getShippingAmount() - $address->getShippingDiscountAmount();
                        $quoteBaseSubtotal = (float) $quote->getBaseSubtotal();
                        $isMultiShipping = $this->cartFixedDiscountHelper->checkMultiShippingQuote($quote);
                        if ($isAppliedToShipping) {
                            $quoteBaseSubtotal = ($quote->getIsMultiShipping() && $isMultiShipping) ?
                                $this->cartFixedDiscountHelper->getQuoteTotalsForMultiShipping($quote) :
                                $this->cartFixedDiscountHelper->getQuoteTotalsForRegularShipping(
                                    $address,
                                    $quoteBaseSubtotal
                                );
                            $discountAmount = $this->cartFixedDiscountHelper->
                            getShippingDiscountAmount(
                                $rule,
                                $shippingAmount,
                                $quoteBaseSubtotal
                            );
                            $baseDiscountAmount = $discountAmount;
                        } else {
                            $discountAmount = min($shippingAmount, $quoteAmount);
                            $baseDiscountAmount = min(
                                $baseShippingAmount - $address->getBaseShippingDiscountAmount(),
                                $cartRules[$rule->getId()]
                            );
                        }
                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);
                    break;
                case Rule::BUY_X_GET_Y_ACTION:
                    $allQtyDiscount = $this->getDiscountQtyAllItemsBuyXGetYAction($quote, $rule);
                    $quoteAmount = $address->getBaseShippingAmount() / $quote->getItemsQty() * $allQtyDiscount;
                    $discountAmount = $this->priceCurrency->convert($quoteAmount, $quote->getStore());
                    $baseDiscountAmount = $quoteAmount;
                    break;
            }

            $discountAmount = min($address->getShippingDiscountAmount() + $discountAmount, $shippingAmount);
            $baseDiscountAmount = min(
                $address->getBaseShippingDiscountAmount() + $baseDiscountAmount,
                $baseShippingAmount
            );
            $address->setShippingDiscountAmount($this->priceCurrency->roundPrice($discountAmount));
            $address->setBaseShippingDiscountAmount($this->priceCurrency->roundPrice($baseDiscountAmount));
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            $this->rulesApplier->maintainAddressCouponCode($address, $rule, $this->getCouponCode());
            $this->rulesApplier->addDiscountDescription($address, $rule);
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        $address->setAppliedRuleIds($this->validatorUtility->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->validatorUtility->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));

        return $this;
    }

    protected function initCartFixedDiscount()
    {
        if (!$this->cartFixedDiscountHelper) {
            $this->cartFixedDiscountHelper = ObjectManager::getInstance()->get(CartFixedDiscount::class);
        }
    }

    protected function maxDiscountAmountCalculator($address, $rule, $quote, $baseShippingAmount)
    {
        $cartRules = $address->getMaximumDiscountRules();
        $quoteAmount = $this->priceCurrency->convert($rule->getMaximumDiscountAmount(), $quote->getStore());
        $isAppliedToShipping = (int) $rule->getApplyToShipping();
        if (!isset($cartRules[$rule->getId()])) {
            $cartRules[$rule->getId()] = $rule->getMaximumDiscountAmount();
        }
        if ($cartRules[$rule->getId()] > 0) {
            $shippingAmount = $address->getShippingAmount() - $address->getShippingDiscountAmount();
            $quoteBaseSubtotal = (float) $quote->getBaseSubtotal();
            $isMultiShipping = $this->cartFixedDiscountHelper->checkMultiShippingQuote($quote);
            if ($isAppliedToShipping) {
                $quoteBaseSubtotal = ($quote->getIsMultiShipping() && $isMultiShipping) ?
                    $this->cartFixedDiscountHelper->getQuoteTotalsForMultiShipping($quote) :
                    $this->cartFixedDiscountHelper->getQuoteTotalsForRegularShipping(
                        $address,
                        $quoteBaseSubtotal
                    );
                $originalDiscountAmount = $rule->getDiscountAmount();
                $rule->setDiscountAmount($rule->getMaximumDiscountAmount());
                $discountAmount = $this->cartFixedDiscountHelper->
                getShippingDiscountAmount(
                    $rule,
                    $shippingAmount,
                    $quoteBaseSubtotal
                );
                $rule->setDiscountAmount($originalDiscountAmount);
                $baseDiscountAmount = $discountAmount;
            } else {
                $discountAmount = min($shippingAmount, $quoteAmount);
                $baseDiscountAmount = min(
                    $baseShippingAmount - $address->getBaseShippingDiscountAmount(),
                    $cartRules[$rule->getId()]
                );
            }
            $cartRules[$rule->getId()] -= $baseDiscountAmount;
        }
        $address->setMaximumDiscountRules($cartRules);
        return [$discountAmount ?? 0, $baseDiscountAmount ?? 0];
    }
}
