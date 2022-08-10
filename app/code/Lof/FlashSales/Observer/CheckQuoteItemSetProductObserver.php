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
use Lof\FlashSales\Helper\PermissionsData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CheckQuoteItemSetProductObserver implements ObserverInterface
{

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var PermissionsData
     */
    protected $_permissionsData;

    /**
     * CheckQuoteItemSetProductObserver constructor.
     * @param Data $_helperData
     * @param PermissionsData $permissionsData
     */
    public function __construct(
        Data $_helperData,
        PermissionsData $permissionsData
    ) {
        $this->_permissionsData = $permissionsData;
        $this->_helperData = $_helperData;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        $quoteItem = $observer->getEvent()->getQuoteItem();
        $product = $observer->getEvent()->getProduct();

        if ($quoteItem->getId()) {
            return $this;
        }

        if ($quoteItem->getParentItem()) {
            $parentItem = $quoteItem->getParentItem();
        } else {
            $parentItem = false;
        }

        /* @var $quoteItem Item */
        if ($product->getDisableAddToCart() && !$quoteItem->isDeleted()) {
            $quoteItem->getQuote()->removeItem($quoteItem->getId());
            if ($parentItem) {
                throw new LocalizedException(
                    __('You cannot add "%1" to the cart.', $parentItem->getName())
                );
            } else {
                throw new LocalizedException(
                    __('You cannot add "%1" to the cart.', $quoteItem->getName())
                );
            }
        }

        return $this;
    }
}
