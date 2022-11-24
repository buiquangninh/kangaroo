<?php

namespace Magenest\MomoPay\Controller\Order;

use Magenest\MomoPay\Api\MomoOrderInfoInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

class AjaxGetPayUrl implements HttpPostActionInterface
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Info constructor.
     * @param RequestInterface $request
     * @param OrderFactory $orderFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param JsonFactory $json
     */
    public function __construct(
        RequestInterface $request,
        OrderFactory $orderFactory,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $json
    ) {
        $this->jsonFactory = $json;
        $this->orderFactory = $orderFactory;
        $this->request = $request;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $id = $this->request->getParam('order_id');
        if ($id) {
            /** @var Order $order */
            $order = $this->orderRepository->get($id);
            $url = $order->getData(MomoOrderInfoInterface::PAYMENT_URL);
            $resultJson->setData($url);
        }

        return $resultJson;
    }
}