<?php

namespace Magenest\MomoPay\Model\Order\Payment\State;

use Magenest\MomoPay\Helper\MomoHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusResolver;

class OrderCommand extends \Magento\Sales\Model\Order\Payment\State\OrderCommand
{
    protected MomoHelper $momoHelper;

    /**
     * @var StatusResolver
     */
    private $statusResolver;

    /**
     * OrderCommand constructor.
     *
     * @param StatusResolver|null $statusResolver
     */
    public function __construct(
        MomoHelper $momoHelper,
        StatusResolver $statusResolver = null
    ) {
        parent::__construct($statusResolver);
        $this->statusResolver = $statusResolver
            ?: ObjectManager::getInstance()->get(StatusResolver::class);
        $this->momoHelper = $momoHelper;
    }

    public function execute(OrderPaymentInterface $payment, $amount, OrderInterface $order)
    {
        if ($order->getPayment() && !in_array($order->getPayment()->getMethod(), $this->momoHelper->getAllMethod())) {
            return parent::execute($payment, $amount, $order);
        }
        $state = Order::STATE_PENDING_PAYMENT;
        $status = null;
        $message = 'Ordered amount of %1';

        if ($payment->getIsTransactionPending()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $message = 'The order amount of %1 is pending approval on the payment gateway.';
        }

        if ($payment->getIsFraudDetected()) {
            $state = Order::STATE_PAYMENT_REVIEW;
            $status = Order::STATUS_FRAUD;
            $message = 'The order amount of %1 is pending approval on the payment gateway.';
        }

        if (!isset($status)) {
            $status = $this->statusResolver->getOrderStatusByState($order, $state);
        }

        $order->setState($state);
        $order->setStatus($status);

        return __($message, $order->getBaseCurrency()->formatTxt($amount));
    }
}