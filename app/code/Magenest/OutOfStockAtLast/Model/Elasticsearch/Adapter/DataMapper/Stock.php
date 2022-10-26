<?php
declare(strict_types=1);

namespace Magenest\OutOfStockAtLast\Model\Elasticsearch\Adapter\DataMapper;

use Magento\InventorySales\Model\GetProductSalableQty;
use Magenest\CustomSource\Model\Source\Area\Options;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite;
use Magento\Store\Model\StoreManagerInterface;
use Magenest\OutOfStockAtLast\Model\ResourceModel\Inventory;

/**
 * Class Stock for mapping
 */
class Stock
{
    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Options
     */
    private $areaOptions;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var GetProductSalableQty
     */
    private $getProductSalableQty;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;


    /**
     * @var GetAssignedStockIdForWebsite
     */
    private $getAssignedStockIdForWebsite;

    /**
     * Stock constructor.
     * @param Inventory $inventory
     * @param StoreManagerInterface $storeManager
     * @param Options $areaOptions
     * @param Registry $registry
     * @param GetProductSalableQty $getProductSalableQty
     * @param ProductRepositoryInterface $productRepository
     * @param GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite
     */
    public function __construct(
        Inventory $inventory,
        StoreManagerInterface $storeManager,
        Options $areaOptions,
        Registry $registry,
        GetProductSalableQty $getProductSalableQty,
        ProductRepositoryInterface $productRepository,
        GetAssignedStockIdForWebsite $getAssignedStockIdForWebsite
    ) {
        $this->inventory                    = $inventory;
        $this->storeManager                 = $storeManager;
        $this->areaOptions                  = $areaOptions;
        $this->registry                     = $registry;
        $this->getProductSalableQty         = $getProductSalableQty;
        $this->productRepository            = $productRepository;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
    }

    /**
     * Map the attribute
     *
     * @param mixed $entityId
     * @param mixed $storeId
     * @return int[]
     * @throws NoSuchEntityException
     */
    public function map($entityId, $storeId): array
    {
        $result = [];
        foreach ($this->areaOptions->toOptionArray() as $option) {
            $sku = $this->inventory->getSkuRelation((int)$entityId);

            if (!$sku) {
                $result['out_of_stock_es_at_last_' . $option['value']] = 1;
            } else {
                $this->registry->unregister('current_area');
                $this->registry->register('current_area', $option['value']);
                $product = $this->productRepository->get($sku);

                if ($product->getTypeId() == 'bundle') {
                    $value                                              = $this->inventory->getStockStatus(
                        $sku,
                        $this->storeManager->getStore($storeId)->getWebsite()->getCode()
                    );
                    $result['out_of_stock_es_at_last_' . $option['value']] = $value;
                } else {
                    try {
                        if ($product->getTypeId() == "configurable") {
                            $childrenProduct = $product->getTypeInstance()->getUsedProducts($product);
                        } elseif ($product->getTypeId() == 'grouped') {
                            $childrenProduct = $product->getTypeInstance()->getAssociatedProducts($product);
                        }
                        if (isset($childrenProduct) && $childrenProduct) {
                            $salableQtyInArea = 0;
                            foreach ($childrenProduct as $childProduct) {
                                $salableQtyInArea += $this->getProductSalableQty->execute(
                                    $childProduct->getSku(),
                                    $this->getStockIdForWebsite(),
                                    $option['value']
                                );
                            }
                        } else {
                            $salableQtyInArea = $this->getProductSalableQty->execute(
                                $sku,
                                $this->getStockIdForWebsite(),
                                $option['value']
                            );
                        }

                        $salableQtyInArea = (int)(is_numeric($salableQtyInArea) && $salableQtyInArea > 0);
                    } catch (\Exception $exception) {
                        $salableQtyInArea = 0;
                    }
                    $result['out_of_stock_es_at_last_' . $option['value']] = $salableQtyInArea;

                }
            }
        }

        return $result;
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
        }
        return $this->getAssignedStockIdForWebsite->execute($websiteCode) ?: 2;
    }
}
