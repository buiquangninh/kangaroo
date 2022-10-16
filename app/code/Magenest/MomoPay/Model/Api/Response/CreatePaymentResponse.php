<?php

namespace Magenest\MomoPay\Model\Api\Response;

use Magenest\MomoPay\Api\Response\CreatePaymentResponseInterface;
use Magento\Framework\DataObject;

class CreatePaymentResponse extends DataObject implements CreatePaymentResponseInterface
{

    public function getPartnerCode()
    {
        return $this->getData(self::PARTNER_CODE);
    }

    public function setPartnerCode($partnerCode)
    {
        return $this->setData(self::PARTNER_CODE, $partnerCode);
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    public function getRequestId()
    {
        return $this->getData(self::REQUEST_ID);
    }

    public function setRequestId($requestId)
    {
        return $this->setData(self::REQUEST_ID, $requestId);
    }

    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    public function getResponseTime()
    {
        return $this->getData(self::RESPONSE_TIME);
    }

    public function setResponseTime($responseTime)
    {
        return $this->setData(self::RESPONSE_TIME, $responseTime);
    }

    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    public function getResultCode()
    {
        return $this->getData(self::RESULT_CODE);
    }

    public function setResultCode($resultCode)
    {
        return $this->setData(self::RESULT_CODE, $resultCode);
    }

    public function getPayUrl()
    {
        return $this->getData(self::PAY_URL);
    }

    public function setPayUrl($payUrl)
    {
        return $this->setData(self::PAY_URL, $payUrl);
    }
}