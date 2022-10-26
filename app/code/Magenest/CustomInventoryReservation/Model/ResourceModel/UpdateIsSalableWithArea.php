<?php

namespace Magenest\CustomInventoryReservation\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Model\ResourceModel\UpdateIsSalable;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexName;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameResolverInterface;

class UpdateIsSalableWithArea extends UpdateIsSalable
{
    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param IndexNameResolverInterface $indexNameResolver
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        IndexNameResolverInterface $indexNameResolver,
        ResourceConnection $resourceConnection
    ) {
        $this->indexNameResolver = $indexNameResolver;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Update index salability data.
     *
     * @param IndexName $indexName
     * @param array $dataForUpdate = ['sku' => bool]
     * @param string $connectionName
     * @param string $area
     *
     * @return void
     */
    public function execute(IndexName $indexName, array $dataForUpdate, string $connectionName, string $area = ''): void
    {
        $connection = $this->resourceConnection->getConnection($connectionName);
        $tableName = $this->indexNameResolver->resolveName($indexName);

        foreach ($dataForUpdate as $sku => $isSalable) {
            $condition =  "sku = '{$sku}' ";
            if ($area) {
                $condition .= " AND area_code = '{$area}'";
            }
            $connection->update($tableName, [IndexStructure::IS_SALABLE => $isSalable], $condition);
        }
    }
}
