<?php
namespace Magenest\PreOrder\Plugin;

use Magenest\PreOrder\Helper\PreOrderProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\CustomerData\DefaultItem;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class AddCartItemData
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var int */
    private $storeId;

    /** @var PreOrderProduct */
    private $helper;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param PreOrderProduct $helper
     * @param LoggerInterface $logger
     * @throws NoSuchEntityException
     */
    public function __construct(ProductRepositoryInterface $productRepository, StoreManagerInterface $storeManager, PreOrderProduct $helper, LoggerInterface $logger)
    {
        $this->helper = $helper;
        $this->productRepository = $productRepository;

        $this->storeId = $storeManager->getStore()->getId();
    }

    /**
     * @param DefaultItem $subject
     * @param $result
     * @param Item $item
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetItemData(DefaultItem $subject, $result, Item $item)
    {
        $product = $this->productRepository->get($item->getSku(), false, $this->storeId);
        $result['is_preorder'] = (int)$this->helper->isPreOrderProduct($product);
        return $result;
    }
}
