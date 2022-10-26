<?php
namespace Magenest\PreOrder\Setup\Patch\Data;

use Magenest\PreOrder\Model\System\Config\Source\OrderType;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddOrderTypeAttribute implements DataPatchInterface
{
    const ORDER_TYPE_CONTAINER = "order_type_container";
    const ORDER_TYPE_ATTRIBUTE = "order_type";
    const ORDER_TYPE_DEFAULT_ATTRIBUTE = "order_type_default";

    /** @var ModuleDataSetupInterface */
    private $_setup;

    /** @var EavSetupFactory */
    private $_eavSetupFactory;

    /**
     * AddOrderHoldAttribute constructor.
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->_setup = $setup;
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->_setup]);
        $this->addOrderTypeContainer($eavSetup);
        $this->addOrderTypeAttribute($eavSetup);
    }

    /**
     * @param EavSetup $eavSetup
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addOrderTypeContainer(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ORDER_TYPE_CONTAINER,
            [
                'label' => __('Pre-Order/Backorder'),
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'type' => 'text',
                'required' => false,
                'user_defined' => false,
                'sort_order' => 200,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );
    }

    /**
     * @param EavSetup $eavSetup
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addOrderTypeAttribute(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ORDER_TYPE_ATTRIBUTE,
            [
                'type' => 'int',
                'label' => __(''),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'source'   => OrderType::class,
                'default' => '0',
                'user_defined' => false,
                'sort_order' => 205,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            self::ORDER_TYPE_DEFAULT_ATTRIBUTE,
            [
                'type' => 'int',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'label' => __('Use Config Settings'),
                'input' => 'boolean',
                'source'   => Boolean::class,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'sort_order' => 210,
                'apply_to' => ''
            ]
        );
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
}
