<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\FlashSales\Observer;

use Lof\FlashSales\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class SalesQuoteItemQtySetAfterObserver implements ObserverInterface
{

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * SalesQuoteItemQtySetAfterObserver constructor.
     * @param Data $helperData
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Data $helperData,
        ManagerInterface $messageManager
    ) {
        $this->_helperData = $helperData;
        $this->_messageManager = $messageManager;
    }

    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $observer->getEvent()->getItem();

        $appliedProduct = $this->_helperData->getAppliedProductCollection()
            ->addFieldToFilter('sku', $quoteItem->getSku())
            ->getFirstItem();

        if ($appliedProduct->getQtyLimit() != null && $quoteItem->getQty() > $appliedProduct->getQtyLimit()) {
            $stockQty = $appliedProduct->getQtyLimit();
            if ($appliedProduct->getQtyLimit() == null || $appliedProduct->getQtyLimit() == 0) {
                if ($this->_helperData->getSellOverQuantityLimit()) {
                    return $this;
                } else {
                    $this->_messageManager->addWarningMessage(__(
                        '%1 has been sold out of flash sale products.',
                        $quoteItem->getName()
                    ));
                }
            } else {
                $this->_messageManager->addWarningMessage(__(
                    'You can add a maximum of %1 item(s) to purchase only.',
                    $appliedProduct->getQtyLimit()
                ));
            }
            $quoteItem->getQuote()->setIsSuperMode(true);
            $quoteItem->getQuote()->setHasError(false);
            $quoteItem->setHasError(false);
            $quoteItem->setData('qty', $stockQty);
        }

        return $this;
    }
}
