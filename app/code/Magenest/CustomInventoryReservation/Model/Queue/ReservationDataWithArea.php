<?php


namespace Magenest\CustomInventoryReservation\Model\Queue;


use Magento\InventoryIndexer\Model\Queue\ReservationData;

class ReservationDataWithArea extends ReservationData
{
    /**
     * @var string
     */
    private $area;

    /**
     * @var string[]
     */
    private $skus;

    /**
     * @var int
     */
    private $stockId;

    /**
     * ReservationDataWithArea constructor.
     * @param string[] $skus
     * @param int $stock
     * @param string $area
     */
    public function __construct(array $skus, int $stock, string $area)
    {
        $this->skus = $skus;
        $this->stockId = $stock;
        $this->area = $area;
        parent::__construct($skus, $stock);
    }

    /**
     * Retrieve area code.
     *
     * @return string
     */
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * Retrieve products SKUs to process.
     *
     * @return string[]
     */
    public function getSkus(): array
    {
        return $this->skus;
    }

    /**
     * Retrieve stock id.
     *
     * @return int
     */
    public function getStock(): int
    {
        return $this->stockId;
    }
}
