<?php

namespace Magenest\PaymentEPay\Controller\Adminhtml\Payment;

use Magento\Backend\App\Action;
use Magento\Sales\Api\Data\OrderInterface;

class OnePartPayment extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    protected $request;
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    protected $resultFactory;

    /**
     * Index constructor.
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magenest\SalesPerson\Helper\AssignedToSales $assignedToSales
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param Action\Context $context
     */

    public function __construct(
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Action\Context $context
    )
    {
        $this->resultFactory = $resultFactory;
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->json = $json;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $params = $this->request->getParams();
            if (isset($params["selected"])) {
                foreach ($params["selected"] as $orderId) {
                    $order = $this->orderRepository->get($orderId);
                    $order->setData('is_one_part_payment_done', 1);
                    $this->orderRepository->save($order);
                }

                $this->messageManager->addSuccessMessage(__('The order had been assigned.'));
                return $resultRedirect->setPath('sales/order/index');
            } else {
                $order = $this->getOrder();
                $order->setData('is_one_part_payment_done', 1);
                $this->orderRepository->save($order);
                return $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            }
        } catch (\Exception $exception) {
        }
    }

    /**
     * @return false|OrderInterface
     */
    private function getOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        try {
            return $this->orderRepository->get($id);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('This order no longer exists.'));
            return false;
        }
    }
}
