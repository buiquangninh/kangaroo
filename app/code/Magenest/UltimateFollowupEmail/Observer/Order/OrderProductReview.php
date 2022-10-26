<?php
namespace Magenest\UltimateFollowupEmail\Observer\Order;

use Magenest\UltimateFollowupEmail\Model\Processor\OrderProcessor;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderProductReview extends StatusChange
{
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();

            if ($order->getState() === Order::STATE_COMPLETE) {
                /** @var OrderProcessor $orderProcessor */
                $orderProcessor = $this->orderProcessorFactory->create();
                $orderProcessor->setEmailTarget($order);
                $orderProcessor->setType('order_product_review');
                $orderProcessor->run();
            }
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->debug($e->getMessage());
        }
    }
}
