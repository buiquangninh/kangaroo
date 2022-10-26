<?php

namespace Magenest\LalaMove\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magenest\LalaMove\Model\Carrier\LalaMove;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class SalesOrderCancelAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Lalamove
     */
    protected $lalamoveCarrier;

    /**
     * SalesOrderCancelAfterObserver constructor.
     * @param Lalamove $lalamoveCarrier
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Lalamove $lalamoveCarrier,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->lalamoveCarrier = $lalamoveCarrier;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Observer $observer
     * @throws \Zend_Http_Client_Exception
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            $order = $observer->getEvent()->getPayment()->getOrder();
        }
        if ($order) {
            if ($order->getShippingMethod() == 'lalamove_lalamove') {
                $lalamoveLabelId = $order->getApiOrderId();
                $this->lalamoveCarrier->cancelOrder($lalamoveLabelId);
            }

        }
    }
}
