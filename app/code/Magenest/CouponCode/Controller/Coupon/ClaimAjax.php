<?php

namespace Magenest\CouponCode\Controller\Coupon;

use Exception;
use Magenest\CouponCode\Model\ClaimCouponFactory;
use Magenest\CouponCode\Model\ResourceModel\ClaimCoupon;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\PageCache\Model\Cache\Type;
use Magento\SalesRule\Model\CouponFactory;
use Psr\Log\LoggerInterface;

/**
 * Class ClaimAjax
 */
class ClaimAjax extends Action
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

    protected $session;

    protected $coupon;

    /**
     * ClaimAjax constructor.
     * @param Context $context
     * @param Type $flush
     * @param Json $json
     * @param ClaimCoupon $resource
     * @param ClaimCouponFactory $couponFactory
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param Session $session
     * @param CouponFactory $coupon
     */
    public function __construct(
        Context            $context,
        Type               $flush,
        Json               $json,
        ClaimCoupon        $resource,
        ClaimCouponFactory $couponFactory,
        JsonFactory        $resultJsonFactory,
        LoggerInterface    $logger,
        Session            $session,
        CouponFactory         $coupon
    ) {
        $this->flush = $flush;
        $this->resource = $resource;
        $this->couponFactory = $couponFactory;
        $this->json = $json;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->session = $session;
        $this->coupon = $coupon;
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
        $resultJson = $this->resultJsonFactory->create();
        $response['success'] = false;

        if ($this->getRequest()->isAjax()) {
            try {
                $couponCode = $this->_request->getParam('couponCode');
                $coupon = $this->coupon->create()->load($couponCode,'code');
                $model = $this->couponFactory->create();
                $model->addData([
                    'rule_id' => $coupon->getRuleId(),
                    'coupon_id' => $coupon->getId(),
                    'customer_id' => $this->session->getCustomerId()
                ]);
                $this->resource->save($model);
                $response['success'] = true;
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
            }
        }

        return $resultJson->setData($response);
    }
}
