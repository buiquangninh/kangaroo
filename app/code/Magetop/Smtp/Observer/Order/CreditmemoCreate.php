<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Smtp\Observer\Order;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;
use Magetop\Smtp\Helper\EmailMarketing;
use Psr\Log\LoggerInterface;

/**
 * Class CreditmemoCreate
 * @package Magetop\Smtp\Observer\Order
 */
class CreditmemoCreate implements ObserverInterface
{
    /**
     * @var EmailMarketing
     */
    protected $helperEmailMarketing;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * CreditmemoCreate constructor.
     *
     * @param EmailMarketing $helperEmailMarketing
     * @param LoggerInterface $logger
     */
    public function __construct(
        EmailMarketing $helperEmailMarketing,
        LoggerInterface $logger
    ) {
        $this->helperEmailMarketing = $helperEmailMarketing;
        $this->logger               = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->helperEmailMarketing->isEnableEmailMarketing() &&
            $this->helperEmailMarketing->getSecretKey() &&
            $this->helperEmailMarketing->getAppID()
        ) {
            /* @var Creditmemo $creditmemo */
            $creditmemo = $observer->getEvent()->getDataObject();
            $this->syncCreditmemo($creditmemo);
            $this->helperEmailMarketing->updateCustomer($creditmemo->getOrder()->getCustomerId());
        }
    }

    /**
     * @param Creditmemo $creditmemo
     */
    public function syncCreditmemo($creditmemo)
    {
        try {
            if ($creditmemo->getId() && $creditmemo->getCreatedAt() == $creditmemo->getUpdatedAt()) {
                $this->helperEmailMarketing->sendOrderRequest($creditmemo, EmailMarketing::CREDITMEMO_URL);
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
