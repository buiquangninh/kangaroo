<?php

namespace Magenest\RewardPoints\Observer;

use Exception;
use Magenest\Affiliate\Model\AccountFactory as AccountAffiliateFactory;
use Magenest\RewardPoints\Cookie\ReferralCode;
use Magenest\RewardPoints\Helper\Data;
use Magenest\RewardPoints\Model\AccountFactory;
use Magenest\RewardPoints\Model\ExpiredFactory;
use Magenest\RewardPoints\Model\ReferralFactory;
use Magenest\RewardPoints\Model\Rule;
use Magenest\RewardPoints\Model\RuleFactory;
use Magenest\RewardPoints\Model\TransactionFactory;
use Magento\Backend\App\ConfigInterface;
use Magento\CatalogRule\Model\RuleFactory as CoreRuleFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PlaceOrder
 * @package Magenest\RewardPoints\Observer
 */
class PlaceOrder implements ObserverInterface
{
    /**
     * @var AccountFactory
     */
    protected $_accountFactory;

    /**
     * @var TransactionFactory
     */
    protected $_transactionFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ExpiredFactory
     */
    protected $expiredFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var CoreRuleFactory
     */
    protected $_coreRuleFactory;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var
     */
    protected $_order;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var
     */
    protected $_quote;

    /**
     * @var ConfigInterface
     */
    protected $_config;

    /**
     * @var ReferralCode
     */
    protected $referralCodeCookie;

    /**
     * @var AccountAffiliateFactory
     */
    protected $accountAffiliateFactory;

    /**
     * @var ReferralFactory
     */
    protected $referralFactory;

    /**
     * System event manager
     *
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * PlaceOrder constructor.
     *
     * @param AccountFactory $accountFactory
     * @param ConfigInterface $config
     * @param ManagerInterface $messageManager
     * @param RuleFactory $ruleFactory
     * @param OrderFactory $orderFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param CoreRuleFactory $coreRuleFactory
     * @param Data $helper
     * @param QuoteFactory $quoteFactory
     * @param TransactionFactory $transactionFactory
     * @param ExpiredFactory $expiredFactory
     * @param ReferralCode $referralCodeCookie
     * @param AccountAffiliateFactory $accountAffiliateFactory
     * @param ReferralFactory $referralFactory
     * @param \Magento\Framework\Event\ManagerInterface $_eventManager
     */
    public function __construct(
        AccountFactory                            $accountFactory,
        ConfigInterface                           $config,
        ManagerInterface                          $messageManager,
        RuleFactory                               $ruleFactory,
        OrderFactory                              $orderFactory,
        StoreManagerInterface                     $storeManagerInterface,
        CoreRuleFactory                           $coreRuleFactory,
        Data                                      $helper,
        QuoteFactory                              $quoteFactory,
        TransactionFactory                        $transactionFactory,
        ExpiredFactory                            $expiredFactory,
        ReferralCode                              $referralCodeCookie,
        AccountAffiliateFactory                   $accountAffiliateFactory,
        ReferralFactory                           $referralFactory,
        \Magento\Framework\Event\ManagerInterface $_eventManager
    ) {
        $this->_accountFactory = $accountFactory;
        $this->_config = $config;
        $this->quoteFactory = $quoteFactory;
        $this->_transactionFactory = $transactionFactory;
        $this->messageManager = $messageManager;
        $this->_ruleFactory = $ruleFactory;
        $this->_orderFactory = $orderFactory;
        $this->_coreRuleFactory = $coreRuleFactory;
        $this->_storeManager = $storeManagerInterface;
        $this->_helper = $helper;
        $this->expiredFactory = $expiredFactory;
        $this->referralCodeCookie = $referralCodeCookie;
        $this->accountAffiliateFactory = $accountAffiliateFactory;
        $this->referralFactory = $referralFactory;
        $this->_eventManager = $_eventManager;
    }

