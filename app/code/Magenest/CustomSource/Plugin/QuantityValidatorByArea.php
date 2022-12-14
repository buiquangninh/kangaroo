<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * Created by PhpStorm.
 * User: crist
 * Date: 24/11/2021
 * Time: 15:01
 */

namespace Magenest\CustomSource\Plugin;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Helper\Data;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\StockItem;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Item;

class QuantityValidatorByArea extends QuantityValidator
{
    /**
     * @var \Magenest\CustomSource\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        Option $optionInitializer,
        StockItem $stockItemInitializer,
        StockRegistryInterface $stockRegistry,
        StockStateInterface $stockState,
        \Magenest\CustomSource\Helper\Data $data,
        Context $context
    ) {
        $this->dataHelper = $data;
        $this->context = $context;
        parent::__construct(
            $optionInitializer,
            $stockItemInitializer,
            $stockRegistry,
            $stockState
        );
    }

    /**
     * Add error information to Quote Item
     *
     * @param \Magento\Framework\DataObject $result
     * @param Item $quoteItem
     * @return void
     */
    private function addErrorInfoToQuote($result, $quoteItem)
    {
        $quoteItem->addErrorInfo(
            'cataloginventory',
            Data::ERROR_QTY,
            $result->getMessage()
        );

        $quoteItem->getQuote()->addErrorInfo(
            $result->getQuoteMessageIndex(),
            'cataloginventory',
            Data::ERROR_QTY,
            $result->getQuoteMessage()
        );
    }

    /**
     * Check product inventory data when quote item quantity declaring
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validate(Observer $observer)
    {
        /* @var $quoteItem Item */
        $quoteItem = $observer->getEvent()->getItem();
        if (!$quoteItem ||
            !$quoteItem->getProductId() ||
            !$quoteItem->getQuote()
        ) {
            return;
        }
        $product = $quoteItem->getProduct();
        $qty = $quoteItem->getQty();

        /* @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        if (!$stockItem instanceof StockItemInterface) {
            throw new LocalizedException(__('The Product stock item is invalid. Verify the stock item and try again.'));
        }

        if (($options = $quoteItem->getQtyOptions()) && $qty > 0) {
            foreach ($options as $option) {
                $this->optionInitializer->initialize($option, $quoteItem, $qty);
            }
        } else {
            $this->stockItemInitializer->initialize($stockItem, $quoteItem, $qty);
        }

        if ($quoteItem->getQuote()->getIsSuperMode()) {
            return;
        }

        /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $stockStatus */
        $stockStatus = $this->stockRegistry->getStockStatus($product->getId(), $product->getStore()->getWebsiteId());

        /* @var \Magento\CatalogInventory\Api\Data\StockStatusInterface $parentStockStatus */
        $parentStockStatus = false;

        /**
         * Check if product in stock. For composite products check base (parent) item stock status
         */
        if ($quoteItem->getParentItem()) {
            $product = $quoteItem->getParentItem()->getProduct();
            $parentStockStatus = $this->stockRegistry->getStockStatus(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            );
        }

