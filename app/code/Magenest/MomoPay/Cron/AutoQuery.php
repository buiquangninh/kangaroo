<?php

namespace Magenest\MomoPay\Cron;

use Magenest\MomoPay\Gateway\Config\Config;
use Magenest\MomoPay\Gateway\Request\AbstractDataBuilder;
use Magenest\MomoPay\Helper\MomoHelper;
use Magenest\MomoPay\Model\Api\Response\PaymentInfo;
use Magenest\MomoPay\Model\PaymentResultHandle;
use Magenest\MomoPay\Model\QueryStatusFactory;
use Magenest\MomoPay\Model\ResourceModel\QueryStatus;
use Magenest\Repayment\Helper\RepaymentHelper;

class AutoQuery
{
    const MAX_QUERY_TIMES = 3;
    const TIME_FOR_FIRST_TIME = 5; //minutes
    const TIME_FOR_NEXT_TIME = 5; //minutes

    /**
     * @var QueryStatusFactory
     */
    protected $queryStatusFactory;

    /**
     * @var QueryStatus
     */
    protected $queryStatusResource;

    /**
     * @var QueryStatus\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magenest\MomoPay\Model\ClientRequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var MomoHelper
     */
    protected $helper;

    /**
     * @var PaymentResultHandle
     */
    protected $paymentResultHandle;

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    protected $gatewayConfig;

    /**
     * AutoQuery constructor.
     * @param QueryStatusFactory $queryStatusFactory
     * @param QueryStatus $queryStatusResource
     * @param QueryStatus\CollectionFactory $collectionFactory
     * @param PaymentResultHandle $paymentResultHandle
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magenest\MomoPay\Model\ClientRequestBuilder $requestBuilder
     * @param \Magento\Payment\Gateway\ConfigInterface $gatewayConfig
     * @param MomoHelper $helper
     */
    public function __construct(
        QueryStatusFactory $queryStatusFactory,
        QueryStatus $queryStatusResource,
        QueryStatus\CollectionFactory $collectionFactory,
        PaymentResultHandle $paymentResultHandle,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magenest\MomoPay\Model\ClientRequestBuilder $requestBuilder,
        \Magento\Payment\Gateway\ConfigInterface $gatewayConfig,
        MomoHelper $helper
    ) {
        $this->queryStatusFactory = $queryStatusFactory;
        $this->queryStatusResource = $queryStatusResource;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
        $this->requestBuilder = $requestBuilder;
        $this->helper = $helper;
        $this->paymentResultHandle = $paymentResultHandle;
        $this->gatewayConfig = $gatewayConfig;
    }

    public function execute()
    {
        $expiredTime = $this->helper->getScopeConfig(RepaymentHelper::XML_PATH_MOMO_EXPIRED_TIME) + self::TIME_FOR_FIRST_TIME;
        $firstTimeFilter = $this->dateTime->date('Y-m-d H:i:s', '-' . $expiredTime . ' minutes');
        $nextTimeFilter = $this->dateTime->date('Y-m-d H:i:s', '-' . self:: TIME_FOR_NEXT_TIME . ' minutes');

        $firstCollection = $this->collectionFactory->create()
            ->addFieldToFilter('status', ['eq' => \Magenest\MomoPay\Model\Config\Source\QueryStatus::STATUS_UNSENT])
            ->addFieldToFilter('query_count', 0)
            ->addFieldToFilter('updated_at', ['lteq' => $firstTimeFilter]);

        $nextCollection = $this->collectionFactory->create()
            ->addFieldToFilter('status', ['eq' => \Magenest\MomoPay\Model\Config\Source\QueryStatus::STATUS_FAIL])
            ->addFieldToFilter('query_count', ['lt' => self::MAX_QUERY_TIMES])
            ->addFieldToFilter('updated_at', ['lteq' => $nextTimeFilter]);

        $mergedIds = array_merge($firstCollection->getAllIds(), $nextCollection->getAllIds());
        $collection = $this->collectionFactory->create()->addFieldToFilter('entity_id', ['in' => $mergedIds]);

        foreach ($collection as $item) {
            try {
                $this->doAction($item);
            } catch (\Exception $e) {
                $this->helper->debug($e);
            }
        }

        $this->deleteOldRecord();
    }

    /**
     * @throws \Zend_Http_Client_Exception|\Magento\Framework\Exception\AlreadyExistsException
     */
    public function doAction($item)
    {
        /** @var \Magenest\MomoPay\Model\QueryStatus $item */
        $item->setData('query_count', $item->getData('query_count') + 1);
        $item->setData('status', \Magenest\MomoPay\Model\Config\Source\QueryStatus::STATUS_FAIL);
        $this->queryStatusResource->save($item);
        /** @var PaymentInfo $response */
        $response = $this->makeQueryRequest($item);
        $this->paymentResultHandle->handle($response);
    }

    /**
     * @param $item
     * @return array|bool|float|int|mixed|string|null
     * @throws \Zend_Http_Client_Exception
     */
    public function makeQueryRequest($item)
    {
        $uri = trim($this->getPaymentConfig(Config::API_URL), '/') . Config::API_ENDPOINT_QUERY;
        $incrementId = $item->getOrderId();
        $payload = [
            AbstractDataBuilder::ACCESS_KEY => $this->getPaymentConfig(Config::ACCESS_KEY),
            AbstractDataBuilder::PARTNER_CODE => $this->getPaymentConfig(Config::PARTNER_CODE),
            AbstractDataBuilder::REQUEST_ID => $incrementId . '-' . time(),
            AbstractDataBuilder::ORDER_ID => $incrementId,
        ];
        $payload[AbstractDataBuilder::SIGNATURE] = $this->helper->generateSignature($payload, $this->getPaymentConfig(Config::SECRET_KEY));
        $payload[AbstractDataBuilder::LANG] = Config::DEFAULT_LANG;
        $this->requestBuilder->initClient();
        $this->helper->debug(__('Check Transaction Request: %1', $uri));
        $this->helper->debug(__('Check Transaction Request: %1', print_r($payload, true)));
        return $this->requestBuilder->makeClientRequest('POST', $uri, $payload, \Magenest\MomoPay\Api\Response\PaymentInfoInterface::class);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteOldRecord()
    {
        $timeFilter = $this->dateTime->date('Y-m-d H:i:s', '-30 days');
        $connection = $this->queryStatusResource->getConnection();
        $connection->delete(
            $this->queryStatusResource->getMainTable(),
            ['updated_at <= ?' => $timeFilter]
        );
    }

    /**
     * @param $field
     * @return mixed
     */
    public function getPaymentConfig($field)
    {
        return $this->gatewayConfig->getValue($field);
    }
}