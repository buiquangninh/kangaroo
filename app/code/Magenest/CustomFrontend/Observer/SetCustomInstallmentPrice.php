<?php

namespace Magenest\CustomFrontend\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class SetCustomInstallmentPrice implements ObserverInterface
{
    protected $serializer;

    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getData('quote_item');

        $item = ($item->getParentItem() ? $item->getParentItem() : $item);

        if ($finalPrice = $item->getProduct()->getInstallmentPrice()) {
            $item->setCustomPrice($finalPrice);
            $item->setOriginalCustomPrice($finalPrice);
            $item->setQty(1);
        }
    }
}
