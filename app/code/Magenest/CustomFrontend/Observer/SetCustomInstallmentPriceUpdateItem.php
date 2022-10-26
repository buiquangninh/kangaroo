<?php


namespace Magenest\CustomFrontend\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class SetCustomInstallmentPriceUpdateItem implements ObserverInterface
{
    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $quote      = $observer->getCart();
        $buyRequest = $observer->getInfo();
        foreach ($buyRequest->getData() as $itemId => $qty) {
            $item = $quote->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if ($finalPrice = $item->getProduct()->getInstallmentPrice()) {
                $item->setCustomPrice($finalPrice);
                $item->setOriginalCustomPrice($finalPrice);
                $item->setQty(1);
            }
        }
    }
}
