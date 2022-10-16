<?php

namespace Magenest\MomoPay\Gateway\Request;

use Magenest\MomoPay\Helper\MomoHelper;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

abstract class AbstractDataBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    const PARTNER_CODE = 'partnerCode';
    const PARTNER_NAME = 'partnerName';
    const STORE_ID = 'storeId';
    const REQUEST_ID = 'requestId';
    const AMOUNT = 'amount';
    const ORDER_ID = 'orderId';
    const ORDER_INFO = 'orderInfo';
    const ORDER_GROUP_ID = 'orderGroupId';
    const REDIRECT_URL = 'redirectUrl';
    const IPN_URL = 'ipnUrl';
    const REQUEST_TYPE = 'requestType';
    const EXTRA_DATA = 'extraData';
    const ITEMS = 'items';
    const USER_INFO = 'userInfo';
    const AUTO_CAPTURE = 'autoCapture';
    const LANG = 'lang';
    const SIGNATURE = 'signature';
    const ACCESS_KEY = 'accessKey';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var SubjectReader
     */
    protected $subjectReader;
    /**
     * @var MomoHelper
     */
    protected $momoHelper;

    protected $requestType;

    /**
     * AbstractDataBuilder constructor.
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     * @param MomoHelper $momoHelper
     * @param $requestType
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        ConfigInterface $config,
        SubjectReader $subjectReader,
        MomoHelper $momoHelper,
        $requestType
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->subjectReader = $subjectReader;
        $this->momoHelper = $momoHelper;
        $this->requestType = $requestType;
    }
}