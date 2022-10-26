<?php


namespace Magenest\RewardPoints\Controller\Adminhtml\Transaction;


use Magenest\Directory\Model\District as WardModel;
use Magenest\RewardPoints\Controller\Adminhtml\Transaction;
use Magenest\RewardPoints\Model\TransactionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Transaction
{
    public function __construct(Action\Context $context, PageFactory $pageFactory, TransactionFactory $transactionFactory, Registry $registry)
    {
        parent::__construct($context, $pageFactory, $transactionFactory, $registry);
    }

    public function execute()
    {
        $transaction = $this->_initObject();
        if (!$transaction) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*');

            return $resultRedirect;
        }
        //Set entered data if was error when we do save
        $data = $this->_session->getData('transaction_form', true);
        if (!empty($data)) {
            $transaction->addData($data);
        }

        $this->_coreRegistry->register('rewardpoints_transaction', $transaction);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Create New Transaction'));

        return $resultPage;
    }

    /**
     * Init transaction
     *
     * @return bool|WardModel
     */
    protected function _initObject()
    {
        $transactionId = (int)$this->getRequest()->getParam('id');
        $transaction = $this->_transactionFactory->create();

        if ($transactionId) {
            $transaction->load($transactionId);
            if (!$transaction->getId()) {
                $this->messageManager->addErrorMessage(__('This transaction no longer exists.'));

                return false;
            }
        }

        return $transaction;
    }
}
