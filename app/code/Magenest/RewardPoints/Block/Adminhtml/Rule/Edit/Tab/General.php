<?php

namespace Magenest\RewardPoints\Block\Adminhtml\Rule\Edit\Tab;

use Magenest\RewardPoints\Api\Data\RuleInterface;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Msrp\Model\Product\Attribute\Source\Type\Price;
use Mirasvit\CustomerSegment\Model\ResourceModel\Segment\Collection as GroupCollection;
use Magenest\RewardPoints\Model\ResourceModel\Membership\Collection as MembershipCollection;

/**
 * Class General
 * @package Magenest\RewardPoints\Block\Adminhtml\Rule\Edit\Tab
 */
class General extends Generic implements TabInterface
{
    /**
     * @var \Magenest\RewardPoints\Model\Status
     */
    protected $_status;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;
    /**
     * @var GroupCollection
     */
    private $groupCollection;
    /**
     * @var MembershipCollection
     */
    private $membershipCollection;
    /**
     * General constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magenest\RewardPoints\Model\Status $status
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magenest\RewardPoints\Model\Status $status,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Store\Model\System\Store $systemStore,
        GroupCollection $groupCollection,
        MembershipCollection $membershipCollection,
        array $data
    ) {
        $this->_systemStore           = $systemStore;
        $this->_groupRepository       = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter       = $objectConverter;
        $this->groupCollection = $groupCollection;
        $this->membershipCollection = $membershipCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('rewardpoints_rule');
        $rewardPointImage = $model->getData('rewardpoint_img');
//        $model->setData('rewardpoint_img', null);
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset(
            'general_fieldset',
            [
                'legend' => __('General Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name'     => 'title',
                'label'    => __('Rule Name'),
                'title'    => __('Rule Name'),
                'note'     => __('This will be shown in customer transaction history.'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label'    => __('Status'),
                'title'    => __('Status'),
                'name'     => 'status',
                'required' => true,
                'options'  => [RuleInterface::RULE_STATUS_ACTIVE => __('Active'), RuleInterface::RULE_STATUS_INACTIVE => __('Inactive')]
            ]
        );
        $url = $this->getBaseUrl().'/media/rewardpoint'.$model->getData('rewardpoint_img');
        $fieldset->addField(
            'rewardpoint_img',
            'image',
            [
                'name' => 'rewardpoint_img',
                'label' => __('Image'),
                'title' => __('Image'),
                'note' => 'Allow image type: jpg, jpeg, png',
                'required' => false,
            ]
        )->setBeforeElementHtml(
            '<img src="'.$url.'" id="rule_rewardpoint_img_image" />'.
            '<script>' .
            "
                 require(['jquery'], function($){
                    $('#rule_rewardpoint_img').change(function() {
                       const [file] = rule_rewardpoint_img.files;
                          if (file) {
                            $('#rule_rewardpoint_img_image').attr('src','#');
                            rule_rewardpoint_img_image.src = URL.createObjectURL(file)
                            $('#rule_rewardpoint_img_image').attr('width','300px');
                            $('#rule_rewardpoint_img_image').attr('height','300px');
                            $('#rule_rewardpoint_img_delete').attr('display','none');
                          }
                    })
                });
                " .
            '</script>'
        );
        $fieldset->addField(
            'description',
            'textarea',
            [
                'label'    => __('Description'),
                'title'    => __('Description'),
                'name'     => 'description',
            ]
        );
        $fieldset->addField(
            'rule_type',
            'select',
            [
                'label'    => __('Rule Type'),
                'title'    => __('Rule Type'),
                'name'     => 'rule_type',
                'required' => true,
                'disabled' => $model->getId() ? true : false,
                'options'  => [RuleInterface::RULE_TYPE_PRODUCT => __('Product Rule'), RuleInterface::RULE_TYPE_BEHAVIOR => __('Behaviour Rule')]
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(
            \IntlDateFormatter::SHORT
        );
        $fieldset->addField(
            'from_date',
            'date',
            [
                'name'         => 'from_date',
                'label'        => __('From Date'),
                'title'        => __('From Date'),
                'image'        => $this->getViewFileUrl('images/grid-cal.png'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format'  => $dateFormat
            ]
        );
        $fieldset->addField(
            'to_date',
            'date',
            [
                'name'         => 'to_date',
                'label'        => __('To Date'),
                'title'        => __('To Date'),
                'image'        => $this->getViewFileUrl('images/grid-cal.png'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format'  => $dateFormat
            ]
        );

        $fieldset->addField(
            'customer_segment_group_ids',
            'multiselect',
            [
                'name' => 'customer_segment_group_ids[]',
                'label' => __('Customer Segment Group'),
                'title' => __('Customer Segment Group'),
                'values' => $this->groupCollection->toOptionArray(),
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'membership_group_ids',
            'multiselect',
            [
                'name' => 'membership_group_ids[]',
                'label' => __('Membership Group'),
                'title' => __('Membership Group'),
                'values' => $this->membershipCollection->toOptionArray(),
                'disabled' => false
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name'         => 'sort_order',
                'label'        => __('Priority'),
                'title'        => __('Priority'),
                'image'        => $this->getViewFileUrl('images/grid-cal.png'),
            ]
        );
        $fieldset->addField(
            'stop_rules_processing',
            'select',
            [
                'name'         => 'stop_rules_processing',
                'label'        => __('Discard subsequent rules'),
                'title'        => __('Discard subsequent rules'),
                'image'        => $this->getViewFileUrl('images/grid-cal.png'),
                'options'  =>  [RuleInterface::RULE_STOP_RULES_PROCESSING => __('Yes'), RuleInterface::RULE_NOT_STOP_RULES_PROCESSING => __('No')]
            ]
        );
//        $model->setData('status', 1);
        $form->setValues($model->getData());
        $form->addValues(['rewardpoint_img'=> null]);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabLabel()
    {
        return __('General Information');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTabTitle()
    {
        return __('General Information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
