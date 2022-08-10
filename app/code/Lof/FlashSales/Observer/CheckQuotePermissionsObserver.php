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
use Lof\FlashSales\Model\Adminhtml\System\Config\Source\ConfigData;
use Lof\FlashSales\Model\FlashSales;
use Magento\Customer\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class CheckQuotePermissionsObserver implements ObserverInterface
{
    /**
     * Customer session instance
     *
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var FlashSales
     */
    protected $_flashSalesModel;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * CheckQuotePermissionsObserver constructor.
     *
     * @param Data $_helperData
     * @param Session $customerSession
     * @param StoreRepositoryInterface $storeRepository
     * @param FlashSales $flashSalesModel
     */
    public function __construct(
        Data $_helperData,
        Session $customerSession,
        StoreRepositoryInterface $storeRepository,
        FlashSales $flashSalesModel
    ) {
        $this->_helperData = $_helperData;
        $this->_flashSalesModel = $flashSalesModel;
        $this->_customerSession = $customerSession;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return $this;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getCart()->getQuote();
        $allQuoteItems = $quote->getAllItems();
        $this->_initPermissionsOnQuoteItems($quote);

        foreach ($allQuoteItems as $quoteItem) {
            /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
            if ($quoteItem->getParentItem()) {
                continue;
            }

            if ($quoteItem->getDisableAddToCart() && !$quoteItem->isDeleted()) {
                $quote->removeItem($quoteItem->getQuoteId());
                $quote->deleteItem($quoteItem);
                $quote->setHasError(
                    true
                )->addMessage(
                    __('You cannot add "%1" to the cart.', $quoteItem->getName())
                );
            }
        }

        return $this;
    }

    /**
     * Initialize permissions for quote items
     *
     * @param Quote $quote
     * @return $this
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _initPermissionsOnQuoteItems(Quote $quote)
    {
        $storeId = $quote->getStoreId();
        $customerGroupId = $this->_customerSession->getCustomerGroupId();

        $storeId = $this->storeRepository->getById($storeId)->getId();

        if (!is_array($quote->getAllItems()) || !$quote->getAllItems()) {
            return $this;
        }

        foreach ($quote->getAllItems() as $item) {
            $categoryIds = $item->getProduct()->getCategoryIds();

            $categoryPermissionIndex = $this->_flashSalesModel->getIndexForCategory(
                $categoryIds,
                $storeId
            );

            if (!is_array($categoryPermissionIndex) || !$categoryPermissionIndex) {
                continue;
            }

            foreach ($categoryPermissionIndex as $permission) {
                if ($permission['is_default_private_config']) {
                    if ($this->_helperData->getCheckoutItemsMode() === ConfigData::GRANT_ALL &&
                        $this->_helperData->getCatalogCategoryViewMode() === ConfigData::GRANT_ALL) {
                        return $this;
                    }
                    if (empty($categoryIds)) {
                        $item->setDisableAddToCart(true);
                        continue;
                    }
                    if ($this->_helperData->getCatalogCategoryViewMode() == ConfigData::GRANT_CUSTOMER_GROUP) {
                        if (!in_array(
                            $customerGroupId,
                            $this->_helperData->getCatalogCategoryViewGroups()
                        )) {
                            $item->setDisableAddToCart(true);
                        }
                    }
                    if ($this->_helperData->getCheckoutItemsMode() == ConfigData::GRANT_CUSTOMER_GROUP) {
                        if (!in_array(
                            $customerGroupId,
                            $this->_helperData->getCheckoutItemsGroups()
                        )) {
                            $item->setDisableAddToCart(true);
                        }
                    }
                    if ($this->_helperData->getCheckoutItemsMode() == ConfigData::GRANT_NONE) {
                        $item->setDisableAddToCart(true);
                    }
                    if ($this->_helperData->getCatalogCategoryViewMode() == ConfigData::GRANT_NONE) {
                        $item->setDisableAddToCart(true);
                    }
                } else {
                    if ($permission['grant_checkout_items'] === ConfigData::GRANT_ALL &&
                        $permission['grant_event_view'] === ConfigData::GRANT_ALL) {
                        return $this;
                    }
                    if ($permission['grant_event_view'] === ConfigData::GRANT_CUSTOMER_GROUP) {
                        if (!in_array(
                            $customerGroupId,
                            $permission['grant_event_view_groups']
                        )) {
                            $item->setDisableAddToCart(true);
                        }
                    }
                    if ($permission['grant_checkout_items'] === ConfigData::GRANT_CUSTOMER_GROUP) {
                        if (!in_array(
                            $customerGroupId,
                            $permission['grant_checkout_items_groups']
                        )) {
                            $item->setDisableAddToCart(true);
                        }
                    }
                    if ($permission['grant_checkout_items'] == ConfigData::GRANT_NONE) {
                        $item->setDisableAddToCart(true);
                    }
                    if ($permission['grant_event_view'] == ConfigData::GRANT_NONE) {
                        $item->setDisableAddToCart(true);
                    }
                }
            }
        }

        return $this;
    }
}
