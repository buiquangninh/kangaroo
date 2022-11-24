<?php

namespace Magenest\MomoPay\Controller\Payment;

use Magenest\MomoPay\Model\Api\Response\PaymentInfo;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class Ipn extends Response implements CsrfAwareActionInterface
{
    public function execute()
    {
        $ipnData = $this->httpRequest->getContent();
        $this->helper->debug('Payment Notify (IPN) Response: ' . var_export($this->httpRequest->getContent(), true));
        $ipnData = $this->helper->unserialize($ipnData);
        /** @var PaymentInfo $response */
        $response = $this->getResponse($ipnData);
        $this->paymentResultHandle->handle($response);
        if (in_array($response->getResultCode(), Response::REPS_CODE_NOT_FINAL)) {
            $order = $this->orderFactory->create()->loadByIncrementId($response->getOrderId());
            $this->paymentResultHandle->sendFailEmail($order);
        }
        return $this->response->setHttpResponseCode(204);
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