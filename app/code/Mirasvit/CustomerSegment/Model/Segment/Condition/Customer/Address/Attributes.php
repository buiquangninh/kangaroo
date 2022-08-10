<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-customer-segment
 * @version   1.2.1
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CustomerSegment\Model\Segment\Condition\Customer\Address;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\UrlInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Directory\Model\Config\Source\Allregion;
use Mirasvit\CustomerSegment\Model\Segment\Rule;

class Attributes extends AbstractCondition
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var Allregion
     */
    private $directoryAllregion;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * Attributes constructor.
     *
     * @param Allregion                    $directoryAllregion
     * @param SearchCriteriaBuilder        $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param Context                      $context
     * @param array                        $data
     */
    public function __construct(
        Allregion $directoryAllregion,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $attributeRepository,
        UrlInterface $url,
        Context $context,
        array $data = []
    ) {
        $this->directoryAllregion = $directoryAllregion;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->url = $url;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    public function getNewChildSelectOptions()
    {
        $conditions = [];
        foreach ($this->loadAttributeOptions()->getData('attribute_option') as $code => $label) {
            $conditions[] = [
                'value' => $this->getData('type') . '|' . $code,
                'label' => $label,
            ];
        }

        return $conditions;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAttributeOptions()
    {
        $attributes = [];
        $addressAttributesList = $this->attributeRepository->getList(
            'customer_address',
            $this->searchCriteriaBuilder->create()
        );

        foreach ($addressAttributesList->getItems() as $attr) {
            $label = $attr->getDefaultFrontendLabel();
            if ($attr->getAttributeCode() == 'region') {
                $label = __('Region');
            }
            if ($attr->getAttributeCode() == 'city_id') {
                $label = __('City (Selection)');
            }
            if ($attr->getAttributeCode() == 'district_id') {
                $label = __('District (Selection)');
            }
            if ($attr->getAttributeCode() == 'ward_id') {
                $label = __('Ward (Selection)');
            }

            $attributes[$attr->getAttributeCode()] = $label;
        }

        $this->setData('attribute_option', $attributes);

        return $this;
    }

    /**
     * Retrieve attribute element
     *
     * @return AbstractCondition
     */
    public function getAttributeElement()
    {
        $element = parent::getAttributeElement();
        $element->setData('show_as_text', true); // Do not allow to choose other elements from current optgroup

        return $element;
    }

    /**
     * Get current attribute object.
     *
     * @return \Magento\Eav\Api\Data\AttributeInterface|\Magento\Customer\Model\Attribute
     */
    private function getAttributeObject()
    {
        return $this->attributeRepository->get('customer_address', $this->getData('attribute'));
    }

    /**
     * @inheritDoc
     */
    public function getValueSelectOptions()
    {

        if (!$this->getData('value_select_options') && is_object($attr = $this->getAttributeObject())) {
            /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attr */
            if ($attr->usesSource()) {
                switch ($attr->getAttributeCode()) {
                    case 'region_id':
                        $options = $this->directoryAllregion->toOptionArray();
                        break;

                    default:
                        $options = $attr->getSource()->getAllOptions();
                }
                $this->setData('value_select_options', $options);
            }
        }

        return $this->getData('value_select_options');
    }

    /**
     * Get input type for attribute operators.
     *
     * @return string
     */
    public function getInputType()
    {
        if ($this->getData('attribute') == 'region_id') {
            return 'select';
        }

        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }

        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
            default:
                return 'string';
        }
    }

    /**
     * Get attribute value input element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->getData('attribute') == 'region_id') {
            return 'select';
        }

        if (!is_object($this->getAttributeObject())) {
            return 'text';
        }

        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
            default:
                return 'text';
        }
    }

    /**
     * Retrieve value element
     *
     * @return AbstractCondition
     */
    public function getValueElement()
    {
        $element = parent::getValueElement();
        if (is_object($attr = $this->getAttributeObject())) {
            switch ($attr->getFrontendInput()) {
                case 'date':
                    $element->setData('image', $this->_assetRepo->getUrl('Magento_Theme::calendar.png'));
                    break;
            }
        }

        return $element;
    }

    /**
     * Retrieve after element HTML
     *
     * @return string
     */
    public function getValueAfterElementHtml()
    {
        $html = '';

        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
            case 'city_id':
            case 'district_id':
            case 'ward_id':
                $image = $this->_assetRepo->getUrl('images/rule_chooser_trigger.gif');
                break;
        }

        if (!empty($image)) {
            $html = '<a href="javascript:void(0)" class="rule-chooser-trigger"><img src="' .
                $image .
                '" alt="" class="v-middle rule-chooser-trigger" title="' .
                __(
                    ' Open Chooser'
                ) . '" /></a>';
        }
        return $html;
    }

    /**
     * Chechk if attribute value should be explicit
     *
     * @return bool
     */
    public function getExplicitApply()
    {
        if (is_object($attr = $this->getAttributeObject())) {
            switch ($attr->getFrontendInput()) {
                case 'date':
                    return true;
            }
            switch ($attr->getAttributeCode()) {
                case 'city_id':
                case 'district_id':
                case 'ward_id':
                    return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function asHtml()
    {
        return __('Address: %1', parent::asHtml());
    }

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'city_id':
            case 'district_id':
            case 'ward_id':
                $url = 'customersegment/segment/chooser/field/' . $this->getAttribute();
                $formName = $this->getJsFormObject() ?? "rule_".Rule::FORM_NAME;
                $url .= '/form/' . $formName;
                break;
            default:
                break;
        }
        return $url !== false ? $this->url->getUrl($url) : '';
    }
}
