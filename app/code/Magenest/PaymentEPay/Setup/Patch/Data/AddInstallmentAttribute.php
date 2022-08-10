<?php
namespace Magenest\PaymentEPay\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;

class AddInstallmentAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetup;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory $eavSetup
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetup
    ) {
        $this->setup    = $setup;
        $this->eavSetup = $eavSetup;
    }

    /**
     * Install deposit type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function apply()
    {
        $eavSetup  = $this->eavSetup->create(['setup' => $this->setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'is_allow_installment',
            [
                'group' => 'General',
                'type' => 'int',
                'sort_order' => 98,
                'label' => 'Is Allow Installment',
                'input' => 'boolean',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'default' => false,
                'unique' => false,
                'apply_to'=>''
            ]
        );
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
