<?php

namespace Magenest\RewardPoints\Setup\Patch\Data;

use Magenest\RewardPoints\Model\RuleFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;


class UpdateMappingTransactionImage implements DataPatchInterface
{
    protected $moduleDataSetup;

    public function __construct(
        RuleFactory                       $rule,
        ModuleDataSetupInterface          $moduleDataSetup
    )
    {
        $this->rule = $rule;
        $this->moduleDataSetup = $moduleDataSetup;
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

        $table = $connection->getTableName('magenest_rewardpoints_transaction');

        $select = $connection->select()
            ->from($table);
        $result = $connection->fetchAll($select);
        foreach ($result as $item) {
            $rule = $this->rule->create()->load($item['rule_id'], 'id');
            $connection->update(
                $table,
                [
                    'rewardpoint_img' => $rule->getData('rewardpoint_img') ?? '',
                ],
                ['rule_id = ?' => $rule->getId()]
            );
        }
        $this->moduleDataSetup->endSetup();

    }

}


