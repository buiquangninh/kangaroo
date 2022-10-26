<?php
namespace Magenest\PreOrder\Plugin;

use Magenest\PreOrder\Helper\PreOrderProduct;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Controller\Cart\Add;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class AddToCart
{
    /** @var int|null */
    private $storeId = null;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var Session */
    private $checkoutSession;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ManagerInterface */
    private $messageManager;

    /** @var LoggerInterface */
    private $logger;

    /** @var PreOrderProduct */
    private $helper;

    /**
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     * @param PreOrderProduct $helper
     * @param ManagerInterface $messageManager
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @throws NoSuchEntityException
     */
    public function __construct(
        Session                    $checkoutSession,
        LoggerInterface            $logger,
        PreOrderProduct            $helper,
        ManagerInterface           $messageManager,
        ScopeConfigInterface       $scopeConfig,
        StoreManagerInterface      $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helper            = $helper;
        $this->logger            = $logger;
        $this->storeManager      = $storeManager;
        $this->messageManager    = $messageManager;
        $this->checkoutSession   = $checkoutSession;
        $this->productRepository = $productRepository;

        $this->storeId = $this->storeManager->getStore()->getId();
    }

    /**
     * @param Add $subject
     * @return array
     */
    public function beforeExecute(Add $subject): array
    {
        try {
            $productId = (int)$subject->getRequest()->getParam('product');
            if ($productId) {
                $product = $this->getProductById($productId);
                if ($this->helper->isPreOrderProduct($product) || $this->isCartPreOrder()) {
                    $quote = $this->checkoutSession->getQuote();
                    $quote->removeAllItems()->save();
                }
            }
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__("An unexpected error happened when adding product to cart."));
            $this->logger->critical($e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        return [];
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isCartPreOrder()
    {
        $result = false;
        $quoteItemsCollection = clone $this->checkoutSession->getQuote()->getItemsCollection();
        /** @var Item $item */
        foreach ($quoteItemsCollection as $item) {
            if ($this->helper->isPreOrderProduct($this->getProductById($item->getProductId()))) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * @param $productId
     * @return ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getProductById($productId)
    {
        return $this->productRepository->getById($productId, false, $this->storeId);
    }
}
