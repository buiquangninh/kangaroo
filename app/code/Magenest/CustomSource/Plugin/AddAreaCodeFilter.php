<?php


namespace Magenest\CustomSource\Plugin;


use Magenest\CustomSource\Helper\Data;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\InventoryCatalog\Model\ResourceModel\AddStockDataToCollection;

class AddAreaCodeFilter
{
    /**
     * AddAreaCodeFilter constructor.
     * @param Data $data
     */
    public function __construct(
        Data $data
    )
    {
        $this->data = $data;
    }

    /**
     * @param AddStockDataToCollection $subject
     * @param $result
     * @param Collection $collection
     * @param bool $isFilterInStock
     * @param int $stockId
     */
    public function afterExecute(
        AddStockDataToCollection $subject,
        $result,
        Collection $collection,
        bool $isFilterInStock,
        int $stockId
    ) {
        $areaCode = $this->data->getCurrentArea();
        $collection->getSelect()->where('stock_status_index.area_code = ?', $areaCode);
    }
}
