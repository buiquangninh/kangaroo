<?php
namespace Magenest\PreOrder\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddButtonLabelAttribute implements DataPatchInterface
{
    const BUTTON_LABEL_CONTAINER = "add_to_cart_label_container";
    const BUTTON_LABEL_ATTRIBUTE = "add_to_cart_label";
    const BUTTON_LABEL_DEFAULT_ATTRIBUTE = "add_to_cart_label_default";

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
        $this->addButtonLabelContainer($eavSetup);
        $this->addButtonLabelAttribute($eavSetup);
    }

    /**
     * @param EavSetup $eavSetup
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addButtonLabelContainer(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::BUTTON_LABEL_CONTAINER,
            [
                'label' => __('Add to Cart Button Label'),
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'type' => 'text',
                'required' => false,
                'user_defined' => false,
                'sort_order' => 215,
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
    private function addButtonLabelAttribute(EavSetup $eavSetup)
    {
        $eavSetup->addAttribute(
            Product::ENTITY,
            self::BUTTON_LABEL_ATTRIBUTE,
            [
                'type' => 'varchar',
                'label' => __(''),
                'input' => 'select',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'sort_order' => 220,
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
            self::BUTTON_LABEL_DEFAULT_ATTRIBUTE,
            [
                'type' => 'int',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'label' => __('Use Config Settings'),
                'input' => 'boolean',
                'source' => Boolean::class,
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
                'sort_order' => 225,
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
