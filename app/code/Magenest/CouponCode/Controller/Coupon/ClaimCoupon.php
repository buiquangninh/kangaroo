<?php

namespace Magenest\CouponCode\Controller\Coupon;


use Magenest\CouponCode\Model\ClaimCouponFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\SalesRule\Model\CouponFactory as CouponModel;
use Magento\SalesRule\Model\ResourceModel\Coupon as CouponResource;
use Psr\Log\LoggerInterface;
use Magenest\CouponCode\Model\ResourceModel\ClaimCoupon as ResourceClaimCoupon;
use Magenest\CouponCode\Model\ResourceModel\ClaimCoupon\CollectionFactory as ClaimCouponCollection;

class ClaimCoupon extends Action
{
    /**
     * @var ClaimCouponFactory
     */
    protected $couponFactory;
    /**
     * @var ResourceClaimCoupon
     */
    protected $resource;

    /**
     * @var ClaimCouponCollection
     */
    protected $claimCouponCollection;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CouponModel
     */
    protected $couponModel;

    /**
     * @var CouponResource
     */
    protected $couponResource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ClaimCoupon constructor.
     * @param Context $context
     * @param ResourceClaimCoupon $resource
     * @param ClaimCouponFactory $couponFactory
     * @param Session $session
     * @param CouponModel $couponModel
     * @param CouponResource $couponResource
     * @param LoggerInterface $logger
     * @param ClaimCouponCollection $claimCouponCollection
     */
    public function __construct(
        Context $context,
        ResourceClaimCoupon $resource,
        ClaimCouponFactory $couponFactory,
        Session $session,
        CouponModel $couponModel,
        CouponResource $couponResource,
        LoggerInterface $logger,
        ClaimCouponCollection $claimCouponCollection
    )
    {
        $this->resource              = $resource;
        $this->couponFactory         = $couponFactory;
        $this->logger                = $logger;
        $this->session               = $session;
        $this->couponModel           = $couponModel;
        $this->couponResource        = $couponResource;
        $this->claimCouponCollection = $claimCouponCollection;
        parent::__construct($context);
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
        $resultJson          = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response['success'] = false;

        if ($this->getRequest()->isAjax()) {
            try {
                $couponCode  = $this->_request->getParam('coupon_code');
                $couponModel = $this->couponModel->create();
                $this->couponResource->load($couponModel, $couponCode, 'code');
                if ($couponModel->getId()) {
                    $collection = $this->claimCouponCollection->create()
                            ->addFieldToFilter('main_table.coupon_id', $couponModel->getId())
                        ->addFieldToFilter('main_table.customer_id', $this->session->getCustomerId());
                    if ($collection->getFirstItem()->getId()) {
                        $response['message'] = __("Coupon code already exists in your wallet");
                    } else {
                        $model = $this->couponFactory->create();
                        $model->addData([
                            'rule_id' => $couponModel->getRuleId(),
                            'coupon_id' => $couponModel->getId(),
                            'customer_id' => $this->session->getCustomerId()
                        ]);
                        $this->resource->save($model);
                        $response['message'] = __("You claimed coupon code successfully");
                    }

                    $response['success'] = true;
                }
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                $response['message'] = $exception->getMessage();
            }
        }

        return $resultJson->setData($response);
    }
}
