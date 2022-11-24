<?php

namespace Magenest\MomoPay\Api\Response;

interface CreatePaymentResponseInterface
{
    const PARTNER_CODE = 'partnerCode';
    const ORDER_ID = 'orderId';
    const REQUEST_ID = 'requestId';
    const AMOUNT = 'amount';
    const RESPONSE_TIME = 'responseTime';
    const MESSAGE = 'message';
    const RESULT_CODE = 'resultCode';
    const PAY_URL = 'payUrl';

    /**
     * @return mixed
     */
    public function getPartnerCode();

    /**
     * @param $partnerCode
     * @return mixed
     */
    public function setPartnerCode($partnerCode);

    /**
     * @return mixed
     */
    public function getOrderId();

    /**
     * @param $orderId
     * @return mixed
     */
    public function setOrderId($orderId);

    /**
     * @return mixed
     */
    public function getRequestId();

    /**
     * @param $requestId
     * @return mixed
     */
    public function setRequestId($requestId);

    /**
     * @return mixed
     */
    public function getAmount();

    /**
     * @param $amount
     * @return mixed
     */
    public function setAmount($amount);

    /**
     * @return mixed
     */
    public function getResponseTime();

    /**
     * @param $responseTime
     * @return mixed
     */
    public function setResponseTime($responseTime);

    /**
     * @return mixed
     */
    public function getMessage();

    /**
     * @param $message
     * @return mixed
     */
    public function setMessage($message);

    /**
     * @return mixed
     */
    public function getResultCode();

    /**
     * @param $resultCode
     * @return mixed
     */
    public function setResultCode($resultCode);

    /**
     * @return mixed
     */
    public function getPayUrl();

    /**
     * @param $payUrl
     * @return mixed
     */
    public function setPayUrl($payUrl);
}







