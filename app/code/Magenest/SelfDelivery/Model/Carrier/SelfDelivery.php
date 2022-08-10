<?php
namespace Magenest\SelfDelivery\Model\Carrier;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

class SelfDelivery extends AbstractCarrier implements CarrierInterface
{
    const CODE = 'selfDelivery';

    /** @var string */
    protected $_code = self::CODE;

    /** @var bool */
    protected $_isFixed = true;

    /** @var ResultFactory */
    private $rateFactory;

    /** @var MethodFactory */
    private $rateMethodFactory;

    /** @var Session  */
    private $session;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResultFactory $rateFactory,
        MethodFactory $rateMethodFactory,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Session $session,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateFactory = $rateFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->session = $session;
    }

    /**
     * @param DataObject $request
     * @return bool
     */
    public function processAdditionalValidation(DataObject $request)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isShippingLabelsAvailable()
    {
        return false;
    }

    /**
     * @param RateRequest $request
     * @return bool|Result|null
     */
    public function collectRates(RateRequest $request)
    {
        if ($this->session->getInstallmentPaymentValue()) {
            return false;
        }
        $result = $this->rateFactory->create();
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setPrice(0);
        $method->setCost(0);

        return $result->append($method);
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
