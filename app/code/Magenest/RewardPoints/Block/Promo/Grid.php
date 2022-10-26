<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\RewardPoints\Block\Promo;

/**
 * Coupon codes grid
 *
 * @api
 * @since 100.0.2
 */
class Grid extends \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Coupons\Grid
{
    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->addColumnAfter(
            'customer_id',
            [
                'header' => __('Customer id'),
                'index' => 'customer_id',
                'type' => 'text',
                'default' => '',
                'width' => '30',
                'align' => 'center',
                'renderer' =>
                    \Magenest\RewardPoints\Block\Promo\Used::class,
            ],
            'created_at'
        );
    }


}
