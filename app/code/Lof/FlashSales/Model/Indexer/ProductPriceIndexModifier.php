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

namespace Lof\FlashSales\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Lof\FlashSales\Model\ResourceModel\ProductPrice;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

class ProductPriceIndexModifier implements PriceModifierInterface
{

    /**
     * @var ProductPrice
     */
    private $productPriceResourceModel;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @param ProductPrice $priceResourceModel
     * @param ResourceConnection $resourceConnection
     * @param string $connectionName
     */
    public function __construct(
        ProductPrice $priceResourceModel,
        ResourceConnection $resourceConnection,
        $connectionName = 'indexer'
    ) {
        $this->productPriceResourceModel = $priceResourceModel;
        $this->resourceConnection = $resourceConnection ?: ObjectManager::getInstance()->get(ResourceConnection::class);
        $this->connectionName = $connectionName;
    }

    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []): void
    {
        $connection = $this->resourceConnection->getConnection($this->connectionName);

        $select = $connection->select();

        $select->join(
            ['loffs_pp' => $this->productPriceResourceModel->getMainTable()],
            'loffs_pp.product_id = i.' . $priceTable->getEntityField(),
            []
        );

        if ($entityIds) {
            $select->where('i.entity_id IN (?)', $entityIds, \Zend_Db::INT_TYPE);
        }

        $finalPrice = $priceTable->getFinalPriceField();
        $finalPriceExpr = $select->getConnection()->getLeastSql([
            $priceTable->getFinalPriceField(),
            $select->getConnection()->getIfNullSql('loffs_pp.flash_sale_price', 'i.' . $finalPrice),
        ]);
        $minPrice = $priceTable->getMinPriceField();
        $minPriceExpr = $select->getConnection()->getLeastSql([
            $priceTable->getMinPriceField(),
            $select->getConnection()->getIfNullSql('loffs_pp.flash_sale_price', 'i.' . $minPrice),
        ]);
        $select->columns([
            $finalPrice => $finalPriceExpr,
            $minPrice => $minPriceExpr,
        ]);

        $query = $connection->updateFromSelect($select, ['i' => $priceTable->getTableName()]);
        $connection->query($query);
    }
}
