<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * User: leo
 * Date: 25/06/2022
 * Time: 15:31
 */
declare(strict_types=1);

namespace Lof\FlashSales\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class AddPositionFlashSales
 */
class AddPositionFlashSales implements SchemaPatchInterface
{
    const TABLE = 'lof_flashsales_appliedproducts';
    const COLUMN = 'position';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SchemaSetupInterface
     */
    private $setup;


    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param SchemaSetupInterface $setup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        SchemaSetupInterface $setup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->setup = $setup;
    }

    /**
     * @ingeritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable(self::TABLE),
            self::COLUMN,
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'default' => 0,
                'comment'  => 'Position of product flash sales',
            ]
        );

        $this->setup->getConnection()->addIndex(
            $this->setup->getTable(self::TABLE),
            $this->setup->getIdxName(
                self::TABLE,
                self::COLUMN
            ),
            self::COLUMN,
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @ingeritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @ingeritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
