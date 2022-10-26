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
namespace Magenest\AffiliateMultiLevel\Model\ResourceModel\Account;

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
        $this->joinAffiliateAccount()->join(
            ['maa' => 'magenest_affiliate_account'],
            'main_table.customer_id = maa.customer_id',
            []
        )->getSubTotal()->getTotalCommission()->addOrdersCount()->addAttributeToFilter(
            'main_table.created_at',
            ['from' => $fromDate, 'to' => $toDate, 'datetime' => true]
        );

        return $this;
    }

    public function addOrdersCount()
    {
        $this->getSelect()->columns(['orders_count' => 'COUNT(main_table.entity_id)']);

        return $this;
    }

    public function getTotalCommission()
    {
        $this->getSelect()->columns(['total_commission' => "maa.total_commission"]);

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

    public function getSubTotal(){
        $this->getSelect()->columns(['total_sales' => "SUM(subtotal)"])->order('total_sales DESC')->group('main_table.customer_id');

        return $this;
    }

    public function joinAffiliateAccount($alias = 'name')
    {
        $this->getSelect()->columns([$alias => 'maa.customer_email']);

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
            $this->addSumAvgTotals(1);
        } else {
            $this->addSumAvgTotals();
        }

        return $this;
    }
}
