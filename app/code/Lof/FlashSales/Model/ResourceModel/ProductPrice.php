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

use Lof\FlashSales\Api\Data\ProductPriceInterface;
use Magento\Framework\Exception\LocalizedException;

class ProductPrice extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_flashsales_productprice', 'index_id');
    }

    /**
     * @param $flashSalesIds
     * @throws LocalizedException
     */
    public function cleanByFlashSalesIds($flashSalesIds)
    {
        $query = $this->getConnection()->deleteFromSelect(
            $this->getConnection()
                ->select()
                ->from($this->getMainTable(), ProductPriceInterface::FLASHSALES_ID)
                ->where(ProductPriceInterface::FLASHSALES_ID . ' IN (?)', $flashSalesIds),
            $this->getMainTable()
        );

        $this->getConnection()->query($query);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function cleanAllIndex()
    {
        $this->getConnection()->delete($this->getMainTable());
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     * @throws LocalizedException
     */
    public function insertIndexData(array $data)
    {
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data);
        return $this;
    }
}
