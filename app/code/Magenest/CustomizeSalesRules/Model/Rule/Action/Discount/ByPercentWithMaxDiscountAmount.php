<?php
namespace Magenest\CustomizeSalesRules\Model\Rule\Action\Discount;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\ByPercent;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;

class ByPercentWithMaxDiscountAmount extends ByPercent
{
    /**
     * @var DeltaPriceRound
     */
    private $maxDiscountAmountCalculator;

    /**
     * @var string
     */
    private static $discountType = 'ByPercentWithMaxDiscountAmount';

    /**
     * ByPercentWithMaxDiscountAmount constructor.
     * @param Validator $validator
     * @param DataFactory $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param MaxDiscountAmountCalculator $maxDiscountAmountCalculator
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        MaxDiscountAmountCalculator $maxDiscountAmountCalculator
    ) {
        $this->maxDiscountAmountCalculator = $maxDiscountAmountCalculator;
        $this->maxDiscountAmountCalculator->setType(self::$discountType);
        parent::__construct($validator, $discountDataFactory, $priceCurrency);
    }

    /**
     * @param Rule $rule
     * @param AbstractItem $item
     * @param float $qty
     * @param float $rulePercent
     * @return Data
     */
    protected function _calculate($rule, $item, $qty, $rulePercent)
    {
        $quote    = $item->getQuote();
        $_rulePct = $rulePercent / 100;

        $possibleDiscountAmount = $quote->getSubtotal() * $_rulePct;
        if ($possibleDiscountAmount < $rule->getMaximumDiscountAmount() || $rule->getMaximumDiscountAmount() <= 0) {
            return parent::_calculate($rule, $item, $qty, $rulePercent);
        } else {
            return $this->maxDiscountAmountCalculator->calculate($rule, $item, $qty);
        }
    }
}
