<?php

namespace Magenest\CouponCode\Model;

//use Amasty\Promo\Helper\Item;
//use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\RulesApplier;
//use \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
//use \Magento\SalesRule\Model\Utility;

class OriginalPriceApplier extends RulesApplier
{
//    protected $itemHelper;
//
//    public function __construct(
//        CalculatorFactory $calculatorFactory,
//        \Magento\Framework\Event\ManagerInterface $eventManager,
//        Utility $utility,
//        Item $item,
//        ChildrenValidationLocator $childrenValidationLocator = null
//    ) {
//        parent::__construct($calculatorFactory, $eventManager, $utility, $childrenValidationLocator);
//        $this->itemHelper = $item;
//    }

    protected function applyRule($item, $rule, $address, $couponCode)
    {
//        if ($rule->getApplyOnOriginalPrice() && !$item->getIsReseted() && !$this->itemHelper->isPromoItem($item)) {
        if ($rule->getApplyOnOriginalPrice() && !$item->getIsReseted()) {
            $item->setDiscountAmount(0);
            $item->setBaseDiscountAmount(0);
            $item->setPrice($item->getOriginalPrice());
            $item->setPriceInclTax($item->getOriginalPrice());
            $item->setBasePrice($item->getBaseOriginalPrice());
            $item->setBasePriceInclTax($item->getBaseOriginalPrice());
            $item->setRowTotal($item->getPrice()*$item->getQty());
            $item->setBaseRowTotal($item->getBasePrice()*$item->getQty());
            $item->setRowTotalInclTax($item->getPrice()*$item->getQty());
            $item->setBaseRowTotalInclTax($item->getBasePrice()*$item->getQty());
            $item->setDiscountCalculationPrice($item->getOriginalPrice());
            $item->setBaseDiscountCalculationPrice($item->getBaseOriginalPrice());
            $item->setCalculationPrice($item->getOriginalPrice());
            $item->setBaseCalculationPrice($item->getBaseOriginalPrice());
            $item->setTaxCalculationPrice($item->getOriginalPrice());
            $item->setBaseTaxCalculationPrice($item->getBaseOriginalPrice());
            $item->setIsReseted(true);
            $item->setConvertedPrice(null);
        }

        return parent::applyRule($item, $rule, $address, $couponCode); // TODO: Change the autogenerated stub
    }
}
