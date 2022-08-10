<?php

namespace Magenest\PaymentEPay\Controller\Payment;

use Magenest\PaymentEPay\Api\Data\HandlePaymentInterface;
use Magenest\PaymentEPay\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class IPN
 *
 */
class QRPaymentIPN extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    /**
     * @var Json
     */
    protected $json;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    protected $quoteRepository;

    protected $cartManagement;

    /**
     * @var Data
     */
    protected $helperData;

    protected $_logger;

    /**
     * @var HandlePaymentInterface
     */
    private $handlePaymentInterface;

    protected $commandPool;

    /**
     * Response constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param CartRepositoryInterface $cartRepository
     * @param Data $helperData
     * @param Json $json
     * @param \Magenest\PaymentEPay\Logger\Logger $logger
     * @param CartManagementInterface $cartManagement
     * @param CommandPoolInterface $commandPool
     * @param HandlePaymentInterface $handlePaymentInterface
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        CartRepositoryInterface $cartRepository,
        Data $helperData,
        Json $json,
        \Magenest\PaymentEPay\Logger\Logger $logger,
        CartManagementInterface $cartManagement,
        CommandPoolInterface $commandPool,
        HandlePaymentInterface $handlePaymentInterface
    ) {
        parent::__construct($context);
        $this->commandPool = $commandPool;
        $this->orderFactory    = $orderFactory;
        $this->helperData = $helperData;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $cartRepository;
        $this->cartManagement = $cartManagement;
        $this->json = $json;
        $this->_logger = $logger;
        $this->handlePaymentInterface = $handlePaymentInterface;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $responseParams = $this->getRequest()->getContent();
            $resultToJson = json_decode($responseParams, true);
            $this->_logger->debug($responseParams);
            if (!empty($responseParams)) {
                $check = $this->helperData->checkToken($resultToJson);
                $pos = strpos($resultToJson['invoiceNo'], '-');
                if ($pos !== false) {
                    $resultToJson['invoiceNo'] = substr($resultToJson['invoiceNo'], 0, strpos($resultToJson['invoiceNo'], "-"));
                    $vpcOrderInfo = $resultToJson['invoiceNo'];
                } else {
                    $vpcOrderInfo = $resultToJson['invoiceNo'];
                }
                $this->_logger->debug('### Ipn order: ' . $vpcOrderInfo . '###');
                $order = $this->orderFactory->create()->loadByIncrementId($vpcOrderInfo);
                if (empty($order->getId())) {
                    $this->_logger->debug('### Order not exist: ' . $vpcOrderInfo);
                }
                $amount = $resultToJson['amount'];
                if ($check) {
                    if ($order->getId()
                        && floatval($amount) === floatval($order->getBaseGrandTotal())) {
                        $payment = $order->getPayment();
                        if(!$payment){
                            $this->_logger->debug("--- IPN: Order is in creating process. ---");
                        } else {
                            if (isset($resultToJson['resultCd']) && $resultToJson['resultCd'] === "00_000") {
                                $this->commandPool->get('order')->execute(
                                    [
                                        'order' => $order,
                                        'payment' => $payment,
                                        'response' => $resultToJson,
                                        'amount' => $payment->formatAmount($order->getBaseGrandTotal())
                                    ]
                                );
                                $this->commandPool->get('complete')->execute(
                                    [
                                        'order' => $order,
                                        'amount' => $payment->formatAmount($order->getBaseGrandTotal())
                                    ]
                                );
                                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/success');
                            } else {
                                $this->messageManager->addError(__("Your transaction has been canceled"));
                                return $this->resultRedirectFactory->create()->setPath('checkout/cart');
                            }
                        }
                    }
                } else {
                    $this->messageManager->addError(__("Invalid Merchant Token."));
                    return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');
                }
            } else {
                $this->messageManager->addError(__("Sorry, something went wrong. Please try again later."));
                return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');
            }
        } catch (LocalizedException $exception) {
            $this->messageManager->addError(__("Sorry, something went wrong. Please try again later."));
            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');
        } catch (\Exception $exception) {
            $this->messageManager->addError(__("Sorry, something went wrong. Please try again later."));
            return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure');
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
