<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Customers by orders Report collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magenest\CustomAdvancedReports\Model\ResourceModel\SalesPerson\Orders;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection
{
    /**
     * Join fields
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    protected function _joinFields($fromDate = '', $toDate = '')
    {
        $this->joinAssignSalesPerson()->join(
            ['admin_user' => 'admin_user'],
            'main_table.assigned_to = admin_user.user_id',
            []
        )->getIncrementsId()->groupBySalesOrder()->addOrdersCount()->addAttributeToFilter(
            'created_at',
            ['from' => $fromDate, 'to' => $toDate, 'datetime' => true]
        );

        return $this;
    }

    /**
     * Set date range
     *
     * @param string $fromDate
     * @param string $toDate
     * @return $this
     */
    public function setDateRange($fromDate, $toDate)
    {
        $this->_reset()->_joinFields($fromDate, $toDate);
        return $this;
    }

    public function getIncrementsId(){
        $this->getSelect()->columns(['increment_id' => "GROUP_CONCAT(increment_id SEPARATOR ', ')"]);

        return $this;
    }

    public function joinAssignSalesPerson($alias = 'name')
    {
        $fields = ['admin_user.firstname', 'admin_user.lastname'];
        $fieldConcat = $this->getConnection()->getConcatSql($fields, ' ');
        $this->getSelect()->columns([$alias => $fieldConcat]);

        return $this;
    }

    public function groupBySalesOrder()
    {
        $this->getSelect()->where('main_table.assigned_to IS NOT NULL')->group( 'main_table.assigned_to');
        return $this;
    }

    /**
     * Set store filter to collection
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->addAttributeToFilter('store_id', ['in' => (array)$storeIds]);
            $this->addSumAvgTotals(1)->orderByOrdersCount();
        } else {
            $this->addSumAvgTotals()->orderByOrdersCount();
        }

        return $this;
    }
}
