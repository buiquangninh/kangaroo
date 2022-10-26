<?php
/**
 * Copyright © Magenest JSC. All rights reserved.
 *
 * User: leo
 * Date: 23/06/2022
 * Time: 14:42
 */
declare(strict_types=1);

namespace Magenest\PaymentEPay\Plugin\Controller\Index;

use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Init checkout session for payment
 */
class Index
{
    /**
     * @var RedirectInterface
     */
    protected $_redirect;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    protected $messageManager;

    public function __construct(
        RedirectInterface $redirect,
        LoggerInterface   $logger,
        Session           $checkoutSession,
        ManagerInterface $manager
    ) {
        $this->_redirect = $redirect;
        $this->logger = $logger;
        $this->_checkoutSession = $checkoutSession;
        $this->messageManager = $manager;
    }

    /**
     * @param \Magento\Checkout\Controller\Index\Index $subject
     * @return array
     */
    public function beforeExecute(\Magento\Checkout\Controller\Index\Index $subject)
    {
        try {
            $refererUrl = $this->_redirect->getRefererUrl();
            if (!preg_match('/epay\/customer\/installmentpayment\//', $refererUrl)) {
                if ($this->_checkoutSession->getInstallmentPaymentValue()) {
                    $quote = $this->_checkoutSession->getQuote();
                    $quote->removeAllItems();
                    $quote->save();
                    $this->messageManager->addNoticeMessage(__("Your installment actions is invalid. Please add the product and try again"));
                }
                $this->_checkoutSession->setInstallmentPaymentValue(null);
                $this->_checkoutSession->setInstallmentPaymentInformation(null);
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }

        return [];
    }
}
