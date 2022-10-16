<?php

namespace Magenest\MomoPay\Model\Api\Response;

use Magenest\MomoPay\Api\Response\PaymentInfoInterface;
use Magento\Framework\DataObject;

class PaymentInfo extends DataObject implements PaymentInfoInterface
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

    public function getOrderInfo()
    {
        return $this->getData(self::ORDER_INFO);
    }

    public function setOrderInfo($orderInfo)
    {
        return $this->setData(self::ORDER_INFO, $orderInfo);
    }

    public function getPartnerUserId()
    {
        return $this->getData(self::PARTNER_USER_ID);
    }

    public function setPartnerUserId($partnerUserId)
    {
        return $this->setData(self::PARTNER_USER_ID, $partnerUserId);
    }

    public function getOrderType()
    {
        return $this->getData(self::ORDER_TYPE);
    }

    public function setOrderType($orderType)
    {
        return $this->setData(self::ORDER_TYPE, $orderType);
    }

    public function getTransId()
    {
        return $this->getData(self::TRANS_ID);
    }

    public function setTransId($transId)
    {
        return $this->setData(self::TRANS_ID, $transId);
    }

    public function getResultCode()
    {
        return $this->getData(self::RESULT_CODE);
    }

    public function setResultCode($resultCode)
    {
        return $this->setData(self::RESULT_CODE, $resultCode);
    }

    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    public function getPayType()
    {
        return $this->getData(self::PAY_TYPE);
    }

    public function setPayType($payType)
    {
        return $this->setData(self::PAY_TYPE, $payType);
    }

    public function getResponseTime()
    {
        return $this->getData(self::RESPONSE_TIME);
    }

    public function setResponseTime($responseTime)
    {
        return $this->setData(self::RESPONSE_TIME, $responseTime);
    }

    public function getExtraData()
    {
        return $this->getData(self::EXTRA_DATA);
    }

    public function setExtraData($extraData)
    {
        return $this->setData(self::EXTRA_DATA, $extraData);
    }

    public function getSignature()
    {
        return $this->getData(self::SIGNATURE);
    }

    public function setSignature($signature)
    {
        return $this->setData(self::SIGNATURE, $signature);
    }

    public function getRefundTrans()
    {
        return $this->getData(self::REFUND_TRANS);
    }

    public function setRefundTrans($refundTrans)
    {
        return $this->setData(self::REFUND_TRANS, $refundTrans);
    }
}