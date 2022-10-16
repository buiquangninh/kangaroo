<?php

namespace Magenest\MomoPay\Gateway\Response;

use Magenest\MomoPay\Helper\MomoHelper;
use Magenest\MomoPay\Model\Config\Source\MomoStatus;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

class OrderHandler extends AbstractResponseHandler
{

    /**
     * @inheritDoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = SubjectReader::readPayment($handlingSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();
        ContextHelper::assertOrderPayment($payment);

        if (isset($response['response'])) {
            /** @var \Magenest\MomoPay\Model\Api\Response\CreatePaymentResponse $response */
            $response = $response['response'];
            foreach ($response->getData() as $key => $param) {
                $payment->setAdditionalInformation($key, $param);
            }
            $order->setData(\Magenest\MomoPay\Api\MomoOrderInfoInterface::PAYMENT_URL, $response->getPayUrl());
        }
        $order->setData(\Magenest\MomoPay\Api\MomoOrderInfoInterface::STATUS, MomoStatus::STATUS_UNPAID);
        $order->setCanSendNewEmailFlag(false);
        $order->setState(Order::STATE_PENDING_PAYMENT);
        $order->setStatus(Order::STATE_PENDING_PAYMENT);
    }
}