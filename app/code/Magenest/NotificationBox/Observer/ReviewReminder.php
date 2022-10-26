<?php
namespace Magenest\NotificationBox\Observer;

use Exception;
use Magenest\NotificationBox\Helper\Helper;
use Magenest\NotificationBox\Model\CustomerNotification as CustomerNotificationModel;
use Magenest\NotificationBox\Model\CustomerNotificationFactory;
use Magenest\NotificationBox\Model\CustomerToken as CustomerTokenModel;
use Magenest\NotificationBox\Model\CustomerTokenFactory;
use Magenest\NotificationBox\Model\Notification;
use Magenest\NotificationBox\Model\NotificationFactory;
use Magenest\NotificationBox\Model\NotificationQueueFactory;
use Magenest\NotificationBox\Model\ResourceModel\CustomerNotification;
use Magenest\NotificationBox\Model\ResourceModel\CustomerNotification\CollectionFactory as CustomerNotificationCollectionFactory;
use Magenest\NotificationBox\Model\ResourceModel\CustomerToken;
use Magenest\NotificationBox\Model\ResourceModel\CustomerToken\CollectionFactory as Collection;
use Magenest\NotificationBox\Model\ResourceModel\Notification\CollectionFactory;
use Magenest\NotificationBox\Model\ResourceModel\NotificationQueue;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Url;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ReviewReminder implements ObserverInterface
{
    public const NOTIFICATION_IDENTITY = "Generated notification for review reminder";

    /** @var Helper */
    protected $helper;

    /** @var CollectionFactory */
    protected $collection;

    /** @var Json */
    protected $serialize;

    /** @var CustomerNotificationCollectionFactory */
    protected $customerNotification;

    /** @var CustomerNotification */
    protected $customerNotificationResource;

    /** @var CustomerNotificationFactory */
    protected $customerNotificationFactory;

    /** @var CustomerTokenFactory */
    protected $customerTokenFactory;

    /** @var CustomerToken */
    protected $customerTokenResource;

    /** @var Collection */
    protected $tokenCollection;

    /** @var LoggerInterface */
    protected $logger;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var NotificationQueueFactory */
    protected $notificationQueueFactory;

    /** @var NotificationQueue */
    protected $notificationQueue;

    /** @var Session */
    protected $checkoutSession;

    /** @var NotificationFactory */
    private $notificationFactory;

    /** @var Url */
    private $urlBuilder;

    /**
     * @param CustomerNotification $customerNotificationResource
     * @param Collection $tokenCollection
     * @param CustomerTokenFactory $customerTokenFactory
     * @param CustomerToken $customerTokenResource
     * @param CollectionFactory $collection
     * @param Json $serialize
     * @param CustomerNotificationCollectionFactory $customerNotification
     * @param CustomerNotificationFactory $customerNotificationFactory
     * @param Helper $helper
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param NotificationQueue $notificationQueue
     * @param NotificationQueueFactory $notificationQueueFactory
     * @param NotificationFactory $notificationFactory
     * @param Session $checkoutSession
     * @param Url $urlBuilder
     */
    public function __construct(
        CustomerNotification                  $customerNotificationResource,
        Collection                            $tokenCollection,
        CustomerTokenFactory                  $customerTokenFactory,
        CustomerToken                         $customerTokenResource,
        CollectionFactory                     $collection,
        Json                                  $serialize,
        CustomerNotificationCollectionFactory $customerNotification,
        CustomerNotificationFactory           $customerNotificationFactory,
        Helper                                $helper,
        LoggerInterface                       $logger,
        StoreManagerInterface                 $storeManager,
        NotificationQueue                     $notificationQueue,
        NotificationQueueFactory              $notificationQueueFactory,
        NotificationFactory                   $notificationFactory,
        Session                               $checkoutSession,
        Url                                   $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->notificationQueue = $notificationQueue;
        $this->notificationFactory = $notificationFactory;
        $this->notificationQueueFactory = $notificationQueueFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->customerNotificationResource = $customerNotificationResource;
        $this->tokenCollection = $tokenCollection;
        $this->helper = $helper;
        $this->customerTokenResource = $customerTokenResource;
        $this->customerTokenFactory = $customerTokenFactory;
        $this->customerNotificationFactory = $customerNotificationFactory;
        $this->serialize = $serialize;
        $this->collection = $collection->create();
        $this->customerNotification = $customerNotification;
    }

    /**
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->getEnableModule()) {
            return;
        }

        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getOrder();
            if ($order->getState() != $order->getOrigData('state') && $order->getState() === Order::STATE_COMPLETE) {
                $customerId = $order->getCustomerId();
                $storeId = $order->getStoreId();
                $orderStatuses = $order->getStatusHistories();
                $items = $order->getItems();

                $customerToken = $this->tokenCollection->create()
                    ->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('is_active', CustomerTokenModel::IS_ACTIVE)
                    ->addFieldToFilter('status', CustomerTokenModel::STATUS_SUBSCRIBED)
                    ->addFieldToFilter('customer_id', $customerId);

                $notification = $this->getNotificationData();
                $notification['customer_id'] = $customerId;
                $notification['star'] = CustomerNotificationModel::UNSTAR;
                $notification['status'] = CustomerNotificationModel::STATUS_UNREAD;
                $notification['description'] = __("Please leave a review for order %1", $order->getIncrementId());
                $customerNotification = $this->customerNotificationFactory->create();
                $customerNotification->addData([
                    'description' => __("Please leave a review for order %1", $order->getIncrementId()),
                    'customer_id' => $customerId,
                    'star' => CustomerNotificationModel::UNSTAR,
                    'status' => CustomerNotificationModel::STATUS_UNREAD,
                    'notification_type' => 'review_reminders',
                    'redirect_url' => $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $order->getId()]),
                    'additional_data' => $this->serialize->serialize([
                        'order_id' => $order->getEntityId(),
                        'title' => is_array($orderStatuses) ? reset($orderStatuses)->getTitle() : __("Completed"),
                        'order_status' => $order->getStatus(),
                        'first_item_sku' => is_array($items) ? reset($items)->getSku() : ""
                    ])
                ]);
                $this->customerNotificationResource->save($customerNotification);

                $tokenSent = [];
                foreach ($customerToken as $token) {
                    $currentToken = ['token' => $token->getToken(), 'id' => $token->getGuestId()];
                    if (!in_array($currentToken, $tokenSent)) {
                        $this->helper->sendNotificationWithFireBase($notification, $token);
                    }
                    $tokenSent[] = $currentToken;
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getNotificationData()
    {
        $listNotification = $this->collection
            ->addFieldToFilter('is_active', Notification::ACTIVE)
            ->addFieldToFilter('notification_type', Notification::CUSTOM_TYPE)
            ->addFieldToFilter('send_time', 'send_immediately')
            ->addFieldToFilter('condition', 'set_time_after_buy')
            ->addFieldToFilter('description', self::NOTIFICATION_IDENTITY);
        if ($listNotification->getSize() > 0) {
            return $listNotification->getFirstItem()->getData();
        } else {
            return $this->notificationFactory->create()->setData(
                [
                    'send_time'=> 'send_immediately',
                    'notification_type' => Notification::CUSTOM_TYPE,
                    'condition' => 'set_time_after_buy',
                    'is_sent' => Notification::IS_NOT_SENT,
                    'is_active' => 1,
                    'description' => self::NOTIFICATION_IDENTITY,
                    'store_view' => $this->serialize->serialize(['0']),
                    'customer_group' => $this->serialize->serialize(['0'])
                ]
            )->save()->getData();
        }
    }
}
