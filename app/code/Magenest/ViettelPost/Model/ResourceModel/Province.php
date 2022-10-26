<?php
namespace Magenest\ViettelPost\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magenest\ViettelPost\Model\Province as ProvinceModel;

class Province extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ProvinceModel::TABLE_NAME, ProvinceModel::PRIMARY_KEY);
    }

    /**
     * @param $code
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function searchProvinceCode($code)
    {
        $select = $this->getConnection()->select()
            ->from(["main_table" => $this->getMainTable()], ['province_id'])
            ->where("`province_code` = \"$code\"");
        return $this->getConnection()->fetchOne($select);
    }
}
