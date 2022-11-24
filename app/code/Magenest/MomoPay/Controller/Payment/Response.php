<?php

namespace Magenest\MomoPay\Controller\Payment;

use Magenest\MomoPay\Helper\MomoHelper;
use Magenest\MomoPay\Model\PaymentResultHandle;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magenest\MomoPay\Model\Api\Response\PaymentInfo;
use Magento\Sales\Model\Order;

class Response implements \Magento\Framework\App\ActionInterface
{
    const REPS_CODE_NOT_FINAL = [9000, 8000, 7000, 1000, 11, 12, 13, 20, 22, 40, 41, 42, 43, 10,];
    const REPS_CODE_FINAL = [1001, 1002, 1003, 1004, 1004, 1006, 1007, 1026, 1080, 1081, 2001, 2007, 3001, 3002, 3003, 3004, 4001, 4010, 4011, 4100, 4015, 99,];
    const RESP_CODE_SUCCESS = 0;

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
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * @var \Magenest\MomoPay\Gateway\Command\CompleteCommand
     */
    protected $completeCommand;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var PaymentResultHandle
     */
    protected $paymentResultHandle;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $httpRequest;

    /**
     * AbstractAction constructor.
     * @param RequestInterface $request
     * @param MomoHelper $helper
     * @param OrderFactory $orderFactory
     * @param OrderResource $orderResource
     * @param RedirectFactory $redirectFactory
     * @param ResponseInterface $response
     * @param ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param PaymentResultHandle $paymentResultHandle
     * @param \Magenest\MomoPay\Gateway\Command\CompleteCommand $completeCommand
     * @param \Magento\Framework\App\Request\Http $httpRequest
     */
    public function __construct(
        RequestInterface $request,
        MomoHelper $helper,
        OrderFactory $orderFactory,
        OrderResource $orderResource,
        RedirectFactory $redirectFactory,
        ResponseInterface $response,
        ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        PaymentResultHandle $paymentResultHandle,
        \Magenest\MomoPay\Gateway\Command\CompleteCommand $completeCommand,
        \Magento\Framework\App\Request\Http $httpRequest
    ) {
        $this->request = $request;
        $this->helper = $helper;
        $this->orderFactory = $orderFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->orderResource = $orderResource;
        $this->completeCommand = $completeCommand;
        $this->response = $response;
        $this->paymentResultHandle = $paymentResultHandle;
        $this->httpRequest = $httpRequest;
    }

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->redirectFactory->create();

        $this->helper->debug('Payment Response: ' . var_export($this->request->getParams(), true));
        $response = $this->getResponse();
        $redirectPath = $this->paymentResultHandle->handle($response);
        $orderIncrementId = $response->getOrderId();
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        $this->restoredCheckoutSession($order);
        return $resultRedirect->setPath($redirectPath);
    }

    /**
     * @param Order $order
     */
    protected function restoredCheckoutSession(\Magento\Sales\Model\Order $order)
    {
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastQuoteId($order->getQuoteId());
        $this->checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
    }

    /**
     * @param PaymentInfo $response
     * @param $order
     * @return bool
     */
    protected function validate($response, $order)
    {
        if (!$order->getId() || $response->getResultCode() != self::RESP_CODE_SUCCESS) {
            return false;
        }
        return true;
    }

    /**
     * @return \Magento\Framework\DataObject|mixed
     */
    protected function getResponse($data = null)
    {
        if (!$data) {
            $data = $this->request->getParams();
        }
        return $this->helper->createObject($data, \Magenest\MomoPay\Api\Response\PaymentInfoInterface::class);
    }
}