<?php

namespace Magenest\RewardPoints\Block\Adminhtml\Rule\Edit\Tab;

use Magenest\RewardPoints\Model\Status;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

/**
 * Class Actions
 *
 * @package Magenest\RewardPoints\Block\Adminhtml\Rule\Edit\Tab
 */
class Actions extends Generic implements TabInterface
{
    /**
     * @var Status
     */
    protected $_status;

    /**
     * @var
     */
    protected $type;

    /**
     * Actions constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Status $status
     * @param array $data
     */
    public function __construct(
        Context     $context,
        Registry    $registry,
        FormFactory $formFactory,
        Status      $status,
        array       $data
    ) {
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Phrase|string
     */
    public function getTabLabel()
    {
        return __('Actions');
    }

    /**
     * @return Phrase|string
     */
    public function getTabTitle()
    {
        return __('Actions');
    }

    /**
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
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('rewardpoints_rule');
        $this->type = $model->getRuleType();
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');
        $type = $model->getRuleType();
        $fieldset = $form->addFieldset(
            'action_fieldset',
            ['legend' => __('Set appropriate point action')]
        );

        if ($type) {
            $options = $model->ruleActionTypesToArray();
            $fieldset->addField(
                'action_type',
                'select',
                [
                    'label' => __('Apply'),
                    'name' => 'action_type',
                    'options' => $options,
                    'required' => true,
                    'disabled' => count($options) == 1 ? true : false
                ]
            );
        }

        $fieldset->addField(
            'steps',
            'text',
            [
                'name' => 'steps',
                'required' => true,
                'label' => __('Step (Y)'),
                'class' => 'validate-number validate-digits validate-greater-than-zero',
                'note' => __('This value is also the minimum required to receive reward points.')
            ]
        );

        $fieldset->addField(
            'points',
            'text',
            [
                'name' => 'points',
                'required' => true,
                'label' => __('Number of points (X)'),
                'class' => 'validate-number validate-digits validate-zero-or-greater'
            ]
        );
        if ($type == 2) {
            $fieldset->addField(
                'points_referred',
                'text',
                [
                    'name' => 'points_referred',
                    'required' => true,
                    'label' => __('Default number of points (Y) for the referred'),
                    'class' => 'validate-number validate-digits validate-greater-than-zero'
                ]
            );
        }

        $this->_eventManager->dispatch('reward_points_edit_prepare_form', array('block' => $this, 'form' => $form, 'model' => $model));

        $htmlIdPrefix = $form->getHtmlIdPrefix();

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}action_type",
                'action_type'
            )->addFieldMap(
                "{$htmlIdPrefix}steps",
                'steps'
            )->addFieldMap(
                "{$htmlIdPrefix}condition",
                'condition'
            )->addFieldMap(
                "{$htmlIdPrefix}points_referred",
                'points_referred'
            )->addFieldDependence(
                'steps',
                'action_type',
                '2'
            )->addFieldDependence(
                'points_referred',
                'condition',
                'referafriend'
            )->addFieldDependence(
                'points_referred',
                'condition',
                'earn_when_referee_clicked'
            )->addFieldDependence(
                'points_referred',
                'condition',
                'earn_when_referee_clicked_and_register'
            )->addFieldDependence(
                'points_referred',
                'condition',
                'earn_when_referee_clicked_and_place_order'
            )
        );
        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
