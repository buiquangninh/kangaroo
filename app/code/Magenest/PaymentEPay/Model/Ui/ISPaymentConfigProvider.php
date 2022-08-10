<?php

namespace Magenest\PaymentEPay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use  Magento\Payment\Gateway\ConfigInterface;
use  Magento\Checkout\Model\Session;

/**
 * Class DomesticConfigProvider
 * @package Magenest\OnePay\Model\Ui
 */
class ISPaymentConfigProvider implements ConfigProviderInterface
{
    const CODE = 'vnpt_epay_is';

    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * DomesticConfigProvider constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterFace $config,
        Session $checkoutSession
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                $this::CODE => [
                    'isActive' => $this->config->getValue('active'),
                    'ISData' => $this->checkoutSession->getInstallmentPaymentInformation() ?? ""
                ]
            ]
        ];
    }
}
