<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * User: leo
 * Date: 10/06/2022
 * Time: 09:59
 */
declare(strict_types=1);

namespace Magenest\CustomCatalog\Block\Catalog\Product\View;

use Magenest\CustomInventoryReservation\Rewrite\Magento\InventorySales\Model\Frontend\GetProductSalableQty;
use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Block\Product\Context;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite;

/**
 * Class OutOfStockArea
 */
class OutOfStockArea extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var GetProductSalableQty
     */
    private $getProductSalableQty;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
     * OutOfStockArea constructor.
     * @param Context $context
     * @param GetProductSalableQty $getProductSalableQty
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite
     * @param ProductLinkManagementInterface $productLinkManagement
     * @param array $data
     */
    public function __construct(
        Context $context,
        GetProductSalableQty $getProductSalableQty,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite,
        ProductLinkManagementInterface $productLinkManagement,
        array $data = []
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
        $this->productLinkManagement = $productLinkManagement;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function isOutOfStockInArea()
    {
        $product = $this->getProduct();
        if ($product->getTypeId() == "configurable") {
            $childrenProduct = $product->getTypeInstance()->getUsedProducts($product);
        } elseif ($product->getTypeId() == 'grouped') {
            $childrenProduct = $product->getTypeInstance()->getAssociatedProducts($product);
        } elseif ($product->getTypeId() == 'bundle') {
//            $childrenProduct = $this->productLinkManagement->getChildren($product->getData('sku'));
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
            $websiteCode = $this->storeManager->getWebsite()->getCode();
        } catch (LocalizedException $localizedException) {
            $websiteCode = null;
            $this->logger->error($localizedException->getMessage());
        }
        return $this->getAssignedStockIdForWebsite->execute($websiteCode);
    }
}
