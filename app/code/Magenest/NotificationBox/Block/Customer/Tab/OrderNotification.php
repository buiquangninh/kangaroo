<?php

namespace Magenest\NotificationBox\Block\Customer\Tab;

use Magenest\NotificationBox\Helper\Helper;
use Magenest\NotificationBox\Model\CustomerNotification as CustomerNotificationModel;
use Magenest\NotificationBox\Model\CustomerNotificationFactory;
use Magenest\NotificationBox\Model\NotificationTypeFactory;
use Magenest\NotificationBox\Model\ResourceModel\CustomerNotification;
use Magenest\NotificationBox\Model\ResourceModel\CustomerNotification\CollectionFactory;
use Magenest\NotificationBox\Model\ResourceModel\NotificationType;
use Magenest\NotificationBox\Model\ResourceModel\NotificationType\CollectionFactory as NotificationTypeCollection;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Customer\Model\Session;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Reports\Model\ResourceModel\Quote\Collection;
use Magento\Store\Model\StoreManagerInterface;

class OrderNotification extends Notification
{
    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Notification constructor.
     * @param Collection $collection
     * @param Context $context
     * @param Json $serialize
     * @param CustomerNotificationFactory $customerNotificationModel
     * @param CustomerNotification $customerNotificationResource
     * @param CollectionFactory $collectionFactory
     * @param Helper $helper
     * @param Session $session
     * @param NotificationType $notificationTypeResource
     * @param NotificationTypeFactory $notificationTypeFactory
     * @param StoreManagerInterface $storeManagerInterface
     * @param NotificationTypeCollection $notificationTypeCollection
     * @param Image $image
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Collection $collection,
        Context $context,
        Json $serialize,
        CustomerNotificationFactory $customerNotificationModel,
        CustomerNotification $customerNotificationResource,
        CollectionFactory $collectionFactory,
        Helper $helper,
        Session $session,
        NotificationType $notificationTypeResource,
        NotificationTypeFactory $notificationTypeFactory,
        StoreManagerInterface $storeManagerInterface,
        NotificationTypeCollection $notificationTypeCollection,
        Image $image,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $collection,
            $context,
            $serialize,
            $customerNotificationModel,
            $customerNotificationResource,
            $collectionFactory,
            $helper,
            $session,
            $notificationTypeResource,
            $notificationTypeFactory,
            $storeManagerInterface,
            $notificationTypeCollection
        );
        $this->imageHelper                  = $image;
        $this->productRepository            = $productRepository;
    }

    public function getOrderNotification()
    {
        $pageLimit = $this->helper->getMaximumNotificationInMyNotificationOnMyAccountPage();
        if ($customerId = $this->getCustomerId()) {
            $page                         = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
            $pageSize                     = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : $pageLimit;
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter(['notification_type', 'notification_type'], [['like' => "order_%"], ['like' => "review_reminders"]])
                ->addFieldToFilter('additional_data', ['neq' => 'NULL']);
            $preSelectCollection = clone $collection;
            $preSelectCollection->setPageSize($pageSize);
            $preSelectCollection->setCurPage($page);
            $preSelectCollection->getSelect()->group('notification_type')->reset('columns')->columns('notification_type');
            $notificationTypes = $preSelectCollection->getColumnValues('notification_type');
            if (!$notificationTypes) {
                return [];
            }
            $collection->addFieldToFilter('notification_type', $notificationTypes);
        }
        if ($collection) {
            $collection = $collection->setOrder('entity_id', 'DESC');
        }
        $connection = $collection->getConnection();
        $mainTable = $collection->getMainTable();
        $collection          = $this->getNotificationByCondition(null, $collection->getData());
        $groupedOrderNotification = [];

        $updateRows = [];
        foreach ($collection as $notification) {
            $groupedOrderNotification[$notification['notification_type']][] = $notification;
            $updateRows[] = $notification['entity_id'];
        }
        try {
            $where = ['entity_id IN (?)' => $updateRows];
            $connection->beginTransaction();

            $connection->update($mainTable, ['status' => CustomerNotificationModel::STATUS_READ], $where);

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }
        return $groupedOrderNotification;
    }

    public function getOrderNotificationTitle($additionalData)
    {
        $additionalData = $this->serialize->unserialize($additionalData);
        return $additionalData['title'];
    }

    public function getOrderUrl($additionalData)
    {
        $additionalData = $this->serialize->unserialize($additionalData);
        return $this->getUrl('sales/order/view', ['order_id' => $additionalData['order_id']]);
    }

    public function getOrderFirstItemImageHtml($additionalData)
    {
        $additionalData = $this->serialize->unserialize($additionalData);
        try {
            $sku = $additionalData['first_item_sku'];
            $product = $this->productRepository->get($sku);
            $url = $this->imageHelper->init($product, 'product_thumbnail_image')->getUrl();
        } catch (\Exception $exception) {
            $url = $this->imageHelper->getDefaultPlaceholderUrl('image');
        }
        return "<img class=\"product-image-photo\" src=\"{$url}\" alt=\"" . (isset($product) ? $product->getName() : "KangarooShopping") . "\">";
    }

    /**
     * @return $this|Notification
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        $type      = $this->getFilteredNotificationTypes();
        $pageLimit = $this->helper->getMaximumNotificationInMyNotificationOnMyAccountPage();
        Template::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Notification'));
        $this->getAllCustomerNotification($type);
        if ($this->notificationCollection) {
            $collection = clone $this->notificationCollection;
            $collection->addFieldToFilter('notification_type', ['like' => 'order_%']);
            $collection->getSelect()->group('notification_type');
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'test'
            )->setAvailableLimit([$pageLimit => $pageLimit])
                ->setShowPerPage(true)->setCollection(
                    $collection
                );
            $this->setChild('pager', $pager);
            $collection->load();
        }
        return $this;
    }
}
