<?php

namespace Magenest\MomoPay\Helper;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class MomoHelper extends Helper
{

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * MomoHelper constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Json $serializer
     * @param DataObjectHelper $dataObjectHelper
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Json $serializer,
        DataObjectHelper $dataObjectHelper,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($context, $logger, $serializer, $dataObjectHelper, $objectManager, $storeManager);
        $this->encryptor = $encryptor;
    }

    /**
     * @return array
     */
    public function getAllMethod()
    {
        return [
            \Magenest\MomoPay\Gateway\Config\Config::METHOD_WALLET,
            \Magenest\MomoPay\Gateway\Config\Config::METHOD_ATM,
            \Magenest\MomoPay\Gateway\Config\Config::METHOD_CC,
            \Magenest\MomoPay\Gateway\Config\Config::METHOD_VTS,
        ];
    }

    /**
     * @param array $params
     * @param string $secretKey
     * @param false $needRequestType
     * @return false|string
     */
    public function generateSignature(array $params = [], string $secretKey = '', bool $needRequestType = false)
    {
        ksort($params);
        $md5HashData = '';
        unset($params['signature']);
        foreach ($params as $key => $value) {
            if ($key === "requestType" && $needRequestType === false)
                continue;
            $md5HashData .= ($key) . "=" . ($value) . "&";
        }
        $md5HashData = rtrim($md5HashData, "&");
        $hash = hash_hmac('SHA256', $md5HashData, $secretKey);

        return $hash;
    }

    /**
     * @param array $params
     * @param string $secretKey
     * @return false|string
     */
    public function generateSignatureResponse(array $params = [], $secretKey = '')
    {
        $data = [
            'requestId' => @$params['requestId'],
            'orderId' => @$params['orderId'],
            'message' => @$params['message'],
            'localMessage' => @$params['localMessage'],
            'payUrl' => @$params['payUrl'],
            'errorCode' => @$params['errorCode'],
            'requestType' => @$params['requestType'],
        ];

        $md5HashData = '';
        foreach ($data as $key => $value) {
            if (!empty($value) || $value === 0)
                $md5HashData .= ($key) . "=" . ($value) . "&";
        }
        $md5HashData = rtrim($md5HashData, "&");
        $hash = hash_hmac('SHA256', $md5HashData, $secretKey);

        return $hash;
    }

    /**
     * Decrypt value
     *
     * @param string $value
     * @return string
     */
    public function decrypt($value)
    {
        return $this->encryptor->decrypt($value);
    }
}