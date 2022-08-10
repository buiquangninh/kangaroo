<?php
/**
 * Copyright © CustomInventoryReservation All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\CustomInventoryReservation\Plugin\Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\Frontend;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magenest\CustomInventoryReservation\Api\GetReservationsQuantityInterfaceV2;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;
use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ProductSalabilityErrorInterfaceFactory;

class IsSalableWithReservationsCondition
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var GetReservationsQuantityInterfaceV2
     */
    private $getReservationsQuantity;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @var ProductSalabilityErrorInterfaceFactory
     */
    private $productSalabilityErrorFactory;

    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @param GetStockItemDataInterface $getStockItemData
     * @param GetReservationsQuantityInterfaceV2 $getReservationsQuantity
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     * @param ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     */
    public function __construct(
        GetStockItemDataInterface $getStockItemData,
        GetReservationsQuantityInterfaceV2 $getReservationsQuantity,
        GetStockItemConfigurationInterface $getStockItemConfiguration,
        \Magento\Framework\App\State $state,
        ProductSalabilityErrorInterfaceFactory $productSalabilityErrorFactory,
        ProductSalableResultInterfaceFactory $productSalableResultFactory
    ) {
        $this->getStockItemData = $getStockItemData;
        $this->getReservationsQuantity = $getReservationsQuantity;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
        $this->productSalabilityErrorFactory = $productSalabilityErrorFactory;
        $this->state = $state;
        $this->productSalableResultFactory = $productSalableResultFactory;
    }

    public function aroundExecute(
        \Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsSalableWithReservationsCondition $subject,
        \Closure $proceed,
        string $sku,
        int $stockId,
        float $requestedQty
    ) {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        if (null === $stockItemData) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_salable_with_reservations-no_data',
                    'message' => __('The requested sku is not assigned to given stock')
                ])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }

        /** @var StockItemConfigurationInterface $stockItemConfiguration */
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($sku, $stockId);

        $qtyWithReservation = $stockItemData[GetStockItemDataInterface::QUANTITY] +
            $this->getReservationsQuantity->execute($sku, $stockId);
        $qtyLeftInStock = $qtyWithReservation - $stockItemConfiguration->getMinQty();
        $isInStock = bccomp((string)$qtyLeftInStock, (string)$requestedQty, 4) >= 0;
        $isEnoughQty = (bool)$stockItemData[GetStockItemDataInterface::IS_SALABLE] && $isInStock;

        if (!$isEnoughQty) {
            $errors = [
                $this->productSalabilityErrorFactory->create([
                    'code' => 'is_salable_with_reservations-not_enough_qty',
                    'message' => __('The requested qty is not available')
                ])
            ];
            return $this->productSalableResultFactory->create(['errors' => $errors]);
        }
        return $this->productSalableResultFactory->create(['errors' => []]);

    }
}
