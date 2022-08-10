<?php


namespace Magenest\PaymentEPay\Controller\Payment;


use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Payment\Helper\Data as PaymentData;

class QRResult extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Payment data
     *
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentData;

    /**
     * QRResult constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param PaymentData $paymentData
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        PaymentData $paymentData
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->paymentData     = $paymentData;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $order          = $this->checkoutSession->getLastRealOrder();
        $payment        = $order->getPayment();
        $methodInstance = $this->paymentData->getMethodInstance($payment->getMethod());
        $orderStatus    = $methodInstance->getConfigData('order_status');
        $result         = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData([
            'is_paid' => $order->getStatus() == $orderStatus
        ]);
        return $result;
    }
}
