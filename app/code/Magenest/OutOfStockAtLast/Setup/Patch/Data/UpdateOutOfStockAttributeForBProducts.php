<?php

namespace Magenest\OutOfStockAtLast\Setup\Patch\Data;


use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateOutOfStockAttributeForBProducts implements DataPatchInterface
{
    public function __construct(
        EavSetupFactory          $eavSetupFactory,
        ModuleDataSetupInterface $setup
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup = $setup;
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->setup]);
        $eavSetup->updateAttribute(Product::ENTITY, 'out_of_stock_at_last_mien_bac','is_visible',false);
        $eavSetup->updateAttribute(Product::ENTITY, 'out_of_stock_at_last_mien_trung','is_visible',false);
        $eavSetup->updateAttribute(Product::ENTITY, 'out_of_stock_at_last_mien_nam','is_visible',false);
        $eavSetup->updateAttribute(Product::ENTITY, 'out_of_stock_at_last_mien_trung','backend_model','');
        $eavSetup->updateAttribute(Product::ENTITY, 'out_of_stock_at_last_mien_nam','backend_model','');
    }

    /**`
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}


