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

namespace Lof\FlashSales\Model\Indexer;

use Lof\FlashSales\Api\Data\ProductPriceInterface;
use Lof\FlashSales\Model\AppliedProducts;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory as AppliedProductsCollectionFactory;
use Lof\FlashSales\Model\ResourceModel\FlashSales\Collection;
use Lof\FlashSales\Model\ResourceModel\FlashSales\CollectionFactory as FlashSalesCollectionFactory;
use Lof\FlashSales\Model\ResourceModel\ProductPrice;
use Lof\FlashSales\Ui\Component\Listing\Column\StoreViewOptions;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var CacheContext
     */
    private $cacheContext;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductPrice
     */
    private $productPriceResource;

    /**
     * @var AppliedProductsCollectionFactory
     */
    private $appliedProductsCollectionFactory;

    /**
     * @var FlashSalesCollectionFactory
     */
    protected $flashSalesCollectionFactory;

    /**
     * @var int
     */
    private $batchCacheCount;

    /**
     * @var int
     */
    private $batchCount;

    /**
     * IndexBuilder constructor.
     *
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ManagerInterface $eventManager
     * @param ProductPrice $productPriceResource
     * @param AppliedProductsCollectionFactory $appliedProductsCollectionFactory
     * @param FlashSalesCollectionFactory $flashSalesCollectionFactory
     * @param CacheContext $cacheContext
     * @param int $batchCount
     * @param int $batchCacheCount
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        ProductCollectionFactory $productCollectionFactory,
        ManagerInterface $eventManager,
        ProductPrice $productPriceResource,
        AppliedProductsCollectionFactory $appliedProductsCollectionFactory,
        FlashSalesCollectionFactory $flashSalesCollectionFactory,
        CacheContext $cacheContext,
        $batchCount = 1000,
        $batchCacheCount = 100
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eventManager = $eventManager;
        $this->cacheContext = $cacheContext;
        $this->batchCount = $batchCount;
        $this->batchCacheCount = $batchCacheCount;
        $this->flashSalesCollectionFactory = $flashSalesCollectionFactory;
        $this->appliedProductsCollectionFactory = $appliedProductsCollectionFactory;
        $this->productPriceResource = $productPriceResource;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param $flashSalesId
     * @throws LocalizedException
     */
    public function cleanByFlashSalesIds($flashSalesId)
    {
        $this->productPriceResource->cleanByFlashSalesIds($flashSalesId);
    }

    /**
     * @throws LocalizedException
     */
    public function reindexFull()
    {
        $this->productPriceResource->beginTransaction();
        try {
            $this->doReindexFull();
            $this->productPriceResource->commit();
        } catch (\Exception $e) {
            $this->productPriceResource->rollBack();
            $this->logger->critical($e->getMessage());
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function doReindexFull()
    {
        $this->productPriceResource->cleanAllIndex();

        $ids = $this->getAllProductIds();
        foreach ($this->getAllFlashSales() as $flashSale) {
            $this->reindexByFlashSalesAndProductIds($flashSale, $ids);
        }

        return $this;
    }

    /**
     * @param $id
     * @throws LocalizedException
     */
    public function reindexByFlashSalesId($id)
    {
        $this->reindexByFlashSalesIds([$id]);
    }

    /**
     * @param $ids
     * @throws LocalizedException
     */
    public function reindexByFlashSalesIds($ids)
    {
        $this->productPriceResource->beginTransaction();
        try {
            $this->cleanByFlashSalesIds($ids);
            $this->doReindexByFlashSalesIds($ids);
            $this->productPriceResource->commit();
        } catch (\Exception $e) {
            $this->productPriceResource->rollBack();
            $this->logger->critical($e);
            throw new LocalizedException(
                __("Lof Flash Sales indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * @param $ids
     * @return $this
     */
    public function doReindexByFlashSalesIds($ids)
    {
        $productIds = $this->getAllProductIds();
        foreach ($this->getFlashSalesCollection($ids) as $flashSale) {
            $this->reindexByFlashSalesAndProductIds($flashSale, $productIds);
            $this->cacheContext->registerEntities(AppliedProducts::CACHE_TAG, [$flashSale->getFlashSalesId()]);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }

        return $this;
    }

    /**
     * @param $flashSale
     * @param null $ids
     * @return $this
     * @throws LocalizedException
     */
    public function reindexByFlashSalesAndProductIds($flashSale, $ids = null)
    {
        if (!$ids) {
            return $this;
        }

        list($rows, $productIds) = $this->prepareData($flashSale, $ids);

        if (!empty($rows)) {
            $this->productPriceResource->insertIndexData($rows);
        }

        if (!empty($productIds)) {
            $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
            $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
        }

        return $this;
    }

    /**
     * @param $flashSale
     * @param $ids
     * @return array
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareData($flashSale, $ids)
    {
        $count = 0;
        $rows = [];
        $productIds = [];
        $matchedProductIds = $this->getProductIdsFromFlashSalesId($flashSale);
        $flashSaleStoreIds = $this->getFlashSaleStoreIds($flashSale->getFlashSalesId());
        if ($flashSaleStoreIds && is_array($flashSaleStoreIds) && $matchedProductIds) {
            foreach ($ids as $productId) {
                $productId = (int)$productId;
                if (!array_key_exists($productId, $matchedProductIds)) {
                    continue;
                }

                $matchedStores = array_keys($matchedProductIds[$productId]);
                $stores = array_intersect($matchedStores, $flashSaleStoreIds);
                if (!$stores) {
                    continue;
                }

                foreach ($stores as $storeId) {
                    $flashSalesId = $flashSale->getFlashSalesId();
                    $appliedProducts = $this->getAppliedProductFromProductId(
                        $productId,
                        $flashSalesId
                    );

                    foreach ($appliedProducts as $appliedProduct) {
                        $rows[] = [
                            ProductPriceInterface::PRODUCT_ID => $productId,
                            ProductPriceInterface::FLASH_SALE_PRICE => $appliedProduct->getData('flash_sale_price'),
                            ProductPriceInterface::FLASHSALES_ID => $flashSalesId,
                            ProductPriceInterface::STORE_ID => $storeId
                        ];
                    }
                    $count++;
                }

                $productIds[] = $productId;
                if ($count >= $this->batchCount) {
                    $this->productPriceResource->insertIndexData($rows);
                    $rows = [];
                    $count = 0;
                }

                if (count($productIds) > $this->batchCacheCount) {
                    $this->cacheContext->registerEntities(Product::CACHE_TAG, $productIds);
                    $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);

                    $productIds = [];
                }
            }
        }

        return [$rows, $productIds];
    }

    /**
     * @param $productId
     * @param $flashSalesId
     * @return \Lof\FlashSales\Model\ResourceModel\AppliedProducts\Collection
     */
    public function getAppliedProductFromProductId($productId, $flashSalesId)
    {
        return $this->appliedProductsCollectionFactory->create()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('flashsales_id', $flashSalesId);
    }

    /**
     * @return array
     */
    private function getAllProductIds()
    {
        $collection = $this->productCollectionFactory->create();

        return $collection->getAllIds();
    }

    /**
     * @return Collection
     */
    public function getAllFlashSales()
    {
        return $this->flashSalesCollectionFactory->create()->addFieldToFilter('is_active', 1);
    }

    /**
     * @return Collection
     */
    public function getFlashSalesCollection($ids)
    {
        return $this->flashSalesCollectionFactory->create()
            ->addFieldToFilter('flashsales_id', $ids)
            ->addFieldToFilter('is_active', 1);
    }

    /**
     * @param $flashSalesId
     * @return |null
     */
    public function getFlashSaleStoreIds($flashSalesId)
    {
        $flashSaleStoreIds = [];
        $flashSale = $this->flashSalesCollectionFactory->create()
            ->addFieldToFilter('flashsales_id', $flashSalesId)
            ->addFieldToFilter('is_active', 1)->getFirstItem();

        if ($flashSale) {
            if (in_array(StoreViewOptions::ALL_STORE_VIEWS, $flashSale->getStoreId())) {
                foreach ($this->storeManager->getStores() as $store) {
                    $flashSaleStoreIds[] = $store->getId();
                }
            } else {
                return $flashSale->getStoreId();
            }

            return $flashSaleStoreIds;
        }

        return null;
    }

    /**
     * @param $flashSale
     * @return array
     */
    public function getProductIdsFromFlashSalesId($flashSale)
    {
        $productIds = [];
        $storeIds = [];
        $appliedProducts = $this->appliedProductsCollectionFactory->create()
            ->addFieldToFilter('flashsales_id', $flashSale->getFlashSalesId());
        if (in_array(StoreViewOptions::ALL_STORE_VIEWS, $flashSale->getStoreId())) {
            foreach ($this->storeManager->getStores() as $store) {
                $storeIds[] = $store->getId();
            }
        } else {
            $storeIds = $flashSale->getStoreId();
        }
        foreach ($appliedProducts->getItems() as $appliedProduct) {
            foreach ($storeIds as $storeId) {
                $productIds[$appliedProduct->getProductId()][$storeId] = true;
            }
        }
        return $productIds;
    }
}
