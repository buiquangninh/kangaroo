<?php


namespace Magenest\CustomInventoryReservation\Model;


use Magento\InventorySales\Model\AreProductsSalable;
use Magento\InventorySalesApi\Api\Data\IsProductSalableResultInterfaceFactory;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;

class AreProductsSalableWithArea extends AreProductsSalable
{
    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var IsProductSalableResultInterfaceFactory
     */
    private $isProductSalableResultFactory;

    /**
     * @param IsProductSalableInterface $isProductSalable
     * @param IsProductSalableResultInterfaceFactory $isProductSalableResultFactory
     */
    public function __construct(
        IsProductSalableInterface $isProductSalable,
        IsProductSalableResultInterfaceFactory $isProductSalableResultFactory
    ) {
        $this->isProductSalable = $isProductSalable;
        $this->isProductSalableResultFactory = $isProductSalableResultFactory;
        parent::__construct($isProductSalable, $isProductSalableResultFactory);
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus, int $stockId, $areaCode = ''): array
    {
        $results = [];
        foreach ($skus as $sku) {
            $isSalable = $this->isProductSalable->execute($sku, $stockId, $areaCode);
            $results[] = $this->isProductSalableResultFactory->create(
                [
                    'sku' => $sku,
                    'stockId' => $stockId,
                    'isSalable' => $isSalable,
                ]
            );
        }

        return $results;
    }
}
