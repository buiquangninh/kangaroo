<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\FlashSales\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {

        $installer = $setup;
        $installer->startSetup();

        $lof_flashsale_events_table = $installer->getConnection()
            ->newTable($installer->getTable('lof_flashsales_events'))
            ->addColumn(
                'flashsales_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Flashsales ID'
            )
            ->addColumn(
                'event_name',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Event Name'
            )->addColumn(
                'from_date',
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => false
                ],
                'From'
            )->addColumn(
                'to_date',
                Table::TYPE_DATETIME,
                null,
                [
                    'nullable' => false
                ],
                'To'
            )->addColumn(
                'description',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Description'
            )->addColumn(
                'category_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'unsigned' => true,
                    'nullable' => true,
                    'identity' => false,
                    'default' => null
                ],
                'Category Id'
            )->addColumn(
                'thumbnail_image',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Thumbnail Image'
            )->addColumn(
                'header_banner_image',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Header Banner Image'
            )->addColumn(
                'restricted_landing_page',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => true,
                    'default' => null
                ],
                'Restricted Landing Page'
            )->addColumn(
                'is_private_sale',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'default' => '0'
                ],
                'Is Private Sales'
            )
            ->addColumn(
                'is_assign_category',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'default' => '0'
                ],
                'Is Assign Category'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => true,
                    'default' => '0'
                ],
                'Priority'
            )->addColumn(
                'conditions_serialized',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Conditions Serialized'
            )->addColumn(
                'is_active',
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => '0',
                    'nullable' => false,
                    'unsigned' => true
                ],
                'Is Active'
            )->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => '0',
                    'identity' => false,
                    'nullable' => false,
                    'unsigned' => true
                ],
                'Status'
            )->addColumn(
                'is_default_private_config',
                Table::TYPE_SMALLINT,
                null,
                [
                    'default' => '1',
                    'nullable' => false,
                    'unsigned' => true
                ],
                'Is Default Private Config'
            )->addColumn(
                'grant_event_view',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Grant Event View'
            )->addColumn(
                'display_product_mode',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Display Product Mode'
            )->addColumn(
                'grant_event_product_price',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Grant Event Product Price'
            )->addColumn(
                'grant_checkout_items',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Grant Checkout Items'
            )->addColumn(
                'display_cart_mode',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Display Cart Mode'
            ) ->addColumn(
                'cart_button_title',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Cart Button Title'
            ) ->addColumn(
                'message_hidden_add_to_cart',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Message Hidden Add To Cart'
            )->addColumn(
                'grant_event_view_groups',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Grant Event View Groups'
            )->addColumn(
                'grant_checkout_items_groups',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Grant Checkout Items Groups'
            )->addColumn(
                'grant_event_product_price_groups',
                Table::TYPE_TEXT,
                null,
                ['default' => '', 'nullable' => false],
                'Grant Event Product Price Groups'
            ) ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Update Time'
            )->addIndex(
                $setup->getIdxName('lof_flashsales_events', 'event_name', AdapterInterface::INDEX_TYPE_FULLTEXT),
                'event_name',
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            );

        $lof_flashsale_appliedproducts_table = $installer->getConnection()
            ->newTable($installer->getTable('lof_flashsales_appliedproducts'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Entity ID'
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false
                ],
                'Product Name'
            )->addColumn(
                'flashsales_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Flashsales ID'
            )->addColumn(
                'price_type',
                Table::TYPE_SMALLINT,
                null,
                [
                    'nullable' => false
                ],
                'Price Type'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Product ID'
            )->addColumn(
                'type_id',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ],
                'Type Id'
            )->addColumn(
                'original_price',
                Table::TYPE_DECIMAL,
                '20,6',
                [
                    'default' => null,
                    'unsigned' => true
                ],
                'Original Price'
            )->addColumn(
                'flash_sale_price',
                Table::TYPE_DECIMAL,
                '20,6',
                [
                    'default' => null,
                    'unsigned' => true
                ],
                'Flash Sale Price'
            )->addColumn(
                'sku',
                Table::TYPE_TEXT,
                255,
                [
                    'nullable' => false,
                ],
                'Sku'
            )->addColumn(
                'qty_limit',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true
                ],
                'Quantity Limit'
            )->addColumn(
                'discount_amount',
                Table::TYPE_DECIMAL,
                '10,2',
                [
                    'unsigned' => true
                ],
                'Discount Amount'
            )->addIndex(
                $setup->getIdxName(
                    'lof_flashsales_appliedproducts',
                    [
                        'flashsales_id',
                        'product_id',
                        'name',
                        'type_id',
                        'original_price',
                        'sku',
                        'discount_amount',
                        'qty_limit',
                    ],
                    true
                ),
                [
                    'flashsales_id',
                    'product_id',
                    'name',
                    'type_id',
                    'original_price',
                    'sku',
                    'discount_amount',
                    'qty_limit',
                ],
                ['type' => 'unique']
            )->addIndex(
                $setup->getIdxName('lof_flashsales_appliedproducts', ['flashsales_id']),
                ['flashsales_id']
            )->addIndex(
                $setup->getIdxName('lof_flashsales_appliedproducts', ['name']),
                ['name']
            )->addIndex(
                $setup->getIdxName('lof_flashsales_appliedproducts', ['type_id']),
                ['type_id']
            )->addIndex(
                $setup->getIdxName('lof_flashsales_appliedproducts', ['original_price']),
                ['original_price']
            )->addIndex(
                $setup->getIdxName('lof_flashsales_appliedproducts', ['sku']),
                ['sku']
            )->addIndex(
                $setup->getIdxName('lof_flashsales_appliedproducts', ['discount_amount']),
                ['discount_amount']
            )->addIndex(
                $setup->getIdxName('lof_flashsales_appliedproducts', ['product_id']),
                ['product_id']
            )->addForeignKey(
                $installer->getFkName(
                    'lof_flashsales_appliedproducts',
                    'flashsales_id',
                    'lof_flashsales_events',
                    'flashsales_id'
                ),
                'flashsales_id',
                $installer->getTable('lof_flashsales_events'),
                'flashsales_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'lof_flashsales_appliedproducts',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );

        $lof_flashsale_productprice_table = $installer->getConnection()
            ->newTable($installer->getTable('lof_flashsales_productprice'))
            ->addColumn(
                'index_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Index ID'
            )->addColumn(
                'flashsales_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Flashsales ID'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                ],
                'Product ID'
            )->addColumn(
                'store_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Store Id'
            )->addColumn(
                'flash_sale_price',
                Table::TYPE_DECIMAL,
                '10,2',
                [
                    'unsigned' => true
                ],
                'Flash Sale Price'
            )->addForeignKey(
                $installer->getFkName(
                    'lof_flashsales_productprice',
                    'flashsales_id',
                    'lof_flashsales_appliedproducts',
                    'flashsales_id'
                ),
                'flashsales_id',
                $installer->getTable('lof_flashsales_appliedproducts'),
                'flashsales_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'lof_flashsales_productprice',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );

        $lof_flashsale_store_table = $installer->getConnection()
            ->newTable($installer->getTable('lof_flashsales_store'))
            ->addColumn(
                'flashsales_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Flashsales ID'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                [
                    'identity' => false,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Store ID'
            )->addForeignKey(
                $installer->getFkName(
                    'lof_flashsales_store',
                    'flashsales_id',
                    'lof_flashsales_events',
                    'flashsales_id'
                ),
                'flashsales_id',
                $installer->getTable('lof_flashsales_events'),
                'flashsales_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'lof_flashsales_store',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->addIndex(
                $setup->getIdxName('lof_flashsales_store', ['store_id']),
                ['store_id']
            );

        $installer->getConnection()->createTable($lof_flashsale_events_table);
        $installer->getConnection()->createTable($lof_flashsale_store_table);
        $installer->getConnection()->createTable($lof_flashsale_productprice_table);
        $installer->getConnection()->createTable($lof_flashsale_appliedproducts_table);
        $installer->endSetup();
    }
}
