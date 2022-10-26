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
use Magenest\Affiliate\Model\Group;
use Magenest\Affiliate\Model\ResourceModel\Account\CollectionFactory as AccountCollection;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesOrderCollection;
use Magento\Framework\Pricing\Helper\Data as PriceData;
use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Refer
 * @package Magenest\Affiliate\Block\Account
 */
class MembershipClass extends Account
{
    /**
     * @var Group
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
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var AccountCollection
     */
    private $affiliateAccount;
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
        Group $membership,
        SalesOrderCollection $collectionFactory,
        PriceData $priceHelper,
        ScopeConfigInterface $scopeConfig,
        AccountCollection $affiliateAccount,
        array $data = []
    ) {
        $this->affiliateAccount = $affiliateAccount;
        $this->priceHelper = $priceHelper;
        $this->salesOrderCollectionFactory = $collectionFactory;
        $this->membership = $membership;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $customerSession, $helperView, $affiliateHelper, $paymentHelper, $jsonHelper, $registry, $pricingHelper, $objectManager, $campaignFactory, $accountFactory, $withdrawFactory, $transactionFactory, $data);
    }

    public function getCurrentMembershipRank() {
        $customer = $this->getCustomer();
        $affiliateGroups = $this->affiliateAccount->create()
            ->addFieldToFilter('customer_id', $customer->getId())
            ->join(
                ['mag' => 'magenest_affiliate_group'],
                'main_table.group_id = mag.group_id',
                ["mag.name"]
            );
        return $affiliateGroups->getData()[0];
    }

    public function getConditionUpdateMembershipRank($currentRank) {
        $allGroups = $this->membership->getCollection()->addFieldToFilter('revenue_to_reach', ['neq' => 'NULL'])
            ->addFieldToFilter('revenue_to_keep', ['neq' => 'NULL'])
            ->setOrder('revenue_to_reach','ASC')
            ->getData();
        $groups = [];
        $revenueToReach = [];
        $upgradeGroup = [];

        foreach ($allGroups as $key => $group) {
            $groups[$key] = $group['name'];
            $revenueToReach[$key] = $group['revenue_to_reach'];
        }

        foreach ($groups as $key => $data) {
            if ($data == $currentRank) {
                if (isset($groups[$key + 1])) {
                    $upgradeGroup[$groups[$key + 1]] = $revenueToReach[$key + 1];
                } else {
                    $upgradeGroup[$data] = $revenueToReach[$key];
                }

            }
        }
        if (empty($upgradeGroup)) {
            $upgradeGroup[min($groups)] = $revenueToReach[array_search(min($groups), $groups)];
        }
        return $upgradeGroup;
    }

    public function getUpgradeAffiliateGroupId($currentRankId) {
        $allGroups = $this->membership->getCollection()->addFieldToFilter('revenue_to_reach', ['neq' => 'NULL'])
            ->addFieldToFilter('revenue_to_keep', ['neq' => 'NULL'])
            ->setOrder('revenue_to_reach','ASC')
            ->getData();
        $groups = [];
        $upgradeGroup = "";

        foreach ($allGroups as $key => $group) {
            $groups[$key] =
                [
                    'name' => $group['name'],
                    'group_id' => $group['group_id']
                ];
        }

        foreach ($groups as $key => $data) {
            if ($key == $currentRankId) {
                if (isset($groups[$key + 1])) {
                    $upgradeGroup = $key + 1;
                } else {
                    $upgradeGroup = $key;
                }

            }
        }
        if (empty($upgradeGroup)) {
            $upgradeGroup = $groups[array_search(min($groups), $groups)]['group_id'];
        }

        return $upgradeGroup;
    }

    public function getConditionTotalSalesOrderAmount($name) {
        $collection = $this->membership->getCollection()->addFieldToFilter('name', $name)->getLastItem();

        return $collection->getData('qty_order') ?? 0;
    }

    public function getMembershipTotalSales() {
        $customer = $this->getCustomer();

        $collection = $this->salesOrderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getId());

        $collection->getSelect()
            ->reset('columns')
            ->columns('COUNT(entity_id) as entity_id')
            ->columns('SUM(subtotal) as subtotal')
            ->group('customer_id');


        $getAffiliateCommissionConfig = $this->scopeConfig->getValue('affiliate/commission/process/earn_commission_invoice');

        if ($getAffiliateCommissionConfig == 1) {
            $collection->getSelect()->where(new \Zend_Db_Expr('state = "complete" OR state = "processing"'));
        } else {
            $collection->getSelect()->where('state = ?', 'complete');
        }

        return $collection;
    }

    public function getMembershipTotalSalesCurrentRank($currentRank) {
        $customer = $this->getCustomer();

        $collection = $this->salesOrderCollectionFactory->create()
            ->addFieldToFilter('customer_id', $customer->getId());
        $affiliateGroupId = $this->getUpgradeAffiliateGroupId($currentRank);
        $affiliateGroup = $this->membership->getCollection()->addFieldToFilter('group_id', $affiliateGroupId)->getLastItem();

        $collection->getSelect()
            ->reset('columns')
            ->where('created_at >= "'. $affiliateGroup->getData()['created_at'] . '"')
            ->columns('COUNT(entity_id) as entity_id')
            ->columns('SUM(subtotal) as subtotal')
            ->group('customer_id');

        $getAffiliateCommissionConfig = $this->scopeConfig->getValue('affiliate/commission/process/earn_commission_invoice');

        if ($getAffiliateCommissionConfig == 1) {
            $collection->getSelect()->where(new \Zend_Db_Expr('state = "complete" OR state = "processing"'));
        } else {
            $collection->getSelect()->where('state = ?', 'complete');
        }

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
