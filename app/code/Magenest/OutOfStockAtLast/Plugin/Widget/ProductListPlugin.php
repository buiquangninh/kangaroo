<?php
namespace Magenest\OutOfStockAtLast\Plugin\Widget;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\DB\Select;

/**
 * Class ProductsListPlugin
 */
class ProductListPlugin
{
    public function __construct(
        \Magenest\CustomSource\Helper\Data $dataHelper,
        \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $sourceItemsBySku
    )
    {
        $this->dataHelper = $dataHelper;
        $this->sourceItemsBySku = $sourceItemsBySku;
    }
    /**
     * @param ProductsList $subject`
     * @param Collection $result
     * @return Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateCollection(ProductsList $subject, Collection $result)
    {
        $areaCode = $this->dataHelper->getCurrentArea();
        $result->getSelect()->reset(\Magento\Framework\DB\Select::ORDER);
        $result->setOrder('out_of_stock_at_last_' . $areaCode ,'desc');
        return $result;
    }
}
