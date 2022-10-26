<?php

namespace Magenest\CustomSource\Indexer;

use Magenest\CustomSource\Model\Source\Area\Options;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Indexer\SaveHandler\Batch;
use Magento\InventoryIndexer\Indexer\IndexHandler;
use Magento\InventoryIndexer\Indexer\IndexStructure;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexName;
use Magento\InventoryMultiDimensionalIndexerApi\Model\IndexNameResolverInterface;

class IndexHandlerWithArea extends IndexHandler
{
    /**
     * @var IndexNameResolverInterface
     */
    private $indexNameResolver;

    /**
     * @var Batch
     */
    private $batch;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @var DeploymentConfig|null
     */
    private $deploymentConfig;

    /**
     * @var Options
     */
    private $areaOptions;

    /**
     * Deployment config path
     *
     * @var string
     */
    private const DEPLOYMENT_CONFIG_INDEXER_BATCHES = 'indexer/batch_size/';

    private $handleMissingStockPerSku = [];

    /**
     * IndexHandlerWithArea constructor.
     * @param IndexNameResolverInterface $indexNameResolver
     * @param Batch $batch
     * @param ResourceConnection $resourceConnection
     * @param int $batchSize
     * @param Options $options
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        IndexNameResolverInterface $indexNameResolver,
        Batch $batch,
        ResourceConnection $resourceConnection,
        int $batchSize,
        Options $options,
        ?DeploymentConfig $deploymentConfig = null
    ) {
        $this->indexNameResolver = $indexNameResolver;
        $this->batch = $batch;
        $this->resourceConnection = $resourceConnection;
        $this->batchSize = $batchSize;
        $this->areaOptions = $options;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
        parent::__construct($indexNameResolver, $batch, $resourceConnection, $batchSize, $deploymentConfig);
    }

    /**
     * @inheritdoc
     */
    public function saveIndex(IndexName $indexName, \Traversable $documents, string $connectionName): void
    {
        $connection = $this->resourceConnection->getConnection($connectionName);
        $tableName = $this->indexNameResolver->resolveName($indexName);

        $columns = [IndexStructure::SKU, IndexStructure::QUANTITY, IndexStructure::IS_SALABLE, 'area_code'];

        $this->batchSize = $this->deploymentConfig->get(
                self::DEPLOYMENT_CONFIG_INDEXER_BATCHES . InventoryIndexer::INDEXER_ID . '/' . 'default'
            ) ?? $this->batchSize;

        $options = $this->areaOptions->getValueArray();


        foreach ($this->batch->getItems($documents, $this->batchSize) as $batchDocuments) {
            $connection->insertOnDuplicate($tableName, $batchDocuments, $columns);
            foreach ($batchDocuments as $document) {
                if (!isset($this->handleMissingStockPerSku[$document['sku']])) {
                    $this->handleMissingStockPerSku[$document['sku']] = [];
                }
                $this->handleMissingStockPerSku[$document['sku']][] = $document['area_code'];
                if (count($this->handleMissingStockPerSku[$document['sku']]) == 3) {
                    unset($this->handleMissingStockPerSku[$document['sku']]);
                }
            }
        }
        $missingData = [];
        foreach ($this->handleMissingStockPerSku as $sku => $addedArea) {
            $areaCodes = array_diff($options, $addedArea);
            foreach ($areaCodes as $areaCode) {
                $missingData[] = [
                    'sku' => $sku,
                    'quantity' => 0,
                    'is_salable' => 0,
                    'area_code' => $areaCode
                ];
            }
        }
        $this->handleMissingStockPerSku = [];
        foreach ($this->batch->getItems(new \ArrayIterator($missingData), $this->batchSize) as $batchDocuments) {
            $connection->insertOnDuplicate($tableName, $batchDocuments, $columns);
        }
    }
}
