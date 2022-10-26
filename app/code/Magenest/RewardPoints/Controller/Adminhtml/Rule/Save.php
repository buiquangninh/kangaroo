<?php

namespace Magenest\RewardPoints\Controller\Adminhtml\Rule;

use DateTime;
use Exception;
use Magenest\RewardPoints\Api\Data\NotificationInterface;
use Magenest\RewardPoints\Controller\Adminhtml\Rule;
use Magenest\RewardPoints\Helper\Data;
use Magenest\RewardPoints\Model\NotificationFactory as NotificationModel;
use Magenest\RewardPoints\Model\ResourceModel\Notification;
use Magenest\RewardPoints\Model\ResourceModel\Rule\CollectionFactory;
use Magenest\RewardPoints\Model\RuleFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;
use Zend_Filter_Input;
use Zend_Json;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Save
 * @package Magenest\RewardPoints\Controller\Adminhtml\Rule
 */
class Save extends Rule
{
    /**
     * @var Date
     */
    protected $_dateFilter;

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CollectionFactory
     */
    protected $ruleCollectionFactory;

    protected $_notificationModel;

    protected $_notificationResource;

    /**
     * @var Data
     */
    protected $helper;

    private $serializer;

    /**
     * Save constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param RuleFactory $ruleFactory
     * @param Registry $registry
     * @param Date $dateFilter
     * @param LoggerInterface $logger
     * @param CollectionFactory $ruleCollectionFactory
     * @param NotificationModel $notificationModel
     * @param Notification $notificationResource
     * @param Data $helper
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem  $filesystem,
        AdapterFactory $adapterFactory,
        UploaderFactory $uploaderFactory,
        Action\Context    $context,
        PageFactory       $pageFactory,
        RuleFactory       $ruleFactory,
        Registry          $registry,
        Date              $dateFilter,
        CollectionFactory $ruleCollectionFactory,
        NotificationModel $notificationModel,
        Notification      $notificationResource,
        SerializerInterface $serializer,
        Data              $helper
    ) {
        $this->uploaderFactory = $uploaderFactory;
        $this->fileSystem = $filesystem;
        $this->adapterFactory = $adapterFactory;
        $this->_dateFilter = $dateFilter;
        $this->backendSession = $context->getSession();
        $this->logger = $logger;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->_notificationModel = $notificationModel;
        $this->_notificationResource = $notificationResource;
        $this->helper = $helper;
        $this->serializer = $serializer;
        parent::__construct($context, $pageFactory, $ruleFactory, $registry);
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $img = $this->getRequest()->getFiles('rewardpoint_img');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (isset($data['rule_configs'])) {
                $data['rule_configs'] = Zend_Json::encode($data['rule_configs']);
            }

            $model = $this->_ruleFactory->create();

            if (!empty($data['id'])) {
                $model->load($data['id']);
                if ($data['id'] != $model->getId()) {
                    throw new LocalizedException(__('Something went wrong, please try again later.'));
                }
            }

            if (!isset($data['steps'])) {
                $data['steps'] = null;
            }

            // set action rule due to disabled input
            $options = $model->ruleActionTypesToArrayOnRule();
            if (count($options) == 1) $model->setActionType(array_keys($options)[0]);
            // end

            // if rule is firstpurchase, create corresponding rule
            if (isset($data['condition']) && $data['condition'] == 'firstpurchase') {
                $data['rule']['conditions']['1'] = [
                    "type" => "Magento\CatalogRule\Model\Rule\Condition\Combine",
                    "aggregator" => "all",
                    "value" => 1,
                    "new_child" => "",
                ];
                $data['rule']['conditions']['1--1'] = [
                    "type" => "Magento\SalesRule\Model\Rule\Condition\Address",
                    "attribute" => "base_subtotal",
                    "operator" => ">=",
                    "value" => $data['minimum_amount'],
                ];
            }
            // end

            //if rule is grateful, create corresponding rule
            if (isset($data['condition']) && $data['condition'] == 'grateful') {
                $data['rule']['conditions']['1'] = [
                    "type" => "Magento\CatalogRule\Model\Rule\Condition\Combine",
                    "aggregator" => "all",
                    "value" => 1,
                    "new_child" => "",
                ];
                $data['rule']['conditions']['1--1'] = [
                    "type" => "Magento\SalesRule\Model\Rule\Condition\Address",
                    "attribute" => "customer_id",
                    "operator" => ">=",
                    "value" => $data['customer_number'],
                ];
            }
            //end

            // if rule is fill customer details
            if (isset($data['condition']) && $data['condition'] == \Magenest\RewardPoints\Model\Rule::CONDITION_CUSTOMER_FILL_FULL_DETAIL) {
                $data['rule']['conditions']['1'] = [
                    "type" => "Magento\CatalogRule\Model\Rule\Condition\Combine",
                    "aggregator" => "all",
                    "value" => $data['customer_attributes'],
                    "new_child" => "",
                ];
            }

            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }

            unset($data['rule']);
            // validate date time format for start date and end date
            try {
                if (isset($data['from_date'])) {
                    $day = $data['from_date'];
                    $date = new DateTime($day);
                }
                if (isset($data['to_date'])) {
                    $day = $data['to_date'];
                    $date = new DateTime($day);
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(sprintf(__("Invalid input datetime format of value %s."), $day));

                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
            if (!empty($data['membership_group_ids'])) {
                $data['membership_group_ids'] = implode(',', $data['membership_group_ids']);
            }

            if (!empty($data['customer_segment_group_ids'])) {
                $data['customer_segment_group_ids'] = implode(',', $data['customer_segment_group_ids']);
            }
            $data['rewardpoint_img'] = !empty($img['name']) ? $this->uploadImage('rewardpoint_img') : '';

            // end
            $model->loadPost($data);
            $this->backendSession->setPageData($model->getData());
            try {
                $inputFilter = new Zend_Filter_Input(
                    ['from_date' => $this->_dateFilter, 'to_date' => $this->_dateFilter],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                if (strtotime($data['from_date']) > strtotime($data['to_date'])) {
                    $this->messageManager->addErrorMessage("To Date must be greater than From Date.");

                    return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
                $validateResult = $model->validateData(new DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);
                    return;
                }
                $this->_eventManager->dispatch('save_referral_points', ['current_rule' => $model, 'referral_data' => $data]);
                $ruleCollection = $this->ruleCollectionFactory->create()->addFieldToFilter('condition', 'lifetimeamount');
                if (count($ruleCollection->getData())) {
                    $rule = $ruleCollection->getFirstItem();
                    if ($model->getCondition() == 'lifetimeamount' && $model->getId() != $rule->getId()) {
                        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                        $resultRedirect->setPath('*/*/');
                        $this->messageManager->addErrorMessage(__('Lifetime Amount Rule already exists. Please edit it instead.'));

