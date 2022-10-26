<?php
namespace Magenest\StoreCredit\Plugin\Order;

use Magenest\StoreCredit\Model\KCoin;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\State\CaptureCommand;

class StateCommand
{
    /**
     * @param CaptureCommand $subject
     * @param Phrase $result
     * @param OrderPaymentInterface $payment
     * @param float|string $amount
     * @param OrderInterface $order
     * @return Phrase
     * @throws LocalizedException
     */
    public function afterExecute(
        CaptureCommand        $subject,
        Phrase                $result ,
        OrderPaymentInterface $payment,
                              $amount,
        OrderInterface        $order
    ): Phrase {
        if ($payment->getMethod() === KCoin::CODE) {
            $status = $payment->getMethodInstance()->getConfigData('order_status');
            $order->setState(Order::STATE_NEW);
            $order->setStatus($status);
        }
        return $result;
    }
}
