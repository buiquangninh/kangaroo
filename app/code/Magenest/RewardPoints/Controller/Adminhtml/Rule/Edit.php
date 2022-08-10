<?php

namespace Magenest\RewardPoints\Controller\Adminhtml\Rule;

use Magenest\RewardPoints\Controller\Adminhtml\Rule;
use Magenest\RewardPoints\Model\ResourceModel\Rule as RuleResource;
use Magenest\RewardPoints\Model\RuleFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Zend_Json;
use Zend_Json_Exception;

/**
 * Class Edit
 * @package Magenest\RewardPoints\Controller\Adminhtml\Rule
 */
class Edit extends Rule
{
    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var RuleResource
     */
    protected $_ruleResource;

    /**
     * Edit constructor.
     *
     * @param Action\Context $context
     * @param PageFactory $pageFactory
     * @param RuleFactory $ruleFactory
     * @param RuleResource $ruleResource
     * @param Registry $registry
     */
    public function __construct(
        Action\Context $context,
        PageFactory    $pageFactory,
        RuleFactory    $ruleFactory,
        RuleResource   $ruleResource,
        Registry       $registry
    ) {
        $this->backendSession = $context->getSession();
        $this->_ruleResource = $ruleResource;
        parent::__construct($context, $pageFactory, $ruleFactory, $registry);
    }

    /**
     * @return Page|ResponseInterface|Redirect|ResultInterface
     * @throws Zend_Json_Exception
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $rule = $this->_ruleFactory->create();

        if ($id) {
            $this->_ruleResource->load($rule, $id, 'id');

            if (!$rule->getId()) {
                $this->messageManager->addError(__('This rule doesn\'t exist'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
            $data = $this->backendSession->getFormData(true);
            if (!empty($data)) {
                $rule->setData($data);
            }
            switch ($rule->getCondition()) {
                case \Magenest\RewardPoints\Model\Rule::CONDITION_FIRST_PURCHASE :
                    $rule->setMinimumAmount($rule->getConditions()->getConditions()[0]->getValue());
                    break;
                case \Magenest\RewardPoints\Model\Rule::CONDITION_GRATEFUL :
                    $rule->setCustomerNumber($rule->getConditions()->getConditions()[0]->getValue());
                    break;
                case \Magenest\RewardPoints\Model\Rule::CONDITION_CUSTOMER_FILL_FULL_DETAIL :
                    $rule->setCustomerAttributes($rule->getConditions()->getValue());
                    break;
            }
            $this->_eventManager->dispatch('registry_add_points_referred', ['rule' => $rule]);
            // add additional configuration
            $ruleConfigs = $rule->getRuleConfigs();
            if (!empty($ruleConfigs)) {
                $ruleConfigs = Zend_Json::decode($ruleConfigs);
                if (!empty($ruleConfigs)) {
                    foreach ($ruleConfigs as $key => $value) {
                        $name = 'rule_configs[' . $key . ']';
                        $rule->setData($name, $value);
                    }
                }
            }
        }
        $this->_coreRegistry->register('rewardpoints_rule', $rule);

        /** @var Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()
            ->prepend((!empty($id) && $id == $rule->getId()) ? __('Edit Rule \'%1\'', $rule->getData('title')) : __('New Reward Points Rule'));

        return $resultPage;
    }
}