    /**
     * @param Observer $observer
     *
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        if ($this->_helper->getEnableModule()) {
            /**
             * @var Order $order
             */
            $order = $observer->getEvent()->getOrder();
            $order_id = $order->getId();
            $increment_id = $order->getData('increment_id');
            $customerId = $order->getCustomerId();
            $origOderStatus = $order->getOrigData('order_status');
            if ($origOderStatus == null && $customerId != null) {
                $quoteId = $order->getQuoteId();
                $this->_quote = $this->quoteFactory->create()->load($quoteId);
                $allShippingAddresses = $this->_quote->getAllShippingAddresses();
                $quotePoint = $this->_quote->getData('reward_point') * 1 / count($allShippingAddresses);
                $rewardAmount = $this->_quote->getData('reward_amount') / count($allShippingAddresses);
                if ($quotePoint) {
                    /**
                     * add rewardpoint to order from quote
                     */
                    if (isset($this->_quote)) {
                        $dataToSaveOrder = [
                            'reward_point' => $quotePoint,
                            'reward_amount' => $rewardAmount
                        ];
                        $order->addData($dataToSaveOrder);
                        $order->save();
                    }

                    /**
                     * end add
                     */
                    $comment = 'Order #: ' . $increment_id;
                    $accountModel = $this->_accountFactory->create();
                    $account = $accountModel->getCollection()->addFieldToFilter('customer_id', $customerId)->getFirstItem();
                    if ($account->getId()) {
                        $accountModel->load($account->getId());
                    }
                    $data = [
                        'customer_id' => $customerId,
                        'points_spent' => $account->getData('points_spent') + $quotePoint,
                        'points_current' => $account->getData('points_current') - $quotePoint,
                    ];
                    $accountModel->addData($data)->save();


                    $expiredModel = $this->expiredFactory->create();
                    $listPointOfUser = $expiredModel->getCollection()
                        ->addFieldToFilter('customer_id', $customerId)
                        ->addFieldToFilter('status', 'available')
                        ->setOrder('expired_date', 'DESC')->getData();
                    $neededPoint = $quotePoint;
                    $expiredId = '';
                    if (is_array($listPointOfUser)) {
                        foreach ($listPointOfUser as $userPoint) {
                            if ($neededPoint > 0) {
                                $pointId = $userPoint['id'];
                                $details = $expiredModel->load($pointId);
                                $detail = $details->getData();
                                if ($neededPoint > $detail['points_change']) {
                                    $neededPoint -= $detail['points_change'];
                                    $detail['status'] = 'used';
                                    $details->setData($detail)->save();
                                    $expiredId = $expiredId . ',' . $userPoint['id'];
                                } else {
                                    $detail['points_change'] -= $neededPoint;
                                    $details->setData($detail)->save();
                                    $expiredId = $expiredId . ',' . $userPoint['id'];
                                    break;
                                }
                            } else {
                                break;
                            }
                        }
                    } else {
                        $pointId = $listPointOfUser['id'];
                        $details = $expiredModel->load($pointId);
                        $detail = $details->getData();
                        $detail['points_change'] -= $neededPoint;
                        $details->setData($detail)->save();
                    }

                    $transactionModel = $this->_transactionFactory->create();
                    $data = [
                        'order_id' => $order_id,
                        'customer_id' => $customerId,
                        'points_change' => -$quotePoint,
                        'points_after' => $accountModel->getData('points_current'),
                        'comment' => $comment,
                        'expired_id' => $expiredId
                    ];
                    $transactionModel->addData($data)->save();

                    //Send email
                    if ($this->_helper->getBalanceEmailEnable()) {
                        $this->_helper->getSendEmail($order, $account, null, null, null, null);
                    }
                } else {
                    if (isset($this->_quote)) {
                        $dataToSaveOrder = [
                            'reward_tier' => $this->_quote->getData('reward_tier'),
                            'reward_amount' => $rewardAmount
                        ];
                        $order->addData($dataToSaveOrder);
                        $order->save();
                    }
                }

                $referralCode = $this->_helper->getReferralCode();
                $referralCodeCookie = $this->referralCodeCookie->get();

                if ($referralCodeCookie && $referralCodeCookie !== $referralCode) {
                    $dataArr = [
                        'customer_id' => $customerId,
                        'apply_customer_id' => $this->getRefereeAccountId($referralCodeCookie),
                        'referral_earning_type' => $this->_helper->getReferralEarningType(),
                        'condition_type' => Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED_AND_PLACE_ORDER
                    ];

                    $applyObj = new DataObject($dataArr);
                    // apply referral code
                    $this->_eventManager->dispatch('apply_referral_code', ['applyObj' => $applyObj]);
                    $this->referralCodeCookie->delete();
                }
            }
        }
    }

    /**
     * @return mixed|string
     */
    private function getRefereeAccountId($code)
    {
        $refereeAffiliateCustomer = $this->accountAffiliateFactory->create()->load($code, 'code');
        if ($refereeAffiliateCustomer && $refereeAffiliateCustomer->getId()) {
            return $refereeAffiliateCustomer->getCustomerId();
        }

        return $this->referralFactory->create()->load($code, 'referral_code')->getData('customer_id');
    }
}
