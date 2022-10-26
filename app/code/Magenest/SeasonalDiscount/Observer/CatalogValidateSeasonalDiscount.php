<?php

namespace Magenest\SeasonalDiscount\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CatalogValidateSeasonalDiscount implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(
        ManagerInterface $messageManager,
        SerializerInterface $serializer
    )
    {
        $this->messageManager = $messageManager;
        $this->serializer = $serializer;
    }

    protected $proceed = false;
    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->proceed) {
            return;
        }
        $this->proceed = true;
        $object = $observer->getEvent()->getData('data_object');
        $datas = $object->getSaleDay();
        $count=0;
        if(!empty($datas['sales_day'])) {
            foreach ($datas['sale_day'] as $data){
                ++$count;
                if ($data['start_date'] > $data['expired_date']){
                    unset($datas['sale_day'][$count-1]);
                    $this->messageManager->addErrorMessage('Start date must follow Expired date !!');
                }
            }
        }
        $object->setData('sale_day', null);
        $object->setData('sale_day', $this->serializer->serialize($datas));
        $this->messageManager->addSuccessMessage('Success');
    }
}
