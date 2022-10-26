<?php

namespace Magenest\Directory\Setup\Patch\Data;

use FontLib\Table\Type\name;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magenest\Directory\Helper\ConvertVnToNonVn;

class UpdateNonVneseName implements DataPatchInterface
{
    protected $moduleDataSetup;
    protected $convert;
    protected $helperData;

    /**
     * @param ConvertVnToNonVn $convert
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \Magenest\ViettelPost\Helper\Data $helperData
     */
    public function __construct(
        ConvertVnToNonVn    $convert,
        ModuleDataSetupInterface $moduleDataSetup,
        \Magenest\ViettelPost\Helper\Data $helperData
    )
    {
        $this->convert = $convert;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->helperData = $helperData;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
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
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();

        $table = $connection->getTableName('directory_city_entity');

        $select = $connection->select()
            ->from($table);
        $rows = $connection->fetchAll($select);
        foreach ($rows as $row){
            $name = $this->convert->stripVN($row['name']);
            $connection->update(
                $table,
                [
                    'nonvnese_name' => $name,
                ],
                ['city_id = ?' => $row['city_id']]
            );
        }
        $this->moduleDataSetup->endSetup();
    }
}
