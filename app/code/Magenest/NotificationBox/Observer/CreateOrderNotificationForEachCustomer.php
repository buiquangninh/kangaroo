<?php

namespace Magenest\NotificationBox\Observer;

use Magenest\NotificationBox\Model\CustomerNotificationFactory as NotificationModel;
use Magenest\NotificationBox\Model\ResourceModel\CustomerNotification as NotificationResource;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class CreateOrderNotificationForEachCustomer implements ObserverInterface
{
    /**
     * @var NotificationModel
     */
    protected $model;

    /**
     * @var NotificationResource
     */
    protected $resource;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    protected $logger;

    protected $url;

    protected $serializer;

    /**
     * CreateOrderNotificationForEachCustomer constructor.
     * @param NotificationModel $model
     * @param NotificationResource $resource
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param UrlInterface $url
     * @param SerializerInterface $serializer
     */
    public function __construct(
        NotificationModel $model,
        NotificationResource $resource,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        UrlInterface $url,
        SerializerInterface $serializer
    ) {
        $this->model    = $model;
        $this->resource = $resource;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->url = $url;
        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        $orderComment = $observer->getStatusHistory();

        try {
            $order = $this->orderRepository->get($orderComment->getParentId());
            if ($order->getCustomerId() && $orderComment->getIsCustomerNotifiedStoreFront()) {
                $items = $order->getItems();
                $item = array_shift($items);
                $model = $this->model->create();
                $model->setData([
                    'customer_id' => $order->getCustomerId(),
                    'status' => 0,
                    'description' => $orderComment->getComment(),
                    'redirect_url' => $this->url->getBaseUrl() . "sales/order/view/order_id/" . $order->getEntityId(),
                    'additional_data' => $this->serializer->serialize([
                        'order_id' => $order->getEntityId(),
                        'title' => $orderComment->getTitle(),
                        'order_status' => $order->getStatus(),
                        'first_item_sku' => $item->getSku()
                    ]),
                    'notification_type' => 'order_' . $order->getEntityId()
                ]);
                $this->resource->save($model);
            }

        } catch (\Exception $e) {
            $this->logger->error("Error while creating customer notification. Details: " . $e->getMessage());
        }
    }
}
