<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Model\ResourceModel\AffiliateCountClick;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'affiliatecountclick_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Magenest\AffiliateClickCount\Model\AffiliateCountClick::class,
            \Magenest\AffiliateClickCount\Model\ResourceModel\AffiliateCountClick::class
        );
    }
}
