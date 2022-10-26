<?php


namespace Magenest\Affiliate\Block\Account;

use Magenest\Affiliate\Block\Account;
use Magenest\Affiliate\Helper\Data as AffiliateHelper;
use Magenest\Affiliate\Helper\Payment;
use Magenest\Affiliate\Model\AccountFactory;
use Magenest\Affiliate\Model\CampaignFactory;
use Magenest\Affiliate\Model\TransactionFactory;
use Magenest\Affiliate\Model\WithdrawFactory;
use Magenest\AffiliateOpt\Model\ResourceModel\Accounts\Collection as AccountCollection;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as SalesOrderCollection;

/**
 * Class Refer
 * @package Magenest\Affiliate\Block\Account
 */
class General extends Account
{
    /**
     * @var SalesOrderCollection
     */
    private $salesOrderCollectionFactory;
    /**
     * @var AccountCollection
     */
    protected AccountCollection $accountCollection;
    /**
     * @var PriceCurrencyInterface
     */
    protected PriceCurrencyInterface $priceCurrency;

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
        AccountCollection $accountCollection,
        SalesOrderCollection $salesOrderCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->accountCollection = $accountCollection;
        parent::__construct($context, $customerSession, $helperView, $affiliateHelper, $paymentHelper, $jsonHelper, $registry, $pricingHelper, $objectManager, $campaignFactory, $accountFactory, $withdrawFactory, $transactionFactory, $data);
    }

    public function getAllTotalSalesByFilterDate($date=null) {
        $format = 'Y-m-d';

        $currentDate = $this->_localeDate->date()->format($format);

        if($date == "this_month") {
            $startDate = date('Y-m-01', strtotime($currentDate));
            $endDate = date('Y-m-t', strtotime($currentDate));
        } else if($date == "today") {
            $startDate = date('Y-m-d', strtotime($currentDate));
            $endDate = date('Y-m-d', strtotime($currentDate));
        }



        $customer = $this->getCustomer();

        $collection = $this->salesOrderCollectionFactory->create()
            ->addFieldToSelect('customer_id')
            ->addFieldToFilter('customer_id', $customer->getId());

        if (isset($startDate) && isset($endDate)) {
            if($startDate == $endDate){
                $collection->addFieldToFilter('main_table.created_at', ["gteq" => $startDate]);
            } else {
                $collection->addFieldToFilter('main_table.created_at', ["gteq" => $startDate])
                    ->addFieldToFilter('main_table.created_at', ["lteq" => $endDate]);
            }
        }

        $collection->getSelect()
            ->reset('columns')
            ->columns('SUM(affiliate_commission) as affiliate_commission')
            ->columns('SUM(subtotal) as subtotal')
            ->group('customer_id');

        return $collection;
    }

    public function getCompleteTotalSalesByFilterDate($date = NULL){
        $format = 'Y-m-d';

        $currentDate = $this->_localeDate->date()->format($format);

        if($date == "this_month") {
            $startDate = date('Y-m-01', strtotime($currentDate));
            $endDate = date('Y-m-t', strtotime($currentDate));
        } else if($date == "today") {
            $startDate = date('Y-m-d', strtotime($currentDate));
            $endDate = date('Y-m-d', strtotime($currentDate));
        }



        $customer = $this->getCustomer();

        $collection = $this->salesOrderCollectionFactory->create()
            ->addFieldToSelect('customer_id')
            ->addFieldToFilter('customer_id', $customer->getId())
            ->addFieldToFilter('status',"complete")
            ->addFieldToFilter("state","complete");

        if (isset($startDate) && isset($endDate)) {
            if($startDate == $endDate){
                $collection->addFieldToFilter('main_table.created_at', ["gteq" => $startDate]);
            } else {
                $collection->addFieldToFilter('main_table.created_at', ["gteq" => $startDate])
                    ->addFieldToFilter('main_table.created_at', ["lteq" => $endDate]);
            }
        }

        $collection->getSelect()
            ->reset('columns')
            ->columns('SUM(affiliate_commission) as affiliate_commission')
            ->columns('SUM(subtotal) as subtotal')
            ->group('customer_id');

        return $collection;
    }

    public function getPriceCurrency($price){
        return $this->priceCurrency->convertAndFormat($price);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Sale with KangarooShopping'));

        return parent::_prepareLayout();
    }
}
