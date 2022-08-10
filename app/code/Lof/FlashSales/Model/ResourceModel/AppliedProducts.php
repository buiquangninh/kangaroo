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
declare(strict_types=1);

namespace Lof\FlashSales\Model\ResourceModel;

use Magento\Bundle\Model\Product\Price as BundlePriceType;

class AppliedProducts extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_flashsales_appliedproducts', 'entity_id');
    }

    /**
     *
     * @param int $productId
     * @return bool
     */
    public function isFixedPriceType($productId)
    {
        if ($type = $this->getPriceType($productId)) {
            return $type["value"] == BundlePriceType::PRICE_TYPE_FIXED;
        }
        return !$type;
    }

    /**
     *
     * @param int $productId
     * @return int
     */
    private function getPriceType($productId)
    {
        try {
            $connection = $this->getConnection();
            $subSelect = $connection->select()->from(
                'eav_attribute',
                'attribute_id'
            )->where('attribute_code = ?', 'price_type');

            $select = $connection->select()->from(
                'catalog_product_entity_int',
                'value'
            )->where('entity_id = ?', $productId)
                ->where('attribute_id = (?)', $subSelect);

            return $connection->fetchRow($select);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $flashSalesId
     * @param $productId
     * @return bool|string
     */
    public function hasProduct($flashSalesId, $productId)
    {
        try {
            $connection = $this->getConnection();
            $select = $connection->select()->from($this->getMainTable());
            $select->where(sprintf("flashsales_id = %s AND product_id = %s", $flashSalesId, $productId))
                ->limit(1);
            return $connection->fetchOne($select);
        } catch (\Exception $e) {
            return false;
        }
    }
}
