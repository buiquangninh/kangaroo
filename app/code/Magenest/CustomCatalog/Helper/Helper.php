<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_Kangaroo extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_Kangaroo
 */

namespace Magenest\CustomCatalog\Helper;

use Magenest\CustomInventoryReservation\Rewrite\Magento\InventorySales\Model\Frontend\GetProductSalableQty;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Helper extends AbstractHelper
{
    const CONFIG_PATH_ENABLE_RELATED_GENERATION = 'catalog/related_generate/enable';
    const CONFIG_PATH_NUMBER_PRODUCT_GENERATION = 'catalog/related_generate/generate_amount';

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var GetProductSalableQty
     */
    private $getProductSalableQty;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetAssignedStockIdForWebsite
     */
    private $getAssignedStockIdForWebsite;

    /**
     * @var ProductLinkManagementInterface
     */
    private $productLinkManagement;

    /**
     * Helper constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param GetProductSalableQty $getProductSalableQty
     * @param LoggerInterface $logger
     * @param GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite
     * @param ProductLinkManagementInterface $productLinkManagement
     */
    public function __construct(
        Context      $context,
        StoreManagerInterface $storeManager,
        GetProductSalableQty $getProductSalableQty,
        LoggerInterface $logger,
        GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite,
        ProductLinkManagementInterface $productLinkManagement
    ) {
        $this->_storeManager = $storeManager;
        $this->getProductSalableQty = $getProductSalableQty;
        $this->logger = $logger;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
        $this->productLinkManagement = $productLinkManagement;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isGenerationEnable()
    {
        return $this->getStoreConfig(self::CONFIG_PATH_ENABLE_RELATED_GENERATION);
    }

    /**
     * @param $path
     * @param $storeId
     * @return mixed
     */
    public function getStoreConfig($path, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->getStoreId();
        }

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        try {
            $storeId = $this->_storeManager->getStore()->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = null;
        }

        return $storeId;
    }

    /**
     * @return mixed
     */
    public function getGeneratedProductNumber()
    {
        return $this->getStoreConfig(self::CONFIG_PATH_NUMBER_PRODUCT_GENERATION);
    }

    /**
     * @return bool
     */
    public function isSalableInArea($product)
    {
        if ($product->getTypeId() == "configurable") {
            $childrenProduct = $product->getTypeInstance()->getUsedProducts($product);
        } elseif ($product->getTypeId() == 'grouped') {
            $childrenProduct = $product->getTypeInstance()->getAssociatedProducts($product);
        } elseif ($product->getTypeId() == 'bundle') {
            $childrenProduct = $this->productLinkManagement->getChildren($product->getData('sku'));
            return !$product->isSalable();
        }
        if (isset($childrenProduct) && $childrenProduct) {
            $salableQtyInArea = 0;
            foreach ($childrenProduct as $childProduct) {
                $salableQtyInArea += $this->getProductSalableQty->execute(
                    $childProduct->getSku(),
                    $this->getStockIdForWebsite()
                );
            }
        } else {
            $salableQtyInArea = $this->getProductSalableQty->execute(
                $product->getSku(),
                $this->getStockIdForWebsite()
            );
        }

        if (is_numeric($salableQtyInArea) && $salableQtyInArea > 0) {
            return false;
        }

        return true;
    }

    /**
     * Get website code
     *
     * @return int|null
     */
    private function getStockIdForWebsite()
    {
        try {
            $websiteCode = $this->_storeManager->getWebsite()->getCode();
        } catch (LocalizedException $localizedException) {
            $websiteCode = null;
            $this->logger->error($localizedException->getMessage());
        }
        return $this->getAssignedStockIdForWebsite->execute($websiteCode);
    }
}
