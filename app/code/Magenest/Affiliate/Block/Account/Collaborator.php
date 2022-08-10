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
use Magenest\RewardPoints\Api\GetReferralCodeByCustomerInterface;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
/**
 * Class Refer
 * @package Magenest\Affiliate\Block\Account
 */
class Collaborator extends Account
{
    /**
     * @var GetReferralCodeByCustomerInterface
     */
    protected $getReferralCodeByCustomer;


    /**
     * @var AccountCollection
     */
    protected AccountCollection $accountCollection;

    protected ResourceConnection $resoureConnection;

    protected PriceCurrencyInterface $priceCurrency;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param View $helperView
     * @param AffiliateHelper $affiliateHelper
     * @param Payment $paymentHelper
     * @param JsonHelper $jsonHelper
     * @param Registry $registry
     * @param PriceHelper $pricingHelper
     * @param ObjectManagerInterface $objectManager
     * @param CampaignFactory $campaignFactory
     * @param AccountFactory $accountFactory
     * @param WithdrawFactory $withdrawFactory
     * @param TransactionFactory $transactionFactory
     * @param GetReferralCodeByCustomerInterface $getReferralCodeByCustomer
     * @param array $data
     */
    public function __construct(
        Context                            $context,
        Session                            $customerSession,
        View                               $helperView,
        AffiliateHelper                    $affiliateHelper,
        Payment                            $paymentHelper,
        JsonHelper                         $jsonHelper,
        Registry                           $registry,
        PriceHelper                        $pricingHelper,
        ObjectManagerInterface             $objectManager,
        CampaignFactory                    $campaignFactory,
        AccountFactory                     $accountFactory,
        WithdrawFactory                    $withdrawFactory,
        TransactionFactory                 $transactionFactory,
        GetReferralCodeByCustomerInterface $getReferralCodeByCustomer,
        AccountCollection                  $accountCollection,
        ResourceConnection                 $resourceConnection,
        PriceCurrencyInterface             $priceCurrency,
        array                              $data = []
    ) {
        $this->getReferralCodeByCustomer = $getReferralCodeByCustomer;
        $this->accountCollection = $accountCollection;
        $this->resoureConnection = $resourceConnection;
        $this->priceCurrency     = $priceCurrency;
        parent::__construct(
            $context,
            $customerSession,
            $helperView,
            $affiliateHelper,
            $paymentHelper,
            $jsonHelper,
            $registry,
            $pricingHelper,
            $objectManager,
            $campaignFactory,
            $accountFactory,
            $withdrawFactory,
            $transactionFactory,
            $data
        );
    }

    /**
     * @return string
     */
    public function getSendMailUrl()
    {
        return $this->getUrl('*/*/referemail');
    }

    /**
     * @return string
     */
    public function getSharingParam()
    {
        return $this->_affiliateHelper->getSharingParam();
    }

    /**
     * @return string
     */
    public function getSharingEmail()
    {
        return $this->getCustomer()->getEmail();
    }

    /**
     * @return mixed
     */
    public function getSharingCode()
    {
        return $this->getCurrentAccount()->getCode() ??
            $this->getReferralCodeByCustomer->execute($this->getCustomer()->getId());
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getSocialContent()
    {
        $content = $this->_affiliateHelper->getDefaultMessageShareViaSocial();
        $storeModel = $this->_storeManager->getStore();

        return str_replace([
            '{{store_name}}',
            '{{refer_url}}'
        ], [
            $storeModel->getFrontendName(),
            $this->getSharingUrl()
        ], $content);
    }

    /**
     * @return string
     */
    public function getSharingUrl()
    {
        return $this->_affiliateHelper->getSharingUrl();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEmailContent()
    {
        $content = $this->_affiliateHelper->getDefaultEmailBody();
        $storeModel = $this->_storeManager->getStore();

        return str_replace([
            '{{store_name}}',
            '{{refer_url}}',
            '{{account_name}}'
        ], [
            $storeModel->getFrontendName(),
            $this->getSharingUrl(),
            $this->getCustomer()->getName()
        ], $content);
    }

    public function getReferredAccount() {
        $format = 'Y-m-d';

        $currentDate = $this->_localeDate->date()->format($format);

        $startDate = $this->_request->getParam('from_date') ??
            date('Y-m-d', strtotime($currentDate. ' - 30 days'));
        $endDateParam = $this->_request->getParam('to_date');
        $endDate = $endDateParam ?
            date('Y-m-d', strtotime($endDateParam. ' +1 days')) :
            date('Y-m-d', strtotime($currentDate. ' +1 days'));

        $customer = $this->getCustomer();

        $getParentAccount = $this->accountCollection->addFieldToFilter('customer_id', $customer->getId())->getLastItem()->getData();


        $this->accountCollection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);

        $accountId = $getParentAccount['account_id'];

        $getReferredAccount = $this->accountCollection
            ->addFieldToFilter('parent', $accountId)
            ->addFieldToSelect('account_id')
            ->addFieldToSelect('telephone')
            ->join(
                array("ce" => "customer_entity"),
                "main_table.customer_id = ce.entity_id",
                ['firstname','lastname','email']
            )
            ->join(
                array("so" => "sales_order"),
                "main_table.customer_id = so.customer_id",
                ['created_at','subtotal']
            )
            ->join(
                array("mat"=>"magenest_affiliate_transaction"),
                "main_table.account_id = mat.account_id",
                ['amount']
            )
            ->addFieldToFilter("mat.action","order/invoice")
            ->addFieldToFilter('so.created_at', ["gteq" => $startDate])
            ->addFieldToFilter('so.created_at', ["lteq" => $endDate]);

        if ($getReferredAccount->getSize()) {
            // create pager block for collection
            $pager = $this->getLayout()->getBlock('affiliate.collaborator.pager');
            if (!$pager) {
                $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'affiliate.collaborator.pager');
            }
            // assign collection to pager
            $limit = $this->_request->getParam('limit') ?: 10;
            $pager->setLimit($limit)->setCollection($getReferredAccount);
            $this->setChild('pager', $pager);// set pager block in layout
        }


        return $getReferredAccount->getData();
    }

    public function getPriceCurrency($price){
        return $this->priceCurrency->convertAndFormat($price);
    }

    public function getCountReferredAccount(){
        $customer = $this->getCustomer();

        $getParentAccount = $this->accountCollection->addFieldToFilter('customer_id', $customer->getId())->getLastItem()->getData();


        $this->accountCollection->getSelect()->reset(\Magento\Framework\DB\Select::WHERE);

        $accountId = $getParentAccount['account_id'];

        $count = 0;
        if(!empty($this->getReferredAccount())){
            $count = $this->accountCollection
                ->addFieldToFilter('parent', $accountId)
                ->join(
                    array("ce" => "customer_entity"),
                    "main_table.customer_id = ce.entity_id"
                )
                ->count();
        }

        return $count;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Sponsor Collaborator'));

        return parent::_prepareLayout();
    }
}
