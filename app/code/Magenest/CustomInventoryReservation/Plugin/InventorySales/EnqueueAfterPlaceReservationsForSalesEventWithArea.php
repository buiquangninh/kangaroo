<?php


namespace Magenest\CustomInventoryReservation\Plugin\InventorySales;


use Magenest\CustomSource\Helper\Data;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magenest\CustomInventoryReservation\Model\Queue\ReservationDataWithArea;
use Magenest\CustomInventoryReservation\Model\Queue\ReservationDataWithAreaFactory;
use Magento\InventoryIndexer\Plugin\InventorySales\EnqueueAfterPlaceReservationsForSalesEvent;
use Magento\InventorySales\Model\PlaceReservationsForSalesEvent;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Model\GetAssignedStockIdForWebsiteInterface;

class EnqueueAfterPlaceReservationsForSalesEventWithArea extends EnqueueAfterPlaceReservationsForSalesEvent
{
    /**
     * Queue topic name.
     */
    private const TOPIC_RESERVATIONS_UPDATE_SALABILITY_STATUS = 'inventory.reservations.updateSalabilityStatus';

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var GetAssignedStockIdForWebsiteInterface
     */
    private $getAssignedStockIdForWebsite;

    /**
     * @var ReservationDataWithAreaFactory
     */
    private $reservationDataFactory;

    /** @var Data */
    private $areaData;

    /**
     * @param PublisherInterface $publisher
     * @param GetAssignedStockIdForWebsiteInterface $getAssignedStockIdForWebsite
     * @param ReservationDataWithAreaFactory $reservationDataFactory
     */
    public function __construct(
        PublisherInterface $publisher,
        GetAssignedStockIdForWebsiteInterface $getAssignedStockIdForWebsite,
        ReservationDataWithAreaFactory $reservationDataFactory,
        Data $areaData
    ) {
        $this->publisher = $publisher;
        $this->getAssignedStockIdForWebsite = $getAssignedStockIdForWebsite;
        $this->reservationDataFactory = $reservationDataFactory;
        $this->areaData = $areaData;
    }

    /**
     * Publish reservation data for reindex.
     *
     * @param PlaceReservationsForSalesEvent $subject
     * @param void $result
     * @param ItemToSellInterface[] $items
     * @param SalesChannelInterface $salesChannel
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        PlaceReservationsForSalesEvent $subject,
        $result,
        array $items,
        SalesChannelInterface $salesChannel
    ): void {
        $this->publisher->publish(
            self::TOPIC_RESERVATIONS_UPDATE_SALABILITY_STATUS,
            $this->getReservationsDataObject($salesChannel, $items)
        );
    }

    /**
     * Build reservation data transfer objects.
     *
     * @param SalesChannelInterface $salesChannel
     * @param ItemToSellInterface[] $items
     *
     * @return ReservationDataWithArea
     */
    private function getReservationsDataObject(SalesChannelInterface $salesChannel, array $items): ReservationDataWithArea
    {
        $stockId = $this->getAssignedStockIdForWebsite->execute($salesChannel->getCode());
        $skus = array_map(
            function (ItemToSellInterface $itemToSell): string {
                return $itemToSell->getSku();
            },
            $items
        );

        return $this->reservationDataFactory->create(
            [
                'stock' => $stockId,
                'skus' => $skus,
                'area' => $this->areaData->getCurrentArea()
            ]
        );
    }
}
