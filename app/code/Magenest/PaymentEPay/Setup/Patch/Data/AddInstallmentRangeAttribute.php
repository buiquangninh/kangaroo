<?php


namespace Magenest\PaymentEPay\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Backend\JsonEncoded;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddInstallmentRangeAttribute implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $_setup;

    /** @var EavSetupFactory */
    private $_eavSetupFactory;

    /**
     * AddExcludedDatesAttribute constructor.
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
     * @return AddBookingTypeAttribute|void
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->_setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'installment_options',
            [
                'group' => 'General',
                'label' => 'Installment Options',
                'global' => Attribute::SCOPE_GLOBAL,
                'visible' => true,
                'backend' => JsonEncoded::class,
                'type' => 'text',
                'required' => false,
                'user_defined' => false,
                'sort_order' => 99,
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
            'installment_shipping_fee',
            [
                'group' => 'General',
                'type' => 'decimal',
                'input' => 'price',
                'backend' => Price::class,
                'required' => false,
                'user_defined' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
                'label' => 'Installment Shipping Fee',
                'global' => Attribute::SCOPE_GLOBAL,
                'frontend_class' => 'validate-number validate-zero-or-greater',
                'visible' => true,
                'sort_order' => 100,
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
