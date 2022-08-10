<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\PaymentEPay\Block\Customer;

use Magenest\PaymentEPay\Api\Data\HandlePaymentInterface;
use Magenest\PaymentEPay\Model\Config\Source\Bank;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\Block\Cart\Grid;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;

class InstallmentPayment extends Grid
{
    /**
     * @var HandlePaymentInterface
     */
    protected $handlePaymentInterface;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;
    /**
     * @var Session
     */
    protected $_session;

    /**
     * @var Bank
     */
    protected $bank;
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * InstallmentPayment constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Session $checkoutSession
     * @param Url $catalogUrlBuilder
     * @param Cart $cartHelper
     * @param Context $httpContext
     * @param CollectionFactory $itemCollectionFactory
     * @param JoinProcessorInterface $joinProcessor
     * @param Session $session
     * @param HandlePaymentInterface $handlePaymentInterface
     * @param RedirectFactory $redirectFactory
     * @param Bank $bank
     * @param PriceCurrencyInterface $priceCurrency
     * @param SerializerInterface $serializer
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        Session $checkoutSession,
        Url $catalogUrlBuilder,
        Cart $cartHelper,
        Context $httpContext,
        CollectionFactory $itemCollectionFactory,
        JoinProcessorInterface $joinProcessor,
        Session $session,
        HandlePaymentInterface $handlePaymentInterface,
        RedirectFactory $redirectFactory,
        Bank $bank,
        PriceCurrencyInterface $priceCurrency,
        SerializerInterface $serializer,
        array $data = []
    )
    {
        $this->_session               = $session;
        $this->resultRedirectFactory  = $redirectFactory;
        $this->handlePaymentInterface = $handlePaymentInterface;
        $this->bank                   = $bank;
        $this->priceCurrency          = $priceCurrency;
        $this->serializer             = $serializer;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $itemCollectionFactory,
            $joinProcessor,
            $data
        );
    }

    public function getInstallmentPaymentList($amount)
    {
        return $this->handlePaymentInterface->handleInstallmentPaymentListing($amount);
    }

    public function getInstallmentPaymentInformation()
    {
        if (!empty($this->_checkoutSession->getInstallmentPaymentInformation())) {
            return json_encode($this->_checkoutSession->getInstallmentPaymentInformation(), true);
        } else {
            return "";
        }
    }

    public function getFormAction()
    {
        return 'installmentpayment';
    }

    public function getCartTotal()
    {
        return $this->_checkoutSession->getQuote()->getGrandTotal() ?? $this->_checkoutSession->getQuote()->getSubtotalWithDiscount();
    }

    public function getBankName($code)
    {
        return $this->bank->getBankName($code);
    }

    public function formatPrice($price)
    {
        return $this->priceCurrency->format($price);
    }

    public function getInstallmentOptions()
    {
        $items = $this->getItems();

        if (isset($items[0])) {
            $productInstallmentOptions = $items[0]->getProduct()->getInstallmentOptions();
            try {
                return $this->serializer->unserialize($productInstallmentOptions);
            } catch (\Exception $exception) {

            }
        }
        return [];
    }

    public function getInstallmentFee()
    {
        return $this->_scopeConfig->getValue('payment/vnpt_epay_is/shipping_fee');
    }
}