                        return $resultRedirect;
                    }
                }

                $model->save();
                $this->saveNotification($model->getId());
                $this->helper->clearCacheByType(['block_html']);
                $this->_eventManager->dispatch('reward_point_rule_saved', array('form' => $data));
                $this->messageManager->addSuccess(__('Rule has been saved.'));
                $this->backendSession->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addError($e, __('Something went wrong while saving the rule.'));
                $this->logger->critical($e);
                $this->backendSession->setPageData($data);

                return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }


    /**
     * @param $fileId
     *
     * @return string|null
     * @throws Exception
     */
    private function uploadImage($fileId)
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
            $imageAdapter = $this->adapterFactory->create();
            $uploader->addValidateCallback('rewardpoint_img', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $mediaDirectory  = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
            $destinationPath = $mediaDirectory->getAbsolutePath('rewardpoint');
            $result          = $uploader->save($destinationPath);
            if (!$result) {
                throw new LocalizedException(__('File cannot be saved to path: $1', $destinationPath));
            }

            return $result['file'];
        } catch (\Exception $e) {
            $this->logger->critical(
                $e->getMessage(),
                ['trace' => $e->getTraceAsString(), 'path' => $destinationPath ?? ""]
            );
            $this->messageManager->addExceptionMessage($e);
            return null;
        }
    }
    /**
     * @param $ruleId
     * @throws AlreadyExistsException
     */
    protected function saveNotification($ruleId)
    {
        $ruleData = $this->getRequest()->getPostValue();
        if (!empty($ruleId)) {
            $notificationModel = $this->_notificationModel->create();
            if (!empty($ruleData['notification_id'])) {
                $this->_notificationResource->load($notificationModel, $ruleData['notification_id'], NotificationInterface::ENTITY_ID);
            }

            $notificationModel->addData([
                NotificationInterface::NOTIFICATION_STATUS => $ruleData['notification_status'],
                NotificationInterface::NOTIFICATION_DISPLAY_POSITION => $ruleData['notification_display_position'] ?? '',
                NotificationInterface::NOTIFICATION_DISPLAY_FOR_GUEST => $ruleData['notification_display_for_guest'] ?? 0,
                NotificationInterface::NOTIFICATION_CONTENT => $ruleData['notification_content'] ?? '',
                NotificationInterface::RULE_ID => $ruleId
            ]);

            $this->_notificationResource->save($notificationModel);
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_RewardPoints::system_rewardpoints_earning_rule');
    }
}
