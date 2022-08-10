<?php
namespace Magenest\CustomizeSalesRules\Model;

use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RulesApplier;

class CustomSalesRules extends RulesApplier
{
    /**
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @param Address $address
     * @param mixed $couponCode
     * @return RulesApplier
     */
    protected function applyRule($item, $rule, $address, $couponCode)
    {
        if ($rule->getData('apply_only_to_shipping')) {
            return $this;
        }
        return parent::applyRule($item, $rule, $address, $couponCode);
    }

}
