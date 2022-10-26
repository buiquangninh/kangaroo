<?php


namespace Magenest\OrderManagement\Plugin\Order;


use Magento\Sales\Api\Data\OrderExtension;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;


class AddAdditionalExtensionAttribute
{
    /** @var OrderExtensionFactory */
    protected $orderExtensionFactory;

    /**
     * OrderGet constructor.
     *
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        return $this->populateExtensionsAttribute($order);
    }

    public function afterGetList($subject, $result, $searchCriteria)
    {
        foreach ($result->getItems() as $order) {
            $order = $this->populateExtensionsAttribute($order);
        }
        return $result;
    }

    protected function populateExtensionsAttribute($order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getAreaCode()) {
            return $order;
        }

        /** @var OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setAreaCode($order->getAreaCode());
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }
}
