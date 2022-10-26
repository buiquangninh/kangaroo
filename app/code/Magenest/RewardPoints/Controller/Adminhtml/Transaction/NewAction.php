<?php


namespace Magenest\RewardPoints\Controller\Adminhtml\Transaction;


use Magenest\RewardPoints\Controller\Adminhtml\Transaction;

class NewAction extends Transaction
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_forward('edit');
    }

}
