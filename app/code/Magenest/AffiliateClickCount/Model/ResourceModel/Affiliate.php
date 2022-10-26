<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Affiliate extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('magenest_affiliateclickcount_affiliate', 'affiliate_id');
    }
}

