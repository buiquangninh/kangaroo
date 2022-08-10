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
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;

class ValidateCartObserver implements ObserverInterface
{

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @param ManagerInterface $messageManager
     * @param RedirectInterface $redirect
     * @param CustomerCart $cart
     * @param Data $helperData
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        CustomerCart $cart,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->cart = $cart;
    }

    /**
     * Validate Cart Before going to checkout
     * - event: controller_action_predispatch_checkout_index_index
     *
     * @param Observer $observer
     *
     * @return ValidateCartObserver
     */
    public function execute(Observer $observer)
    {
        $controller = $observer->getControllerAction();
        foreach ($this->cart->getQuote()->getAllVisibleItems() as $quoteItem) {
            $appliedProduct = $this->helperData->getAppliedProductCollection()
                ->addFieldToFilter('sku', $quoteItem->getSku())
                ->getFirstItem();
            if ($appliedProduct->getQtyLimit() != null && $quoteItem->getQty() > $appliedProduct->getQtyLimit()) {
                $stockQty = $appliedProduct->getQtyLimit();
                if ($appliedProduct->getQtyLimit() == null || $appliedProduct->getQtyLimit() == 0) {
                    if ($this->helperData->getSellOverQuantityLimit()) {
                        return $this;
                    } else {
                        $this->messageManager->addWarningMessage(__(
                            '%1 has been sold out of flash sale products.',
                            $quoteItem->getName()
                        ));
                        $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
                    }
                } else {
                    $this->messageManager->addWarningMessage(__(
                        'You can add a maximum of %1 item(s) to purchase only.',
                        $appliedProduct->getQtyLimit()
                    ));
                    $this->redirect->redirect($controller->getResponse(), 'checkout/cart');
                }
                $quoteItem->getQuote()->setIsSuperMode(true);
                $quoteItem->getQuote()->setHasError(false);
                $quoteItem->setHasError(false);
                $quoteItem->setData('qty', $stockQty);
            }
        }
        return $this;
    }
}
