<?php

namespace Magenest\MomoPay\Gateway\Config;

use Magenest\MomoPay\Helper\MomoHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    /**
     * @var MomoHelper
     */
    protected $helper;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param MomoHelper $helper
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        MomoHelper $helper,
        $methodCode = 'momo',
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN
    ) {
        \Magento\Payment\Gateway\Config\Config::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->helper = $helper;
    }

    //method
    const METHOD_WALLET = 'momo_wallet';
    const METHOD_ATM = 'momo_atm';
    const METHOD_CC = 'momo_cc';
    const METHOD_VTS = 'momo_vts';

    //requestType
    const CAPTURE_WALLET = 'captureWallet';
    const PAY_WITH_ATM = 'payWithATM';
    const PAY_WITH_CC = 'payWithCC';
    const PAY_WITH_VTS = 'payWithVTS';
    const PAY_WITH_METHOD = 'payWithMethod';

    const SANDBOX_MODE = 'sandbox_mode';
    const PARTNER_CODE = 'partner_code';
    const ACCESS_KEY = 'access_key';
    const SECRET_KEY = 'secret_key';
    const API_URL = 'api_url';

    const API_ENDPOINT_CREATE = '/v2/gateway/api/create';
    const API_ENDPOINT_QUERY = '/v2/gateway/api/query';

    const DEFAULT_LANG = 'vi';

    /**
     * @var string[]
     */
    private $_envConfig = [
        self::PARTNER_CODE,
        self::ACCESS_KEY,
        self::SECRET_KEY,
        self::API_URL
    ];

    /**
     * @param string $field
     * @param null $storeId
     * @return mixed|null
     */
    public function getValue($field, $storeId = null)
    {
        $realField = $field;
        if (in_array($field, $this->_envConfig)) {
            if ((bool)parent::getValue(self::SANDBOX_MODE)) {
                $field = 'sandbox_' . $field;
            }
        }
        $value = parent::getValue($field);
        if (in_array($realField, [self::ACCESS_KEY, self::SECRET_KEY])) {
            $value = $this->helper->decrypt($value);
        }
        return $value;
    }
}
