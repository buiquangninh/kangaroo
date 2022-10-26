<?php
/**
 * Copyright © Affiliate All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\Affiliate\Controller\Adminhtml\Group;

use Magenest\Affiliate\Model\GroupFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magenest\Affiliate\Controller\Adminhtml\Group
{

    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param GroupFactory $groupFactory
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        GroupFactory $groupFactory,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $groupFactory, $coreRegistry, $resultPageFactory);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('group_id');
        $model = $this->_objectManager->create(\Magenest\Affiliate\Model\Group::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Group no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('magenest_affiliate_group', $model);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Group') : __('New Group'),
            $id ? __('Edit Group') : __('New Group')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Groups'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Group %1', $model->getId()) : __('New Group'));
        return $resultPage;
    }
}

