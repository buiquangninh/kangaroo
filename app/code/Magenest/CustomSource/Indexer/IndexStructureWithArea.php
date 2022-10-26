<?php

namespace Magenest\CustomSource\Indexer;

use Magento\Backend\Block\Widget\Tab;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Exception\StateException;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexName;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameResolverInterface;

class IndexStructureWithArea extends IndexStructure
{
    /**
     * Constants for represent fields in index table
     */
    const ID = 'id';
    const SKU = 'sku';
    const QUANTITY = 'quantity';
    const IS_SALABLE = 'is_salable';
    const AREA = 'area_code';
    /**#@-*/

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @param ResourceConnection $resourceConnection
     * @param IndexNameResolverInterface $indexNameResolver
     */
    public function __construct(ResourceConnection $resourceConnection, IndexNameResolverInterface $indexNameResolver)
    {
        $this->resourceConnection = $resourceConnection;
        $this->indexNameResolver = $indexNameResolver;
        parent::__construct($resourceConnection, $indexNameResolver);
    }

    /**
     * @inheritdoc
     */
    public function create(IndexName $indexName, string $connectionName): void
    {
        $connection = $this->resourceConnection->getConnection($connectionName);
        $tableName = $this->indexNameResolver->resolveName($indexName);

        if ($connection->isTableExists($tableName)) {
            throw new StateException(__('Table %table already exits', ['table' => $tableName]));
        }

        $this->createTable($connection, $tableName);
    }

    /**
     * Create the index table
     *
     * @param AdapterInterface $connection
     * @param string $tableName
     * @return void
     */
    private function createTable(AdapterInterface $connection, string $tableName)
    {
        $table = $connection->newTable(
            $this->resourceConnection->getTableName($tableName)
        )->setComment(
            'Inventory Stock item Table'
        )->addColumn(
            self::ID,
            Table::TYPE_INTEGER,
            10,
            [
                Table::OPTION_PRIMARY => true,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_IDENTITY => true
            ],
            'ID'
        )->addColumn(
            self::SKU,
            Table::TYPE_TEXT,
            64,
            [
                Table::OPTION_PRIMARY => false,
                Table::OPTION_NULLABLE => false,
            ],
            'Sku'
        )->addColumn(
            self::QUANTITY,
            Table::TYPE_DECIMAL,
            null,
            [
                Table::OPTION_UNSIGNED => false,
                Table::OPTION_NULLABLE => false,
                Table::OPTION_DEFAULT => 0,
                Table::OPTION_PRECISION => 10,
                Table::OPTION_SCALE => 4,
            ],
            'Quantity'
        )->addColumn(
            self::AREA,
            Table::TYPE_TEXT,
            24,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Area Code'
        )->addColumn(
            self::IS_SALABLE,
            Table::TYPE_BOOLEAN,
            null,
            [
                Table::OPTION_NULLABLE => false,
            ],
            'Is Salable'
        )->addIndex(
            'index_sku_qty',
            [self::SKU, self::QUANTITY, self::AREA],
            ['type' => AdapterInterface::INDEX_TYPE_INDEX]
        );
        $connection->createTable($table);
    }
}
