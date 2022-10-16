<?php

namespace Magenest\MomoPay\Gateway\Validator;

use Magenest\MomoPay\Gateway\Config\Config;
use Magenest\MomoPay\Helper\MomoHelper;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

abstract class AbstractValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    const RESPONSE = 'response';

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magenest\MomoPay\Helper\Logger\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $_validationSubject;
    /**
     * @var MomoHelper
     */
    protected $helper;


    /**
     * AbstractValidator constructor.
     * @param ResultInterfaceFactory $resultFactory
     * @param \Magento\Payment\Gateway\ConfigInterface $config
     * @param MomoHelper $helper
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        \Magento\Payment\Gateway\ConfigInterface $config,
        MomoHelper $helper
    ) {
        parent::__construct($resultFactory);
        $this->config = $config;
        $this->helper = $helper;
    }

}