<?php

namespace Magenest\MomoPay\Gateway\Http;

use Magenest\MomoPay\Gateway\Config\Config;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Gateway\ConfigInterface;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var string
     */
    private $action;

    /**
     * TransferFactory constructor.
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     * @param string $action
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        $action = null
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
        $this->action = $action;
    }

    /**
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        $uri = trim($this->config->getValue(Config::API_URL), '/') . Config::API_ENDPOINT_CREATE;
        return $this->transferBuilder
            ->setMethod('POST')
            ->setBody($request)
            ->setUri($uri)
            ->build();
    }
}
