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

namespace Magetop\Smtp\Observer\Quote;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magetop\Smtp\Helper\EmailMarketing;
use Psr\Log\LoggerInterface;

/**
 * Class DeleteQuote
 * @package Magetop\Smtp\Observer\Quote
 */
class DeleteQuote implements ObserverInterface
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
     * DeleteQuote constructor.
     *
     * @param EmailMarketing $helperEmailMarketing
     * @param LoggerInterface $logger
     */
    public function __construct(
        EmailMarketing $helperEmailMarketing,
        LoggerInterface $logger
    ) {
        $this->helperEmailMarketing = $helperEmailMarketing;
        $this->logger = $logger;
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
            try {
                /* @var Quote $quote */
                $quote = $observer->getEvent()->getDataObject();
                if ($quote->getId()) {
                    $this->helperEmailMarketing->deleteQuote($quote->getId(), $quote->getStoreId());
                }
            } catch (Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
