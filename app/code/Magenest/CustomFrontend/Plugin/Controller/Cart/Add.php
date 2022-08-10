<?php

namespace Magenest\CustomFrontend\Plugin\Controller\Cart;

use Magento\Checkout\Model\Cart;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Add
 *
 * Used for plugin navigator to checkout index page
 */
class Add
{
    /**
     * @param RequestInterface
     */
    private $request;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var UrlInterface
     */
    protected $_url;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Add constructor.
     * @param RequestInterface $request
     * @param Json $jsonSerializer
     * @param UrlInterface $url
     * @param ManagerInterface $manager
     * @param Cart $cart
     * @param LoggerInterface $logger
     */
    public function __construct(
        RequestInterface $request,
        Json $jsonSerializer,
        UrlInterface $url,
        ManagerInterface $manager,
        Cart $cart,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->jsonSerializer = $jsonSerializer;
        $this->_url = $url;
        $this->messageManager = $manager;
        $this->cart = $cart;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * Function afterExecute
     *
     * @param \Magento\Checkout\Controller\Cart\Add $subject
     * @param $result
     * @return ResponseInterface|ResultInterface
     */
    public function afterExecute(\Magento\Checkout\Controller\Cart\Add $subject, $result)
    {
        try {
            if ($this->request->getParam('buy_now') &&
                $this->cart->getQuote() &&
                !$this->cart->getQuote()->getHasError()
            ) {
                $content['backUrl'] = $this->_url->getUrl('checkout/index/index');
                $subject->getResponse()->representJson($this->jsonSerializer->serialize($content));
                $this->messageManager->getMessages(true);
                return $subject->getResponse();
            } elseif ($this->request->getParam('installment_payment') &&
                $this->cart->getQuote() &&
                !$this->cart->getQuote()->getHasError()
            ) {
                $isHandled = $this->handleProductForInstallmentPayment($this->cart->getQuote());
                if ($isHandled) {
                    $content['backUrl'] = $this->_url->getUrl('epay/customer/installmentpayment');
                    $subject->getResponse()->representJson($this->jsonSerializer->serialize($content));
                    $this->messageManager->getMessages(true);
                    return $subject->getResponse();
                } else {
                    $this->messageManager->addErrorMessage(__('This product doest not allow checkout with installment payment.'));
                }

            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return $result;
    }

    /**
     * Rework item in cart of installment payment from request data
     *
     * @param Quote $quote
     *
     * @return bool
     */
    protected function handleProductForInstallmentPayment($quote)
    {
        try {
            $productId = (int)$this->request->getParam('product');
            if ($productId) {
                $isCartItemRemove = false;
                /** @var \Magento\Quote\Model\Quote\Item $item */
                foreach ($quote->getAllVisibleItems() as $item) {
                    if (
                        $item->getProduct() &&
                        $item->getProduct()->getId() &&
                        (int)$item->getProduct()->getId() !== $productId
                    ) {
                        $isCartItemRemove = true;
                        $quote->removeItem($item->getId());
                    }
                }

                if ($isCartItemRemove) {
                    $quote->save();
                }
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return false;
        }

        return true;
    }
}
