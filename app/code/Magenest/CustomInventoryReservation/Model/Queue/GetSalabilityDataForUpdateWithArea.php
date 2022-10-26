<?php


namespace Magenest\CustomInventoryReservation\Model\Queue;


use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryIndexer\Model\Queue\GetSalabilityDataForUpdate;
use Magento\InventoryIndexer\Model\Queue\ReservationData;
use Magento\InventorySalesApi\Api\AreProductsSalableInterface;
use Magento\InventorySalesApi\Model\GetStockItemDataInterface;

class GetSalabilityDataForUpdateWithArea extends GetSalabilityDataForUpdate
{
    /**
     * @var AreProductsSalableInterface
     */
    private $areProductsSalable;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @param AreProductsSalableInterface $areProductsSalable
     * @param GetStockItemDataInterface $getStockItemData
     */
    public function __construct(
        AreProductsSalableInterface $areProductsSalable,
        GetStockItemDataInterface $getStockItemData
    ) {
        $this->areProductsSalable = $areProductsSalable;
        $this->getStockItemData = $getStockItemData;
        parent::__construct($areProductsSalable, $getStockItemData);
    }

    /**
     * Get stock status changes for given reservation.
     *
     * @param ReservationData $reservationData
     * @return bool[] - ['sku' => bool]
     */
    public function execute(ReservationData $reservationData): array
    {
        $salabilityData = $this->areProductsSalable->execute(
            $reservationData->getSkus(),
            $reservationData->getStock(),
            $reservationData->getArea()
        );

        $data = [];
        foreach ($salabilityData as $isProductSalableResult) {
            $currentStatus = $this->isCurrentlySalable(
                $isProductSalableResult->getSku(),
                $reservationData->getStock()
            );
            if ($isProductSalableResult->isSalable() !== $currentStatus) {
                $data[$isProductSalableResult->getSku()] = $isProductSalableResult->isSalable();
            }
        }

        return $data;
    }

    /**
     * Get current is_salable value.
     *
     * @param string $sku
     * @param int $stockId
     *
     * @return bool
     */
    private function isCurrentlySalable(string $sku, int $stockId): bool
    {
        try {
            $data = $this->getStockItemData->execute($sku, $stockId);
            $isSalable = $data ? (bool)$data[GetStockItemDataInterface::IS_SALABLE] : false;
        } catch (LocalizedException $e) {
            $isSalable = false;
        }

        return $isSalable;
    }
}
