<?php
namespace Magenest\CustomInventoryReservation\Model\IsProductSalableCondition;


use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryReservationsApi\Model\GetReservationsQuantityInterface;
use Magento\InventorySales\Model\IsProductSalableCondition\IsSalableWithReservationsCondition;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;

class IsSalableWithReservationsConditionWithArea extends IsSalableWithReservationsCondition
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var GetReservationsQuantityInterface
     */
    private $getReservationsQuantity;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypesBySkus;

    /**
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetReservationsQuantityInterface $getReservationsQuantity
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param GetProductTypesBySkusInterface $getProductTypesBySkus
     */
    public function __construct(
        GetStockItemDataInterface $getStockItemData,
        GetReservationsQuantityInterface $getReservationsQuantity,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypesBySkus
    ) {
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        parent::__construct(
            $getStockItemData,
            $getReservationsQuantity,
            $getStockItemConfiguration,
            $isSourceItemManagementAllowedForProductType,
            $getProductTypesBySkus
        );
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId, $areaCode = false): bool
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId, $areaCode);
        if (null === $stockItemData) {
            // Sku is not assigned to Stock
            return false;
        }

        $productType = $this->getProductTypesBySkus->execute([$sku])[$sku];
        if (false === $this->isSourceItemManagementAllowedForProductType->execute($productType)) {
            return (bool)$stockItemData[GetStockItemDataInterface::IS_SALABLE];
        }

        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);
        $qtyWithReservation = $stockItemData[GetStockItemDataInterface::QUANTITY] +
            $this->getReservationsQuantity->execute($sku, $stockId);

        return $qtyWithReservation > $stockItemConfiguration->getMinQty();
    }
}
