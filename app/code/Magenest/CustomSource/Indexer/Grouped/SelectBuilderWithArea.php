<?php

namespace Magenest\CustomSource\Indexer\Grouped;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\InventoryGroupedProductIndexer\Indexer\SelectBuilder;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\InventoryMultiDimensionalIndexerApi\Model\Alias;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameBuilder;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameResolverInterface;

class SelectBuilderWithArea extends SelectBuilder
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IndexNameBuilder
     */
    private $indexNameBuilder;

    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ResourceConnection $resourceConnection
     * @param IndexNameBuilder $indexNameBuilder
     * @param IndexNameResolverInterface $indexNameResolver
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        IndexNameBuilder $indexNameBuilder,
        IndexNameResolverInterface $indexNameResolver,
        MetadataPool $metadataPool
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->indexNameBuilder = $indexNameBuilder;
        $this->indexNameResolver = $indexNameResolver;
        $this->metadataPool = $metadataPool;
        parent::__construct($resourceConnection, $indexNameBuilder, $indexNameResolver, $metadataPool);
    }

    /**
     * Prepare select
     *
     * @param int $stockId
     * @return Select
     * @throws \Exception
     */
    public function execute(int $stockId): Select
    {
        $connection = $this->resourceConnection->getConnection();

        $indexName = $this->indexNameBuilder
            ->setIndexId(InventoryIndexer::INDEXER_ID)
            ->addDimension('stock_', (string)$stockId)
            ->setAlias(Alias::ALIAS_MAIN)
            ->build();

        $indexTableName = $this->indexNameResolver->resolveName($indexName);

        $metadata  = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $select = $connection->select();
        $select->from(
            ['parent_link' => $this->resourceConnection->getTableName('catalog_product_link')],
            []
        )->joinInner(
            ['parent_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            "parent_product_entity.{$linkField} = parent_link.product_id",
            [

                IndexStructure::SKU => 'parent_product_entity.sku'
            ]
        )->joinInner(
            ['child_link' => $this->resourceConnection->getTableName('catalog_product_link')],
            'child_link.product_id = parent_link.product_id AND child_link.link_type_id = ' . Link::LINK_TYPE_GROUPED,
            []
        )->joinInner(
            ['child_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            "child_product_entity.entity_id = child_link.linked_product_id",
            []
        )->joinInner(
            ['child_stock' => $indexTableName],
            'child_stock.sku = child_product_entity.sku',
            [
                IndexStructure::QUANTITY => 'SUM(child_stock.quantity)',
                IndexStructure::IS_SALABLE => 'MAX(child_stock.is_salable)',
                'area_code'
            ]
        )->joinInner(
            ['child_filter_product_entity' => $this->resourceConnection->getTableName('catalog_product_entity')],
            "child_filter_product_entity.entity_id = parent_link.linked_product_id",
            []
        )->where(
            'parent_link.link_type_id = ' . Link::LINK_TYPE_GROUPED
        )->group(
            ['parent_product_entity.sku', 'area_code']
        );

        return $select;
    }
}
