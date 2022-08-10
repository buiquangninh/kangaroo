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

use Lof\FlashSales\Model\ResourceModel\FlashSales;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;

class ReadHandler implements ExtensionInterface
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
     * @return bool|object
     * @throws LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getFlashSalesId()) {
            $stores = $this->resourceFlashSales->lookupStoreIds((int)$entity->getFlashSalesId());
            $entity->setData('store_id', $stores);
            $entity->setData('stores', $stores);
        }
        return $entity;
    }
}
