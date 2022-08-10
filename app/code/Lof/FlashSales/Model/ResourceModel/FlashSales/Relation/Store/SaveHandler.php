<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\FlashSales\Model\ResourceModel\FlashSales\Relation\Store;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Lof\FlashSales\Model\ResourceModel\FlashSales;
use Magento\Framework\EntityManager\MetadataPool;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var FlashSales
     */
    protected $resourceFlashSales;

    /**
     * @param MetadataPool $metadataPool
     * @param FlashSales $resourceFlashSales
     */
    public function __construct(
        MetadataPool $metadataPool,
        FlashSales $resourceFlashSales
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceFlashSales = $resourceFlashSales;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        $entityMetadata = $this->metadataPool->getMetadata(FlashSalesInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldStores = $this->resourceFlashSales->lookupStoreIds((int)$entity->getFlashsalesId());
        $newStores = (array)$entity->getStores();

        $table = $this->resourceFlashSales->getTable('lof_flashsales_store');

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = [
                $linkField . ' = ?' => (int)$entity->getData($linkField),
                'store_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    $linkField => (int)$entity->getData($linkField),
                    'store_id' => (int)$storeId,
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
