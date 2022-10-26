<?php

namespace Magenest\Directory\Setup\Patch\Data;

use FontLib\Table\Type\name;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;


class UpdateAreaForDistrict implements DataPatchInterface
{
    protected $moduleDataSetup;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magenest\ViettelPost\Helper\Data $helperData
    )
    {
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
        $mienbac = '24,40,36,44,33,30,22,59,48,63,38,32,62,58,2,6,45,8,19,49,52,35,39,61';
        $mientrung = '20,28,53,60,21,4,3,46,23,27,31,5,54,9,15,41,47,16,26';
        $miennam = '1,13,11,7,18,34,10,56,43,37,25,51,55,14,29,57,17,50,12';
        $select = $connection->select()
            ->from($table,['city_id']);
        $connection->update(
            $table,
            [
                'area' => 'mien_bac',
            ],
            ['city_id IN ('. $mienbac . ')']
        );
        $connection->update(
            $table,
            [
                'area' => 'mien_trung',
            ],
            ['city_id IN ('. $mientrung . ')']
        );
        $connection->update(
        $table,
        [
            'area' => 'mien_nam',
        ],
            ['city_id IN ('. $miennam .')']
        );
        $this->moduleDataSetup->endSetup();
    }
}
