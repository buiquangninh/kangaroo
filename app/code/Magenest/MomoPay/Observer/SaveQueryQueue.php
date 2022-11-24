<?php

namespace Magenest\MomoPay\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SaveQueryQueue implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magenest\MomoPay\Model\QueryStatusFactory
     */
    protected $queryStatusFactory;

    /**
     * @var \Magenest\MomoPay\Model\ResourceModel\QueryStatus
     */
    protected $queryStatusResource;

    /**
     * @var \Magenest\MomoPay\Helper\MomoHelper
     */
    protected $helper;

    /**
     * SaveQueryQueue constructor.
     * @param \Magenest\MomoPay\Model\QueryStatusFactory $queryStatusFactory
     * @param \Magenest\MomoPay\Model\ResourceModel\QueryStatus $queryStatusResource
     * @param \Magenest\MomoPay\Helper\MomoHelper $helper
     */
    public function __construct(
        \Magenest\MomoPay\Model\QueryStatusFactory $queryStatusFactory,
        \Magenest\MomoPay\Model\ResourceModel\QueryStatus $queryStatusResource,
        \Magenest\MomoPay\Helper\MomoHelper $helper
    ) {
        $this->queryStatusFactory = $queryStatusFactory;
        $this->queryStatusResource = $queryStatusResource;
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        /** @var  Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var  Order $order */
        $order = $observer->getEvent()->getOrder();
        $method = $order->getPayment() ? $order->getPayment()->getMethod() : null;
        $allMethod = $this->helper->getAllMethod();
        if (!in_array($method, $allMethod)) {
            return;
        }
        $data = [
            'order_id' => $order->getIncrementId(),
            'query_count' => 0,
            'status' => \Magenest\MomoPay\Model\Config\Source\QueryStatus::STATUS_UNSENT
        ];
        $model = $this->queryStatusFactory->create();
        $model->addData($data);

        try {
            $this->queryStatusResource->save($model);
        } catch (\Exception $e) {
            $this->helper->debug($e);
        }
    }
}