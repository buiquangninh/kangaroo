<?php

namespace Magenest\RewardPoints\Block\Adminhtml\Transaction\Edit;

use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Class Form
 * @package Magenest\RewardPoints\Block\Adminhtml\Transaction\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
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
        array $data
    ) {
        $this->_systemStore           = $systemStore;
        $this->_groupRepository       = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter       = $objectConverter;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('rewardpoints_transaction');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save'),
                'method' => 'post',
            ]
        ]);

        $fieldset = $form->addFieldset(
            'general_fieldset',
            [
                'legend' => __('General Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        $fieldset->addField('customer_email', 'text', [
            'name' => 'customer_email',
            'label' => __('Customer'),
            'title' => __('Customer'),
            'required' => true,
            'readonly' => true,
            'text' => 'abcd'
        ])->setAfterElementHtml(
            '<div id="customer-grid" style="display:none"></div>
                <script type="text/x-magento-init">
                    {
                        "#customer_email": {
                            "Magenest_StoreCredit/js/view/transaction":{
                                "url": "' . $this->getAjaxUrl() . '"
                            }
                        }
                    }
                </script>'
        );
        $fieldset->addField(
            'customer_id',
            'hidden',
            [
                'name'     => 'customer_id',
                'label'    => __('Customer ID'),
                'title'    => __('Customer ID'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'points_change',
            'text',
            [
                'label'    => __('Points Change'),
                'title'    => __('Points Change'),
                'name'     => 'points_change',
                'note'     => __('Use - if you want to remove points from customer.'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'comment',
            'text',
            [
                'label'    => __('Comment to     customer'),
                'title'    => __('Comment to     customer'),
                'note'     => __('This will be shown in customer transaction history.'),
                'name'     => 'comment',
                'required' => true
            ]
        );
        $form->setUseContainer(true);
        $form->setValues($model->getData());
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

    /**
     * Get transaction grid url
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('mpstorecredit/transaction/customergrid');
    }
}
