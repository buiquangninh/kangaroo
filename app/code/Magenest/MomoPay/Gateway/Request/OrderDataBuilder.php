<?php

namespace Magenest\MomoPay\Gateway\Request;

use Magenest\MomoPay\Gateway\Config\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;

class OrderDataBuilder extends AbstractDataBuilder
{

    /**
     * @param array $buildSubject
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $incrementID = $order->getOrderIncrementId();

        $arrParams = [
            self::PARTNER_CODE => $this->config->getValue(Config::PARTNER_CODE),
            self::ACCESS_KEY => $this->config->getValue(Config::ACCESS_KEY),
            self::REQUEST_ID => $incrementID . '-' . time(),
            self::AMOUNT => strval(round(SubjectReader::readAmount($buildSubject), 0)),
            self::ORDER_ID => $incrementID,
            self::ORDER_INFO => __('Payment for order %1: %2', $this->storeManager->getWebsite()->getName(), $incrementID),
            self::REDIRECT_URL => rtrim($this->urlBuilder->getUrl('momo/payment/response'), "/"),
            self::IPN_URL => rtrim($this->urlBuilder->getUrl('momo/payment/ipn'), "/"),
            self::EXTRA_DATA => 'OrderId:' . $incrementID,
            self::REQUEST_TYPE => $this->requestType,
        ];
        $arrParams[self::SIGNATURE] = $this->momoHelper->generateSignature($arrParams, $this->config->getValue(Config::SECRET_KEY), true);
        $arrParams[self::LANG] = Config::DEFAULT_LANG;
        return $arrParams;
    }
}