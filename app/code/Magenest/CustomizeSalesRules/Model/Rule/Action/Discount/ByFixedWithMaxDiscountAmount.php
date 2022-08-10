<?php
namespace Magenest\CustomizeSalesRules\Model\Rule\Action\Discount;


use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\ByFixed;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;

class ByFixedWithMaxDiscountAmount extends ByFixed
{
    /**
     * @var DeltaPriceRound
     */
    private $maxDiscountAmountCalculator;

    /**
     * @var string
     */
    private static $discountType = 'ByFixedWithMaxDiscountAmount';

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
    public function calculate($rule, $item, $qty)
    {
        $quote = $item->getQuote();
        $quoteAmount = $this->priceCurrency->convert($rule->getDiscountAmount(), $item->getQuote()->getStore());

        $possibleDiscountAmount = $quote->getItemsQty() * $quoteAmount;
        if ($possibleDiscountAmount < $rule->getMaximumDiscountAmount() || $rule->getMaximumDiscountAmount() <= 0) {
            return parent::calculate($rule, $item, $qty);
        } else {
            return $this->maxDiscountAmountCalculator->calculate($rule, $item, $qty);
        }
    }
}
