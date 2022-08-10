<?php

namespace Magenest\RewardPoints\Block\Adminhtml\Rule\Edit\Tab;

use Magenest\RewardPoints\Helper\Data;
use Magenest\RewardPoints\Model\Rule;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Model\Metadata\CustomerMetadata as CustomerAttribute;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class Conditions extends Generic implements TabInterface
{
    /**
     * @var Fieldset
     */
    protected $_rendererFieldset;

    /**
     * @var \Magento\Rule\Block\Conditions
     */
    protected $_conditions;

    /**
     * @var
     */
    protected $type;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_rule;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CustomerAttribute
     */
    protected $_customerAttributes;

    /**
     * Conditions constructor.
     * @param CustomerAttribute $customerAttributes
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param Fieldset $rendererFieldset
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        CustomerAttribute              $customerAttributes,
        Context                        $context,
        Registry                       $registry,
        FormFactory                    $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        Fieldset                       $rendererFieldset,
        \Magento\SalesRule\Model\Rule  $rule,
        Data                           $helper,
        array                          $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        $this->_rule = $rule;
        $this->helper = $helper;
        $this->_customerAttributes = $customerAttributes;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     * @return string
     */
    public function getTabLabel()
    {
        return __('Conditions');
    }

    /**
     * Prepare title for tab
     * @return string
     */
    public function getTabTitle()
    {
        return __('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not
     * @return bool
     */
    public function canShowTab()
    {
        $model = $this->_coreRegistry->registry('rewardpoints_rule');
        if ($model->getId()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns status flag about this tab hidden or not
     * @return bool
     */
    public function isHidden()
    {
        $model = $this->_coreRegistry->registry('rewardpoints_rule');
        if ($model->getId()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return Form
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('rewardpoints_rule');
        /**
         * @var \Magento\Framework\Data\Form $form
         */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $type = $model->getRuleType();

        if ($type == 1) {
            $renderer = $this->_rendererFieldset->setTemplate(
                'Magento_CatalogRule::promo/fieldset.phtml'
            )->setNewChildUrl(
                $this->getUrl('catalog_rule/promo_catalog/newConditionHtml/form/rule_conditions_fieldset')
            );

            $fieldset = $form->addFieldset(
                'conditions_fieldset',
                ['legend' => __('Product Restriction (don\'t add conditions if rule is applied to all products)')]
            )->setRenderer(
                $renderer
            );

            $fieldset->addField(
                'conditions',
                'text',
                ['name' => 'conditions', 'label' => __('Conditions'), 'title' => __('Conditions'), 'required' => true]
            )->setRule(
                $model
            )->setRenderer(
                $this->_conditions
            );
        } else if ($type == 2) {
            $fieldset = $form->addFieldset(
                'action_fieldset',
                ['legend' => __('Condition')]
            );

            $options = [
                'review' => __('Customer writes a product review'),
                'registration' => __('Customer signs up in store'),
                Rule::CONDITION_REGISTRATION_AFFILIATE => __('Customer successfully registered for the affiliate program'),
                Rule::CONDITION_LOGIN_DAILY => __('Customer login daily'),
                'newsletter' => __('Customer subscribes to newsletter'),
                'birthday' => __('Customer birthday'),
                Rule::CONDITION_FIRST_PURCHASE => __('First Purchase'),
                Rule::CONDITION_LIFETIME_AMOUNT => __('Lifetime Amount'),
                'grateful' => __('Customer Gratitude'),
            ];

            if ($this->helper->getEnableReferralCodes()) {
                $options[Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED] = __('Earn When Reference Click');
                $options[Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED_AND_REGISTER] = __('Earn When Reference Click And Register New Account');
                $options[Rule::CONDITION_EARN_WHEN_REFEREE_CLICKED_AND_PLACE_ORDER] = __('Earn When Reference Click And Place Order');
            }

            $fieldset->addField(
                'condition',
                'select',
                [
                    'label' => __('Condition'),
                    'title' => __('Condition'),
                    'name' => 'condition',
                    'options' => $options
                ]
            );

            $fieldset->addField(
                'minimum_amount',
                'text',
                [
                    'name' => 'minimum_amount',
                    'required' => true,
                    'label' => __('Minimum Amount'),
                    'title' => __('Minimum Amount'),
                    'class' => 'validate-number validate-greater-than-zero'
                ]
            );

            $fieldset->addField(
                'customer_number',
                'text',
                [
                    'name' => 'customer_number',
                    'required' => true,
                    'label' => __('Customer Number'),
                    'title' => __('Customer Number'),
                    'class' => 'validate-number validate-greater-than-zero'
                ]
            );

            $fieldset->addField(
                'rule_configs[lt_amount_type]',
                'select',
                [
                    'name' => 'rule_configs[lt_amount_type]',
                    'label' => __('Based Amount'),
                    'title' => __('Based Amount'),
                    'options' => [
                        1 => __('Invoiced Amount'),
                        2 => __('Ordered Amount'),
                    ],
                ]
            );

            $fieldset->addField(
                'rule_configs[lt_per_amount]',
                'text',
                [
                    'name' => 'rule_configs[lt_per_amount]',
                    'required' => true,
                    'label' => __('Per Amount'),
                    'title' => __('Per Amount'),
                    'class' => 'validate-number validate-greater-than-zero'
                ]
            );

            $fieldset->addField(
                'customer_attributes',
                'multiselect',
                [
                    'label' => __('Choose Customer Attribute'),
                    'required' => true,
                    'name' => 'customer_attributes',
                    'values' => $this->getAllCustomerAttributes()
                ]
            );

            $htmlIdPrefix = $form->getHtmlIdPrefix();

            $this->setChild(
                'form_after',
                $this->getLayout()->createBlock(
                    'Magento\Backend\Block\Widget\Form\Element\Dependence'
                )->addFieldMap(
                    "{$htmlIdPrefix}condition",
                    'condition'
                )->addFieldMap(
                    "{$htmlIdPrefix}minimum_amount",
                    'minimum_amount'
                )->addFieldMap(
                    "{$htmlIdPrefix}customer_number",
                    'customer_number'
                )->addFieldMap(
                    "{$htmlIdPrefix}rule_configs[lt_amount_type]",
                    'rule_configs[lt_amount_type]'
                )->addFieldMap(
                    "{$htmlIdPrefix}rule_configs[lt_per_amount]",
                    'rule_configs[lt_per_amount]'
                )->addFieldMap(
                    "{$htmlIdPrefix}customer_attributes",
                    'customer_attributes'
                )->addFieldDependence(
                    'minimum_amount',
                    'condition',
                    Rule::CONDITION_FIRST_PURCHASE
                )->addFieldDependence(
                    'customer_number',
                    'condition',
                    'grateful'
                )->addFieldDependence(
                    'rule_configs[lt_amount_type]',
                    'condition',
                    Rule::CONDITION_LIFETIME_AMOUNT
                )->addFieldDependence(
                    'rule_configs[lt_per_amount]',
                    'condition',
                    Rule::CONDITION_LIFETIME_AMOUNT
                )->addFieldDependence(
                    "customer_attributes",
                    'condition',
                    Rule::CONDITION_CUSTOMER_FILL_FULL_DETAIL
                )
            );
        }
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    protected function getAllCustomerAttributes()
    {
        $attrUsedInForms = $this->_customerAttributes->getAttributes('customer_account_edit');

        $attributes = [];
        foreach ($attrUsedInForms as $attributeMetadata) {
            $attributes[] = [
                'value' => $attributeMetadata->getAttributeCode(),
                'label' => $attributeMetadata->getFrontendLabel()
            ];
        }

        return $attributes;
    }
}
