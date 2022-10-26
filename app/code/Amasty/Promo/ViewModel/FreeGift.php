<?php
namespace Amasty\Promo\ViewModel;

use Amasty\BannersLite\Block\ProductBanner;
use Amasty\Promo\Model\RuleFactory;
use Magento\Framework\Pricing\Helper\Data;
class FreeGift implements \Magento\Framework\View\Element\Block\ArgumentInterface
{

    protected $rule;

    public function __construct(
        RuleFactory $rule,
        Data  $priceHelper
    )
    {
        $this->rule = $rule;
        $this->priceHelper = $priceHelper;
    }

    public function getStatus($rule_id) {
        return $this->rule->create()->load($rule_id,'salesrule_id')->getItemsDiscount();
    }
    public function getDiscountFee($rule_id){
        $discount =  $this->rule->create()->load($rule_id,'salesrule_id')->getItemsDiscount();
        if ($discount){
            $discountDetail = substr($discount,-1) ;
            if ($discountDetail != '%'){
                $discount = $this->priceHelper->currency($discount,true,false);
            }
        }
        return $discount;
    }
}
