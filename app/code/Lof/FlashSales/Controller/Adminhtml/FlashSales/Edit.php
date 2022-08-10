<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace Lof\FlashSales\Controller\Adminhtml\FlashSales;

use Lof\FlashSales\Model\FlashSalesFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends \Magento\Backend\App\Action implements HttpGetActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Lof_FlashSales::flashsales';

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var FlashSalesFactory
     */
    protected $flashSalesModel;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        FlashSalesFactory $flashSalesModel,
        PageFactory $resultPageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->flashSalesModel = $flashSalesModel;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Edit Flash Sale
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->flashSalesModel->create();

        if ($id) {
            $model = $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Flashsales no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setFormName('lof_flashsales_form');
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId(
                $model->getConditions()->getFormName()
            )
        );

        $this->_coreRegistry->register('lof_flashsales_events', $model);

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Flash Sale') : __('New Flash Sale'),
            $id ? __('Edit Flash Sale') : __('New Flash Sale')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Flash Sale'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __(
            '%1',
            $model->getEventName()
        ) : __('New Flash Sale'));
        return $resultPage;
    }

    /**
     * Init page
     * @param Page $resultPage
     * @return Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Lof'), __('Lof'))
            ->addBreadcrumb(__('Flash Sales'), __('Flash Sales'));
        return $resultPage;
    }
}
