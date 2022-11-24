<?php

namespace Magenest\MomoPay\Api\Response;

use Magenest\Zns\Api\Data\TemplateInfoInterface;

interface PaymentInfoInterface
{
    const PARTNER_CODE = 'partnerCode';
    const ORDER_ID = 'orderId';
    const REQUEST_ID = 'requestId';
    const AMOUNT = 'amount';
    const ORDER_INFO = 'orderInfo';
    const PARTNER_USER_ID = 'partnerUserId';
    const ORDER_TYPE = 'orderType';
    const TRANS_ID = 'transId';
    const RESULT_CODE = 'resultCode';
    const MESSAGE = 'message';
    const PAY_TYPE = 'payType';
    const RESPONSE_TIME = 'responseTime';
    const EXTRA_DATA = 'extraData';
    const SIGNATURE = 'signature';
    const REFUND_TRANS = 'refundTrans';

    /**
     * @return $this
     */
    public function getPartnerCode();

    /**
     * @param $partnerCode
     * @return $this
     */
    public function setPartnerCode($partnerCode);

    /**
     * @return $this
     */
    public function getOrderId();

    /**
     * @param $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * @return $this
     */
    public function getRequestId();

    /**
     * @param $requestId
     * @return $this
     */
    public function setRequestId($requestId);

    /**
     * @return $this
     */
    public function getAmount();

    /**
     * @param $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * @return $this
     */
    public function getOrderInfo();

    /**
     * @param $orderInfo
     * @return $this
     */
    public function setOrderInfo($orderInfo);

    /**
     * @return $this
     */
    public function getPartnerUserId();

    /**
     * @param $partnerUserId
     * @return $this
     */
    public function setPartnerUserId($partnerUserId);

    /**
     * @return $this
     */
    public function getOrderType();

    /**
     * @param $orderType
     * @return $this
     */
    public function setOrderType($orderType);

    /**
     * @return $this
     */
    public function getTransId();

    /**
     * @param $transId
     * @return $this
     */
    public function setTransId($transId);

    /**
     * @return $this
     */
    public function getResultCode();

    /**
     * @param $resultCode
     * @return $this
     */
    public function setResultCode($resultCode);

    /**
     * @return $this
     */
    public function getMessage();

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return $this
     */
    public function getPayType();

    /**
     * @param $payType
     * @return $this
     */
    public function setPayType($payType);

    /**
     * @return $this
     */
    public function getResponseTime();

    /**
     * @param $responseTime
     * @return $this
     */
    public function setResponseTime($responseTime);

    /**
     * @return $this
     */
    public function getExtraData();

    /**
     * @param $extraData
     * @return $this
     */
    public function setExtraData($extraData);

    /**
     * @return $this
     */
    public function getSignature();

    /**
     * @param $signature
     * @return $this
     */
    public function setSignature($signature);

    /**
     * @return string[]
     */
    public function getRefundTrans();

    /**
     * @param string[] $refundTrans
     * @return $this
     */
    public function setRefundTrans($refundTrans);
}