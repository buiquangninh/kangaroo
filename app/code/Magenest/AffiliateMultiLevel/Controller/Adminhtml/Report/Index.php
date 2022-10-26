<?php


namespace Magenest\AffiliateMultiLevel\Controller\Adminhtml\Report;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Magenest\Affiliate\Controller\Adminhtml\Account
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $pageFactory
    ) {
        $this->_resultPageFactory = $pageFactory;
        parent::__construct($context);
    }

    public const ADMIN_RESOURCE = 'Magenest_AffiliateMultiLevel::report_CTV';
    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magenest_AffiliateMultiLevel::report_CTV');
        $resultPage->getConfig()->getTitle()->prepend((__('Reports CTV')));

        return $resultPage;
    }
}
