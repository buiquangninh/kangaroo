<?php

namespace Magenest\MomoPay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QueryStatus extends AbstractDb
{

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('momo_query_status', 'entity_id');
        $this->_useIsObjectNew = true;
    }

    /**
     * @param $object
     * @param $incrementId
     * @return QueryStatus
     */
    public function loadByIncrementId($object, $incrementId)
    {
        return $this->load($object, $incrementId, 'order_id');
    }
}
