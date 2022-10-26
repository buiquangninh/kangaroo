<?php

namespace Magenest\OrderManagement\Plugin;

class AddTelephoneToOrderCollection
{
    /**
     * @param $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoadWithFilter(
        $subject,
        $printQuery = false,
        $logQuery = false
    ) {
        $subject->getSelect()->joinLeft(
            ['soa' => $subject->getTable("sales_order_address")],
            "main_table.entity_id = soa.parent_id AND soa.address_type = '" . \Magento\Sales\Model\Order\Address::TYPE_SHIPPING . "'",
            [
                "telephone",
                'street' => "soa.street",
                'district_ward' => new \Zend_Db_Expr(
                    sprintf(
                    '%s, %s',
                    new \Zend_Db_Expr('soa.ward'),
                    new \Zend_Db_Expr("soa.district")
                )
                ),
                'city' => "soa.city"
            ]
        );
        $subject->getSelect()->joinLeft(
            ['som' => $subject->getTable("sales_order")],
            "main_table.entity_id = som.entity_id",
            ['som.area_code']
        );
        $subject->getSelect()->joinLeft(
            ['soi' => new \Zend_Db_Expr("(SELECT order_id, GROUP_CONCAT(sku, \" x \", CONVERT(qty_ordered, UNSIGNED) SEPARATOR \" --- \") as skus FROM `sales_order_item` WHERE parent_item_id is null GROUP BY order_id)")],
            "main_table.entity_id = soi.order_id",
            ["skus"]
        );
        $subject->getSelect()->joinLeft(
            ['sosh' => new \Zend_Db_Expr("(SELECT parent_id, comment, user_id FROM `sales_order_status_history` WHERE entity_id in (SELECT MAX(entity_id) FROM sales_order_status_history GROUP BY parent_id))")],
            "main_table.entity_id = sosh.parent_id",
            ["comment", "user_id"]
        );

        return [$printQuery, $logQuery];
    }

    public function afterGetSelectCountSql($subject, $result)
    {
        $result->joinLeft(
            ['soa' => $subject->getTable("sales_order_address")],
            "main_table.entity_id = soa.parent_id AND soa.address_type = '" . \Magento\Sales\Model\Order\Address::TYPE_SHIPPING . "'",
            [
                "telephone",
            ]
        );

        return $result;
    }
}
