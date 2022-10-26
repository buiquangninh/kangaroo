<?php
/**
 * Copyright © AffiliateMultiLevelUpdate All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\PaymentEPay\Rewrite\Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use \Magenest\PaymentEPay\Block\Adminhtml\Form\Field\DynamicInstallmentOptions;

class Attributes extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Attributes
{
    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Attribute
     */
    protected $_attributeAction;

    /**
     * @var array
     */
    private $excludeFields;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    public function __construct(
        Context             $context,
        Registry            $registry,
        FormFactory         $formFactory,
        ProductFactory      $productFactory,
        Attribute           $attributeAction,
        array               $data = [],
        array               $excludeFields = null,
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->_attributeAction = $attributeAction;
        $this->_productFactory = $productFactory;
        $this->excludeFields = $excludeFields ?: [];
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        parent::__construct($context, $registry, $formFactory, $productFactory, $attributeAction, $data, $excludeFields, $secureRenderer);
    }

    protected function _prepareForm(): void
    {
        $this->setFormExcludedFieldList($this->excludeFields);
        $this->_eventManager->dispatch(
            'adminhtml_catalog_product_form_prepare_excluded_field_list',
            ['object' => $this]
        );

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $fieldset = $form->addFieldset('fields', ['legend' => __('Attributes')]);
        $attributes = $this->getAttributes();

        /**
         * Initialize product object as form property
         * for using it in elements generation
         */
        $form->setDataObject($this->_productFactory->create());
        $this->_setFieldset($attributes, $fieldset, $this->getFormExcludedFieldList());

        $layoutBlock = $this->getLayout()->createBlock(
            DynamicInstallmentOptions::class
        );

        $fieldset->getForm()->getElement('installment_options')->setRenderer($layoutBlock);
        $form->setFieldNameSuffix('attributes');
        $this->setForm($form);
    }
}
