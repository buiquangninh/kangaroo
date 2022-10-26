<?php

namespace Magenest\SeasonalDiscount\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CatalogUnserializeSaleDayData implements ObserverInterface
{
    /**
     * @var SerializerInterface
     */
    public $serializer;

    public function __construct(
        SerializerInterface $serializer
    )
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $object = $observer->getEvent()->getData('data_object');
        try {
            $empty = [];
            if ($object->getData('sale_day') == null ) {
                $object->setData('sale_day', $this->serializer->serialize($empty));
            }
            $datas = $object->getData('sale_day');
            $object->setData('sale_day', $this->serializer->unserialize($datas));
        }
        catch (\Exception $e){

        }
    }
}
