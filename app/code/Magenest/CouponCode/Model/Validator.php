<?php
namespace Magenest\CouponCode\Model;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;

class Validator extends \Magenest\CustomizeSalesRules\Model\Validator
{
    /**
     * @param $items
     * @param Address $address
     * @return Validator|\Magenest\CustomizeSalesRules\Model\Validator
     * @throws \Zend_Db_Select_Exception
     * @throws \Zend_Validate_Exception
     */
    public function initTotals($items, Address $address)
    {
        foreach ($items as $item) {
            foreach ($this->_getRules($address) as $rule) {
                if (!$item->getIsReseted()
                    && in_array($rule->getSimpleAction(), [Rule::CART_FIXED_ACTION, Rule::BY_PERCENT_ACTION, Rule::BY_FIXED_ACTION])
                    && $rule->getDiscountAmount() > 0
                    && $this->validatorUtility->canProcessRule($rule, $address)
                    && $this->isValidItemForRule($item, $rule)
                    && $rule->getApplyOnOriginalPrice()
                ) {
                    $this->preprocessItem($item);
                    break;
                }
            }
        }

        return parent::initTotals($items, $address);
    }

    /**
     * @param $item
     * @return void
     */
    private function preprocessItem($item)
    {
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
}
