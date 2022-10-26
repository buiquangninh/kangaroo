<?php

namespace Magenest\AdditionalRegisterForm\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddAttributeForCustomer implements DataPatchInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * AccountPurposeCustomerAttribute constructor.
     * @param ModuleDataSetupInterface $setup
     * @param Config $eavConfig
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Config                   $eavConfig,
        CustomerSetupFactory     $customerSetupFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->setup = $setup;
        $this->eavConfig = $eavConfig;
    }

    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerSetup->getDefaultAttributeSetId($customerEntity->getEntityTypeId());
        $attributeGroup = $customerSetup->getDefaultAttributeGroupId($customerEntity->getEntityTypeId(), $attributeSetId);
        $customerSetup->addAttribute(Customer::ENTITY, 'city_id', [
            'type' => 'varchar',
            'input' => 'select',
            'source' => \Magenest\AdditionalRegisterForm\Model\Config\Source\City::class,
            'label' => 'Customer City',
            'required' => true,
            'default' => 0,
            'visible' => true,
            'user_defined' => true,
            'system' => false,
            'is_visible_in_grid' => true,
            'is_used_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => true,
            'position' => 300
        ]);
        $customerSetup->addAttribute(Customer::ENTITY, 'district_id', [
            'type' => 'varchar',
            'input' => 'select',
            'source' => \Magenest\AdditionalRegisterForm\Model\Config\Source\District::class,
            'label' => 'Customer District',
            'required' => true,
            'default' => 0,
            'visible' => true,
            'user_defined' => true,
            'system' => false,
            'is_visible_in_grid' => true,
            'is_used_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true,
            'position' => 300
        ]);
        $customerSetup->addAttribute(Customer::ENTITY, 'ward_id', [
            'type' => 'varchar',
            'input' => 'select',
            'source' => \Magenest\AdditionalRegisterForm\Model\Config\Source\Ward::class,
            'label' => 'Customer Ward',
            'required' => true,
            'default' => 0,
            'visible' => true,
            'user_defined' => true,
            'system' => false,
            'is_visible_in_grid' => true,
            'is_used_in_grid' => true,
            'is_filterable_in_grid' => true,
            'is_searchable_in_grid' => true,
            'position' => 300
        ]);
        $wardAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'ward_id');
        $districtAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'district_id');
        $cityAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, 'city_id');


        $wardAttribute->addData([
            'used_in_forms' => ['adminhtml_checkout', 'adminhtml_customer', 'customer_account_edit', 'customer_account_create'],
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroup
        ]);
        $wardAttribute->save();

        $districtAttribute->addData([
            'used_in_forms' => ['adminhtml_checkout', 'adminhtml_customer', 'customer_account_edit', 'customer_account_create'],
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroup
        ]);
        $districtAttribute->save();

        $cityAttribute->addData([
            'used_in_forms' => ['adminhtml_checkout', 'adminhtml_customer', 'customer_account_edit', 'customer_account_create'],
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroup
        ]);
        $cityAttribute->save();
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
