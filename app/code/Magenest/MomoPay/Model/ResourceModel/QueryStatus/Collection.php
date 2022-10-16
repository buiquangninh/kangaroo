<?php

namespace Magenest\MomoPay\Model\ResourceModel\QueryStatus;

use Magenest\MomoPay\Model\QueryStatus as Model;
use Magenest\MomoPay\Model\ResourceModel\QueryStatus as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
