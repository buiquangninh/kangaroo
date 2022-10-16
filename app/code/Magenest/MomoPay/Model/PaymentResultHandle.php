<?php

namespace Magenest\MomoPay\Model;

use Magenest\MomoPay\Controller\Payment\Response;
use Magenest\MomoPay\Model\Api\Response\PaymentInfo;
use Magenest\MomoPay\Model\Config\Source\MomoStatus;
use Magento\Sales\Model\Order;
use Magenest\MomoPay\Helper\MomoHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

class PaymentResultHandle
{
    /**
     * @var QueryStatusFactory
     */
    protected $queryStatusFactory;

    /**
     * @var ResourceModel\QueryStatus
     */
    protected $resourceQueryStatus;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var MomoHelper
     */
    protected $helper;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magenest\MomoPay\Gateway\Command\CompleteCommand
     */
    protected $completeCommand;

    /**
     * @var \Magenest\Repayment\Model\Order\EmailSender
     */
    protected $paymentFailEmail;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $areaList;

    /**
     * PaymentResultHandle constructor.
     * @param QueryStatusFactory $queryStatusFactory
     * @param ResourceModel\QueryStatus $resourceQueryStatus
     * @param RequestInterface $request
     * @param MomoHelper $helper
     * @param OrderFactory $orderFactory
     * @param OrderResource $orderResource
     * @param RedirectFactory $redirectFactory
     * @param ResponseInterface $response
     * @param ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magenest\Repayment\Model\Order\EmailSender $paymentFailEmail
     * @param \Magenest\MomoPay\Gateway\Command\CompleteCommand $completeCommand
     * @param \Magento\Framework\App\AreaList $areaList
     */
    public function __construct(
        \Magenest\MomoPay\Model\QueryStatusFactory $queryStatusFactory,
        \Magenest\MomoPay\Model\ResourceModel\QueryStatus $resourceQueryStatus,
        RequestInterface $request,
        MomoHelper $helper,
        OrderFactory $orderFactory,
        OrderResource $orderResource,
        RedirectFactory $redirectFactory,
        ResponseInterface $response,
        ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magenest\Repayment\Model\Order\EmailSender $paymentFailEmail,
        \Magenest\MomoPay\Gateway\Command\CompleteCommand $completeCommand,
        \Magento\Framework\App\AreaList $areaList
    ) {
        $this->queryStatusFactory = $queryStatusFactory;
        $this->resourceQueryStatus = $resourceQueryStatus;
        $this->request = $request;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->orderResource = $orderResource;
        $this->redirectFactory = $redirectFactory;
        $this->response = $response;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->completeCommand = $completeCommand;
        $this->paymentFailEmail = $paymentFailEmail;
        $this->areaList = $areaList;
    }

    /**
     * @param PaymentInfo $response
     * @return string
     */
    public function handle(\Magenest\MomoPay\Model\Api\Response\PaymentInfo $response)
    {
        try {
            $redirectPath = 'checkout/onepage/failure';
            $orderIncrementId = $response->getOrderId();
            /** @var Order $order */
            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
            if ($this->validate($response, $order)) {
                if ($order->getStatus() == Order::STATE_PENDING_PAYMENT) {
                    /** @var Order\Payment $payment */
                    $payment = $order->getPayment();
                    foreach ($response->getData() as $key => $param) {
                        $payment->setAdditionalInformation($key, $param);
                    }
                    $payment->setTransactionId($response->getTransId());
                    $payment->setParentTransactionId($response->getTransId());
                    $order->setState(Order::STATE_PROCESSING);
                    $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
                    $order->setData(\Magenest\MomoPay\Api\MomoOrderInfoInterface::TRANS_ID, $response->getTransId());
                    $order->setData(\Magenest\MomoPay\Api\MomoOrderInfoInterface::STATUS, MomoStatus::STATUS_PAID);
                    $this->completeCommand->execute(['order' => $order, 'amount' => $payment->formatAmount($order->getBaseGrandTotal())]);
                    $this->orderResource->save($order);
                }
                $redirectPath = 'checkout/onepage/success';
            } elseif (in_array($response->getResultCode(), Response::REPS_CODE_FINAL)) { // FAIL - Final status -> cancel order
                if ($order->getStatus() == Order::STATE_PENDING_PAYMENT) {
                    $this->areaList->getArea(\Magento\Framework\App\Area::AREA_FRONTEND)
                        ->load(\Magento\Framework\App\Area::PART_TRANSLATE);
                    $order->cancel();
                    $comment = __($response->getMessage());
                    $order->addCommentToStatusHistory($comment)
                        ->setIsCustomerNotified(false);
                    $this->orderResource->save($order);
                }
            }
            $this->updateStatusLog($response);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__("Something went wrong. Please try again later."));
        }
        return $redirectPath;
    }

    /**
     * @param PaymentInfo $response
     * @param $order
     * @return bool
     */
    protected function validate($response, $order)
    {
        if (!$order->getId() || $response->getResultCode() != Response::RESP_CODE_SUCCESS) {
            return false;
        }
        return true;
    }

    /**
     * @param $order
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function sendFailEmail($order)
    {
        $this->paymentFailEmail->send($order);
    }

    /**
     * Update check transaction status log
     *
     * @param $response
     * @param $logItems
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    protected function updateStatusLog($response, $logItems = null)
    {
        if (!$logItems) {
            $logItems = $this->queryStatusFactory->create();
            $this->resourceQueryStatus->loadByIncrementId($logItems, $response->getOrderId());
        }
        if ($response->getResultCode() != null) {
            $logItems->setData('status', \Magenest\MomoPay\Model\Config\Source\QueryStatus::STATUS_SUCCESS);
            $logItems->setData('message_log', $response->getMessage());
            $this->resourceQueryStatus->save($logItems);
        }
    }
}