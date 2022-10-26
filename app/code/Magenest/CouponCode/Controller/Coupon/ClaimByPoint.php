<?php

namespace Magenest\CouponCode\Controller\Coupon;

use Exception;
use Magenest\CouponCode\Model\ClaimCouponFactory;
use Magenest\CouponCode\Model\ResourceModel\ClaimCoupon;
use Magenest\CouponCode\Model\ResourceModel\ClaimCoupon\CollectionFactory as ClaimCouponCollection;
use Magenest\RewardPoints\Model\AccountFactory;
use Magenest\RewardPoints\Model\TransactionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\PageCache\Model\Cache\Type;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\CouponFactory as CouponModel;
use Magento\SalesRule\Model\ResourceModel\Coupon as CouponResource;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory as CouponCollection;
use Magento\SalesRule\Model\RuleFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ClaimAjax
 */
class ClaimByPoint extends Action
{
    /**
     * @var Type
     */
    protected $flush;
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var ClaimCouponFactory
     */
    protected $couponFactory;
    /**
     * @var ClaimCoupon
     */
    protected $resource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $ruleRepository;

    /**
     * @param Session $session
     * @param TransactionFactory $transaction
     * @param CouponFactory $coupon
     * @param TimezoneInterface $timezone
     * @param RuleFactory $rule
     * @param AccountFactory $account
     * @param CouponCollection $couponCollection
     * @param CouponResource $couponResource
     * @param CouponFactory $couponModel
     * @param ClaimCouponCollection $claimCouponCollection
     * @param Context $context
     * @param Type $flush
     * @param Json $json
     * @param ClaimCoupon $resource
     * @param ClaimCouponFactory $couponFactory
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session               $session,
        TransactionFactory    $transaction,
        CouponFactory         $coupon,
        TimezoneInterface     $timezone,
        RuleFactory           $rule,
        AccountFactory        $account,
        CouponCollection      $couponCollection,
        CouponResource        $couponResource,
        CouponModel           $couponModel,
        ClaimCouponCollection $claimCouponCollection,
        Context               $context,
        Type                  $flush,
        Json                  $json,
        ClaimCoupon           $resource,
        ClaimCouponFactory    $couponFactory,
        JsonFactory           $resultJsonFactory,
        LoggerInterface       $logger,
        RuleRepositoryInterface $ruleRepository
    ) {
        $this->session = $session;
        $this->transaction = $transaction;
        $this->coupon = $coupon;
        $this->timezone = $timezone;
        $this->rule = $rule;
        $this->account = $account;
        $this->couponCollection = $couponCollection;
        $this->couponResource = $couponResource;
        $this->flush = $flush;
        $this->resource = $resource;
        $this->couponModel = $couponModel;
        $this->claimCouponCollection = $claimCouponCollection;
        $this->couponFactory = $couponFactory;
        $this->json = $json;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->ruleRepository = $ruleRepository;
        parent::__construct($context);
    }

    public function createTransaction($customer_id, $rule_id, $coupon_id)
    {
        $account = $this->account->create()->load($customer_id, 'customer_id');
        $rule = $this->rule->create()->load($rule_id, 'rule_id');
        $couponName = $this->coupon->create()->load($coupon_id, 'coupon_id')->getCode();
        $rulePoints = $rule->getKpoint();
        $cuskpoint = $account->getPointsCurrent();
        $kpointLeft = $cuskpoint - $rulePoints;
        $account->setData('points_total', $cuskpoint);
        $account->setData('points_current', $kpointLeft);
        $account->setData('points_spent', $rulePoints);
        $account->save();
        $ruleName = $rule->getName();
        $currentTime = $this->timezone->date();
        $data = [
            'customer_id' => $customer_id,
            'rule_id' => 0,
            'points_change' => -$rulePoints,
            'points_after' => $kpointLeft,
            'insertion_date' => $currentTime,
            'title' => ' "' . $ruleName . '"(' . $couponName . ')',
        ];

        $transactionModel = $this->transaction->create();
        $transaction = $transactionModel->addData($data);
        $transaction->save();
    }
    /**
     * Claim a coupon
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws Exception
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response['success'] = false;

        if ($this->getRequest()->isAjax()) {
            try {
                $ruleId = $this->_request->getParam('rule');
                $rule = $this->ruleRepository->getById($ruleId);
                $customerId = $this->session->getCustomerId();
                $collection = $this->claimCouponCollection->create()
                    ->addFieldToFilter('main_table.rule_id', $ruleId)
                    ->addFieldToFilter('main_table.customer_id', $customerId);
                if ($collection->getFirstItem()->getId()) {
                    $response['message'] = __("Coupon code already exists in your wallet");
                } else {
                    $coupon = $this->couponCollection->create()
                        ->addFieldToFilter('rule_id', $ruleId)
                        ->getFirstItem();
                    if ($coupon->getCouponId()) {
                        $this->createTransaction($customerId, $ruleId, $coupon->getCouponId());
                        $model = $this->couponFactory->create();
                        $model->addData([
                            'rule_id' => $coupon->getRuleId(),
                            'coupon_id' => $coupon->getCouponId(),
                            'customer_id' => $customerId
                        ]);
                        $this->resource->save($model);
                        $response['message'] = __("You claimed coupon code successfully");
                    } else {
                        $response['message'] = __("No more coupon code");
                    }
                }

                $response['success'] = true;
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                $response['message'] = $exception->getMessage();
            }
        }

        return $resultJson->setData($response);
    }
}
