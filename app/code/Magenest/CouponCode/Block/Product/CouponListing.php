<?php

namespace Magenest\CouponCode\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Block\Product\Context;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;

class CouponListing extends AbstractProduct
{
    protected $coupons = [];

    /**
     * @var RuleCollection
     */
    protected $ruleCollection;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * CouponListing constructor.
     * @param RuleCollection $ruleCollection
     * @param DateTime $dateTime
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        RuleCollection $ruleCollection,
        \Magento\Framework\App\Http\Context $httpContext,
        DateTime $dateTime,
        Context $context,
        array $data = []
    ) {
        $this->ruleCollection = $ruleCollection;
        $this->dateTime = $dateTime;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    public function collectCoupon()
    {
        if (!$this->coupons) {
            $ruleCollection = $this->ruleCollection->create()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('is_rss', 1)
                ->addFieldToFilter('coupon_type', 2)
                ->addFieldToFilter('use_auto_generation', 0)
                ->addFieldToFilter('from_date', ["lteq" => $this->dateTime->gmtDate('Y-m-d')])
                ->addFieldToFilter('to_date', ["gteq" => $this->dateTime->gmtDate('Y-m-d')]);

            $product = $this->getProduct();
            $categories = $product->getCategoryIds();
            $condition = [];
            foreach ($categories as $category) {
                $condition[] = "FIND_IN_SET(ablr.banner_product_categories, {$category})";
            }

            if (count($condition) > 0) {
                $conditionStr = "OR " . implode(" OR ", $condition);
            }

            $ruleCollection->getSelect()->joinLeft(
                ['ablr' => $ruleCollection->getTable('amasty_banners_lite_rule')],
                'main_table.rule_id = ablr.salesrule_id',
                ''
            )->where('ablr.show_banner_for = 0 OR FIND_IN_SET(ablr.banner_product_sku, ?) ' . ($conditionStr ?? ""), $product->getSku())
            ->where('FIND_IN_SET(ablr.customer_group_ids, ?)', $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP));
            $this->coupons = $ruleCollection->getItems();
        }
        return $this->coupons;
    }
}
