<?php

namespace Magenest\MomoPay\Model;

use Magenest\MomoPay\Model\ResourceModel\QueryStatus as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class QueryStatus extends AbstractModel
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
