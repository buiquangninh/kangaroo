<?php
/**
 * @copyright Copyright (c) magenest.com, Inc. (https://www.magenest.com)
 */

namespace Magenest\OrderManagement\Controller\Adminhtml\Order;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Controller\Adminhtml\Order as Action;

class UpdateOrderCreator extends Action
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $orderCreator = $this->getRequest()->getParam('order_creator');
            $order        = $this->_initOrder();
            $order->setOrderCreator($orderCreator);
            $this->orderRepository->save($order);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Something went wrong'));
            return $this->_redirect('sales/order');
        }
        $this->messageManager->addSuccessMessage(__('Save order creator successfully'));
        return $this->resultRedirectFactory->create()
            ->setPath('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }
}
