<?php


namespace Magenest\Affiliate\Block\Account;

use Magenest\Affiliate\Block\Account;
use Magenest\Affiliate\Helper\Data as AffiliateHelper;
use Magenest\Affiliate\Helper\Payment;
use Magenest\Affiliate\Model\AccountFactory;
use Magenest\Affiliate\Model\CampaignFactory;
use Magenest\Affiliate\Model\TransactionFactory;
use Magenest\Affiliate\Model\WithdrawFactory;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magenest\RewardPoints\Model\Membership;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesOrderCollection;
use Magento\Framework\Pricing\Helper\Data as PriceData;

/**
 * Class Refer
 * @package Magenest\Affiliate\Block\Account
 */
class MembershipClass extends Account
{
    /**
     * @var Membership
     */
    private $membership;
    /**
     * @var SalesOrderCollection
     */
    private $salesOrderCollectionFactory;
    /**
     * @var PriceData
     */
    private $priceHelper;
    public function __construct(
        Context $context,
        Session $customerSession,
        View $helperView,
        AffiliateHelper $affiliateHelper,
        Payment $paymentHelper,
        JsonHelper $jsonHelper,
        Registry $registry,
        PriceHelper $pricingHelper,
        ObjectManagerInterface $objectManager,
        CampaignFactory $campaignFactory,
        AccountFactory $accountFactory,
        WithdrawFactory $withdrawFactory,
        TransactionFactory $transactionFactory,
        Membership $membership,
        SalesOrderCollection $collectionFactory,
        PriceData $priceHelper,
        array $data = []
    ) {
        $this->priceHelper = $priceHelper;
        $this->salesOrderCollectionFactory = $collectionFactory;
        $this->membership = $membership;
        parent::__construct($context, $customerSession, $helperView, $affiliateHelper, $paymentHelper, $jsonHelper, $registry, $pricingHelper, $objectManager, $campaignFactory, $accountFactory, $withdrawFactory, $transactionFactory, $data);
    }

    public function getCurrentMembershipRank($totalSales,$countTotalOrders) {
        $membershipRank = [];
        $membershipRankOnly = [];
        $finalValue = [];
        $collection = $this->membership->getCollection()->setOrder('condition_reach_tier_value', 'DESC')->getData();

        foreach($collection as $data) {
            $membershipRank[$data['name']] = [
                'total_sales'  => $data['condition_reach_tier_value'],
                'total_orders' => $data['added_value_amount']
            ];
        }

        foreach ($membershipRank as $key => $value) {
            if ($totalSales < $value['total_sales'] || $value['total_orders'] > $countTotalOrders) {
                unset($membershipRank[$key]);
            }
        }

        foreach ($membershipRank as $key => $value) {
            $membershipRankOnly[$key] = $value["total_sales"];
        }


        if (!empty($membershipRankOnly)) {
            $membershipRankKey = array_search(max($membershipRankOnly), $membershipRankOnly);

            $membershipRankValue = max($membershipRankOnly);

            $finalValue[$membershipRankKey] = $membershipRankValue;
        }

        return $finalValue;
    }

    public function getConditionUpdateMembershipRank($totalSales, $countTotalOrders) {
        $membershipRank = [];
        $membershipRankOnly = [];
        $finalValue = [];
        $collection = $this->membership->getCollection()->setOrder('condition_reach_tier_value', 'ASC')->getData();

        foreach($collection as $data) {
            $membershipRank[$data['name']] = [
                'total_sales'  => $data['condition_reach_tier_value'],
                'total_orders' => $data['added_value_amount']
            ];
        }

        foreach ($membershipRank as $key => $value) {
            if ($totalSales >= $value['total_sales'] && $value['total_orders'] <= $countTotalOrders) {
                unset($membershipRank[$key]);
            }
        }

        foreach ($membershipRank as $key => $value) {
            $membershipRankOnly[$key] = $value["total_sales"];
        }

        $membershipRankKey = array_search(min($membershipRankOnly),$membershipRankOnly);

        $membershipRankValue = min($membershipRankOnly);

        $finalValue[$membershipRankKey] = $membershipRankValue;

        return $finalValue;
    }

    public function getConditionTotalSalesOrderAmount($name) {
        $collection = $this->membership->getCollection()->addFieldToFilter('name', $name)->getLastItem();

        return $collection->getData('added_value_amount');
    }

    public function getMembershipTotalSales() {
        $customer = $this->getCustomer();

        $collection = $this->salesOrderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getId());

        return $collection;
    }
    public function getFormatPrice($price) {
        return $this->pricingHelper->currency($price);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Membership Class'));

        return parent::_prepareLayout();
    }
}
