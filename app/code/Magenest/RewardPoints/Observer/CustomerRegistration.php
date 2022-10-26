<?php

namespace Magenest\RewardPoints\Observer;

use Exception;
use Magenest\Affiliate\Model\AccountFactory;
use Magenest\RewardPoints\Block\Customer\Points as CustomerPoints;
use Magenest\RewardPoints\Cookie\ReferralCode;
use Magenest\RewardPoints\Helper\Data;
use Magenest\RewardPoints\Model\ReferralFactory;
use Magenest\RewardPoints\Model\Rule;
use Magenest\RewardPoints\Model\RuleFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class CustomerRegistration
 * @package Magenest\RewardPoints\Observer
 */
class CustomerRegistration implements ObserverInterface
{
    const COOKIE_NAME = 'referralcode';

    /**
     * @var ReferralCode
     */
    protected $referralCodeCookie;

    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var ReferralFactory
     */
    protected $referralFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CustomerPoints
     */
    protected $points;

    /**
     * @var AccountFactory
     */
    protected $accountFactory;

    /**
     * System event manager
     *
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * CustomerRegistration constructor.
     * @param RuleFactory $ruleFactory
     * @param ManagerInterface $messageManager
     * @param Data $helper
     * @param ReferralFactory $referralFactory
     * @param Session $customerSession
     * @param CustomerPoints $points
     * @param ReferralCode $referralCodeCookie
     * @param AccountFactory $accountFactory
     */
    public function __construct(
        RuleFactory                               $ruleFactory,
        ManagerInterface                          $messageManager,
        Data                                      $helper,
        ReferralFactory                           $referralFactory,
        Session                                   $customerSession,
        CustomerPoints                            $points,
        ReferralCode                              $referralCodeCookie,
        AccountFactory                            $accountFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_ruleFactory = $ruleFactory;
        $this->messageManager = $messageManager;
        $this->_helper = $helper;
        $this->referralFactory = $referralFactory;
        $this->customerSession = $customerSession;
        $this->points = $points;
        $this->referralCodeCookie = $referralCodeCookie;
        $this->accountFactory = $accountFactory;
        $this->_eventManager = $eventManager;
    }

    /**
     * @param Observer $observer
     * @throws Exception
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        $customerId = $customer->getId();
        if ($this->_helper->getEnableModule()) {
            if ($customerId != null) {
                $ruleModel = $this->_ruleFactory->create();
                $rulesRegistration = $ruleModel->getCollection()->addFieldToFilter('condition', 'registration');
                if (!empty($rulesRegistration)) {
                    foreach ($rulesRegistration as $rule) {
                        $ruleId = $rule->getId();
                        if ($this->_helper->validateRule($rule, $customerId)) {
                            $point = $rule->getPoints();
                            $result = $this->_helper->addPoints($customer, $ruleId, null, null);

                            if ($result == true) {
                                if ($event->getName() === 'adminhtml_customer_save_after') {
                                    $this->messageManager->addSuccessMessage(
                                        sprintf(__('The customer has earned %s points for register to our store.'), $point)
                                    );
                                } else {
                                    $this->messageManager->addSuccessMessage(
                                        sprintf(__('You have earned %s points for register to our store.'), $point)
                                    );
                                }
                            }
                        }
                    }
                }
            }
        }
        $customerSession = $this->customerSession;
        $customerSession->setData('display_referer_popup', true);

        // Customer Gratitude
        $this->customerGratitude($event, $customer);

        //Merger module ReferAFriend
        if ($customerId == null) return;

        $referralCode = $this->createCode();

        // Send email coupon
        $referralCodeCookie = $this->points->getReferralCodeCookie();
        if ($referralCodeCookie != null) {
            $this->points->getApplyReferralCodeUrl($referralCodeCookie, $customerId);

            $dataArr = [
                'customer_id' => $customerId,
                'apply_customer_id' => $this->getRefereeAccountId($referralCodeCookie),
                'referral_earning_type' => $this->_helper->getReferralEarningType(),
                'condition_type' => Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED_AND_REGISTER
            ];

            $applyObj = new DataObject($dataArr);
            // apply referral code
            $this->_eventManager->dispatch('apply_referral_code', ['applyObj' => $applyObj]);
        }

        /**
         * @var \Magenest\RewardPoints\Model\Referral $referral
         */
        $referral = $this->referralFactory->create();
        $referral->setData('customer_id', $customerId);
        $referral->setData('referral_code', $referralCode);
        $referral->save();
    }

    /**
     * @param $observer
     * @throws Exception
     */
    public function customerGratitude($event, $customer)
    {
        $customerId = $customer->getId();
        if ($customerId != null) {
            $ruleModel = $this->_ruleFactory->create();
            $rules = $ruleModel->getCollection()->addFieldToFilter('condition', 'grateful');
            if (!empty($rules)) {
                foreach ($rules as $rule) {
                    $ruleId = $rule->getId();
                    $amount = $rule->getConditions()->getConditions()[0]->getValue();
                    if ($customerId == $amount) {
                        $point = $rule->getPoints();
                        $result = $this->_helper->addPoints($customer, $ruleId, null, null);
                        if ($result == true) {
                            if ($event->getName() === 'customer_register_success') {
                                $this->messageManager->addSuccessMessage(
                                    sprintf(__('You were lucky to become our %snd customer . We give you %s points.'),
                                        $customerId, $point)
                                );
                            } else {
                                $this->messageManager->addSuccessMessage(
                                    sprintf(__('Welcome to our store'))
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Generate code
     *
     * @return mixed
     */
    public function createCode()
    {
        $gen_arr = [];

        $pattern = $this->_helper->getCodePattern();
        if (!$pattern) {
            $pattern = '[A1][N1][A1][N1][A1][N1]';
        }

        preg_match_all("/\[[AN][.*\d]*\]/", $pattern, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $delegate = substr($match [0], 1, 1);
            $length = substr($match [0], 2, strlen($match [0]) - 3);
            $gen = '';
            if ($delegate == 'A') {
                $gen = $this->generateString($length);
            } elseif ($delegate == 'N') {
                $gen = $this->generateNum($length);
            }

            $gen_arr [] = $gen;
        }
        foreach ($gen_arr as $g) {
            $pattern = preg_replace('/\[[AN][.*\d]*\]/', $g, $pattern, 1);
        }

        return $pattern;
    }

    /**
     * Generate String
     *
     * @param $length
     * @return string
     */
    public function generateString($length)
    {
        if ($length == 0 || $length == null || $length == '') {
            $length = 5;
        }
        $string = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $rand = '';
        for ($i = 0; $i < $length; $i++) {
            $rand .= $string[rand(0, 51)];
        }

        return $rand;
    }

    /**
     * Generate Number
     *
     * @param $length
     * @return string
     */
    public function generateNum($length)
    {
        if ($length == 0 || $length == null || $length == '') {
            $length = 5;
        }
        $number = "0123456789";
        $rand = '';
        for ($i = 0; $i < $length; $i++) {
            $rand .= $number[rand(0, 9)];
        }

        return $rand;
    }

    /**
     * @return mixed|string
     */
    private function getRefereeAccountId($code)
    {
        $refereeAffiliateCustomer = $this->accountFactory->create()->load($code, 'code');
        if ($refereeAffiliateCustomer && $refereeAffiliateCustomer->getId()) {
            return $refereeAffiliateCustomer->getCustomerId();
        }

        return $this->referralFactory->create()->load($code, 'referral_code')->getData('customer_id');
    }
}
