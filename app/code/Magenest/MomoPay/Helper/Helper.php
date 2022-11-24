<?php

namespace Magenest\MomoPay\Helper;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Helper extends AbstractHelper
{
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Helper constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Json $serializer
     * @param DataObjectHelper $dataObjectHelper
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        Json $serializer,
        DataObjectHelper $dataObjectHelper,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_logger = $logger;
        $this->serializer = $serializer;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @param $path
     * @param null $scope
     * @param null $scopeId
     * @return mixed
     */
    public function getScopeConfig($path, $scope = null, $scopeId = null)
    {
        $scopeArray = [
            ScopeInterface::SCOPE_STORE,
            ScopeInterface::SCOPE_STORES,
            ScopeInterface::SCOPE_WEBSITE,
            ScopeInterface::SCOPE_WEBSITES
        ];
        if ($scope && in_array($scope, $scopeArray, true)) {
            try {
                if (empty($scopeId)) {
                    if ($scope == ScopeInterface::SCOPE_WEBSITE || $scope == ScopeInterface::SCOPE_WEBSITES) {
                        $website = $this->storeManager->getWebsite();
                        $scopeId = $website->getId();
                    } else {
                        $store = $this->storeManager->getStore();
                        $scopeId = $store->getId();
                    }
                }
                if ($value = $this->scopeConfig->getValue($path, $scope, $scopeId)) {
                    return $value;
                }
            } catch (LocalizedException $e) {
                $this->debug($e);
            }
        }

        return $this->scopeConfig->getValue($path, ScopeConfigInterface::SCOPE_TYPE_DEFAULT);
    }

    /**
     * @param $e
     */
    public function debug($e)
    {
        if ($e instanceof \Throwable) {
            $message = $e->getMessage();
        } else {
            $message = $e;
        }
        $this->_logger->debug($message);
    }

    /**
     * @param $string
     * @return bool|string
     */
    public function serialize($string)
    {
        if ($this->isJson($string)) {
            return $string;
        }

        return $this->serializer->serialize($string);
    }

    /**
     * @param $string
     * @return array|bool|float|int|mixed|string|null
     */
    public function unserialize($string)
    {
        if (!$this->isJson($string)) {

            return is_array($string) ?: [$string];
        }

        return $this->serializer->unserialize($string);
    }

    /**
     * @param $string
     * @return bool
     */
    public function isJson($string)
    {
        if (!empty($string) && !is_array($string)) {
            json_decode($string);

            return (json_last_error() == JSON_ERROR_NONE);
        }

        return false;
    }

    /**
     * @param $data
     * @param $interface
     * @return mixed
     */
    public function createObject($data, $interface = null)
    {
        if ($interface) {
            $object = $this->objectManager->create($interface);
            $this->dataObjectHelper->populateWithArray($object, $data, $interface);
            return $object;
        }

        return new DataObject($data);
    }
}