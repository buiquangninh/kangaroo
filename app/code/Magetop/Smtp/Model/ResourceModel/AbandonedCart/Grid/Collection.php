<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Smtp\Model\ResourceModel\AbandonedCart\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Class Collection
 * @package Magetop\Smtp\Model\ResourceModel\AbandonedCart\Grid
 */
class Collection extends SearchResult
{
    /**
     * @return $this|SearchResult|void
     */
    public function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->where('main_table.is_active = ?', 1)
            ->where('main_table.customer_email != ?', null)
            ->where('main_table.items_count != ?', 0);

        return $this;
    }
}
