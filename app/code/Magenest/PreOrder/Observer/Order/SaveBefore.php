<?php
namespace Magenest\PreOrder\Observer\Order;

use Magenest\PreOrder\Helper\PreOrderProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;

class SaveBefore implements ObserverInterface
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var PreOrderProduct */
    private $helper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param PreOrderProduct $helper
     */
    public function __construct(ProductRepositoryInterface $productRepository, PreOrderProduct $helper)
    {
        $this->helper = $helper;
        $this->productRepository = $productRepository;
    }
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        $stateBefore = $order->getOrigData(OrderInterface::STATE);

        if (!isset($stateBefore)) {
            foreach ($order->getAllVisibleItems() as $item) {
                $product = $this->productRepository->get($item->getSku(), false, $order->getStoreId());
                if ($this->helper->isPreOrderProduct($product)) {
                    $order->setState(Order::STATE_NEW);
                    $order->setStatus('preorder');
                    break;
                }
            }
        }
    }
}