        if ($stockStatus) {
            if (in_array(
                $stockStatus->getTypeId(),
                [Product\Type::DEFAULT_TYPE, Product\Type::TYPE_VIRTUAL]
            )) {
                $stockData = $this->dataHelper->getDataIsSalableOfProduct(
                    $this->dataHelper->getCurrentArea(),
                    [$stockStatus->getSku()]
                );
                foreach ($stockData as $stock) {
                    $quantityAvailable = $stock['qty'];
                    // Please read link: https://docs.magento.com/user-guide/catalog/inventory-backorders.html for more information
                    // With Backorders enabled,  A zero value of out-of-stock threshold allows for infinite backorders.
                    if ($stockItem->getBackorders() && !$stockItem->getMinQty()) {
                        $quantityAvailable = INF;
                    } else {
                        $quantityAvailable = $quantityAvailable - $stockItem->getMinQty();
                    }

                    if ($stock['sku'] == $stockStatus->getSku() && $quantityAvailable < $quoteItem->getQty()) {
                        $hasError = $quoteItem->getStockStateResult()
                            ? $quoteItem->getStockStateResult()->getHasError() : false;
                        if (!$hasError) {
                            $quoteItem->addErrorInfo(
                                'cataloginventory',
                                Data::ERROR_QTY,
                                __('This product is out of stock in the area.')
                            );
                        } else {
                            $quoteItem->addErrorInfo(null, Data::ERROR_QTY);
                        }
//                        $quoteItem->getQuote()->addErrorInfo(
//                            'stock',
//                            'cataloginventory',
//                            Data::ERROR_QTY,
//                            __('Some of the products are out of stock in the area.')
//                        );
                        $quoteItem->getQuote()->addErrorInfo(
                            'stock',
                            'cataloginventory',
                            Data::ERROR_QTY
                        );
                        return;
                    }
                }
            }

            if ($stockStatus->getStockStatus() === Stock::STOCK_OUT_OF_STOCK
                || $parentStockStatus && $parentStockStatus->getStockStatus() == Stock::STOCK_OUT_OF_STOCK
            ) {
                $hasError = $quoteItem->getStockStateResult()
                    ? $quoteItem->getStockStateResult()->getHasError() : false;
                if (!$hasError) {
                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        Data::ERROR_QTY,
                        __('This product is out of stock.')
                    );
                } else {
                    $quoteItem->addErrorInfo(null, Data::ERROR_QTY);
                }
                $quoteItem->getQuote()->addErrorInfo(
                    'stock',
                    'cataloginventory',
                    Data::ERROR_QTY,
                    __('Some of the products are out of stock.')
                );
                return;
            } else {
                // Delete error from item and its quote, if it was set due to item out of stock
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Data::ERROR_QTY);
            }
        }

        /**
         * Check item for options
         */
        if ($options) {
            $qty = $product->getTypeInstance()->prepareQuoteItemQty($quoteItem->getQty(), $product);
            $quoteItem->setData('qty', $qty);
            if ($stockStatus) {
                $this->checkOptionsQtyIncrements($quoteItem, $options);
            }

            // variable to keep track if we have previously encountered an error in one of the options
            $removeError = true;
            foreach ($options as $option) {
                $result = $option->getStockStateResult();
                if ($result->getHasError()) {
                    $option->setHasError(true);
                    //Setting this to false, so no error statuses are cleared
                    $removeError = false;
                    $this->addErrorInfoToQuote($result, $quoteItem);
                }
            }
            if ($removeError) {
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Data::ERROR_QTY);
            }
        } else {
            if ($quoteItem->getParentItem() === null) {
                $result = $quoteItem->getStockStateResult();
                if ($result->getHasError()) {
                    $this->addErrorInfoToQuote($result, $quoteItem);
                } else {
                    $this->_removeErrorsFromQuoteAndItem($quoteItem, Data::ERROR_QTY);
                }
            }
        }
    }

    /**
     * Verifies product options quantity increments.
     *
     * @param Item $quoteItem
     * @param array $options
     * @return void
     */
    private function checkOptionsQtyIncrements(Item $quoteItem, array $options): void
    {
        $removeErrors = true;
        foreach ($options as $option) {
            $optionValue = $option->getValue();
            $optionQty = $quoteItem->getData('qty') * $optionValue;
            $result = $this->stockState->checkQtyIncrements(
                $option->getProduct()->getId(),
                $optionQty,
                $option->getProduct()->getStore()->getWebsiteId()
            );
            if ($result->getHasError()) {
                $quoteItem->getQuote()->addErrorInfo(
                    $result->getQuoteMessageIndex(),
                    'cataloginventory',
                    Data::ERROR_QTY_INCREMENTS,
                    $result->getQuoteMessage()
                );

                $removeErrors = false;
            }
        }

        if ($removeErrors) {
            // Delete error from item and its quote, if it was set due to qty problems
            $this->_removeErrorsFromQuoteAndItem(
                $quoteItem,
                Data::ERROR_QTY_INCREMENTS
            );
        }
    }
}
