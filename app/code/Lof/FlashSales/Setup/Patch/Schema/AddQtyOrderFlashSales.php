<?php

namespace Lof\FlashSales\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class AddQtyOrderFlashSales
 */
class AddQtyOrderFlashSales implements SchemaPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @ingeritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('lof_flashsales_appliedproducts'),
            'qty_ordered',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'default' => 0,
                'comment'  => 'Qty Ordered of product flash sales',
            ]
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
