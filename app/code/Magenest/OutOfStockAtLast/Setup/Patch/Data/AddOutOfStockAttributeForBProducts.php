<?php

namespace Magenest\OutOfStockAtLast\Setup\Patch\Data;


use Magenest\OutOfStockAtLast\Model\Product\Attribute\Backend\Stock;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddOutOfStockAttributeForBProducts implements DataPatchInterface
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
        $eavSetup->addAttribute(Product::ENTITY, 'out_of_stock_at_last_mien_bac', [
            'type' => 'static',
            'input' => 'text',
            'label' => 'Out of stock at last mien bac',
            'default' => 0,
            'backend' => Stock::class,
            'visible' => false,
            'required' => false,
            'user_defined' => false,
            'sort_order' => 200,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'used_in_product_listing' => false,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'used_for_sort_by' => false,
        ]);
        $eavSetup->addAttribute(Product::ENTITY, 'out_of_stock_at_last_mien_trung', [
            'type' => 'static',
            'input' => 'text',
            'label' => 'Out of stock at last mien trung',
            'default' => 0,
            'backend' => Stock::class,
            'visible' => false,
            'required' => false,
            'user_defined' => false,
            'sort_order' => 200,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'used_in_product_listing' => false,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'used_for_sort_by' => false,
        ]);
        $eavSetup->addAttribute(Product::ENTITY, 'out_of_stock_at_last_mien_nam', [
            'type' => 'static',
            'input' => 'text',
            'label' => 'Out of stock at last mien nam',
            'default' => 0,
            'system' => false,
            'backend' => Stock::class,
            'visible' => false,
            'required' => false,
            'user_defined' => false,
            'sort_order' => 200,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'visible_in_advanced_search' => false,
            'used_in_product_listing' => false,
            'is_visible_in_grid' => true,
            'is_filterable_in_grid' => true,
            'used_for_sort_by' => false,
        ]);
    }

    /**
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


