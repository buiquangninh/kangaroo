<?php
namespace Magenest\RewardPoints\Setup\Patch\Schema;

use Magenest\RewardPoints\Api\Data\MembershipInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class MembershipUpdate implements \Magento\Framework\Setup\Patch\SchemaPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [Membership::class];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        if ($this->moduleDataSetup->tableExists(MembershipInterface::TABLE_NAME)) {
            $this->moduleDataSetup->getConnection()->addColumn(
                $this->moduleDataSetup->getTable(MembershipInterface::TABLE_NAME),
                MembershipInterface::COLOR_CODE,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => "Color code for membership tier"
                ]
            );
        }
    }
}
