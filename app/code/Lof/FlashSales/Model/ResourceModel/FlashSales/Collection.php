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

namespace Lof\FlashSales\Model\ResourceModel\FlashSales;

use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Lof\FlashSales\Model\ResourceModel\AbstractCollection;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'flashsales_id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'lof_flashsales_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'flashsales_collection';

    /**
     * Whether collection should dispose of the closed events
     *
     * @var bool
     */
    protected $_skipClosed = false;

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $entityMetadata = $this->metadataPool->getMetadata(FlashSalesInterface::class);

        $this->performAfterLoad('lof_flashsales_store', $entityMetadata->getLinkField());

        return parent::_afterLoad();
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Lof\FlashSales\Model\FlashSales::class,
            \Lof\FlashSales\Model\ResourceModel\FlashSales::class
        );
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['flashsales_id'] = 'main_table.flashsales_id';
    }

    /**
     * Add filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->performAddStoreFilter($store, $withAdmin);

        return $this;
    }

    /**
     * Add filter for visible events on frontend
     *
     * @return \Lof\FlashSales\Model\ResourceModel\FlashSales\Collection
     */
    public function addVisibilityFilter()
    {
        $statusMapper = \Lof\FlashSales\Model\FlashSales::$statusMapper;
        $this->_skipClosed = true;
        $this->addFieldToFilter('status', ['nin' => $statusMapper[\Lof\FlashSales\Model\FlashSales::STATUS_ENDED]]);
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $entityMetadata = $this->metadataPool->getMetadata(FlashSalesInterface::class);
        $this->joinStoreRelationTable('lof_flashsales_store', $entityMetadata->getLinkField());
    }
}
