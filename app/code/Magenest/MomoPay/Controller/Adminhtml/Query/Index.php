<?php

namespace Magenest\MomoPay\Controller\Adminhtml\Query;

use Magento\Framework\App\ResponseInterface;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magenest_MomoPay::query_listing';

    /**
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('MoMo Query Logs'));
        $resultPage->setActiveMenu('Magenest_Backend::Magenest');
        return $resultPage;
    }
}