<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magenest\CustomTableRate\Model;

use Magento\Checkout\Model\Session;
use Magento\SalesRule\Model\Validator;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\DataObject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magenest\CustomTableRate\Model\ResourceModel\CarrierFactory;
use Magenest\Core\Helper\Data as CoreData;

/**
 * Class Standard
 * @package Magenest\CustomTableRate\Model
 */
abstract class Carrier extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var CarrierFactory
     */
    protected $_carrierFactory;

    /**
     * @var CoreData
     */
    protected $_coreData;

    /**
     * {@inheritdoc}
     */
    protected $_isFixed = true;

    /**
     * @var null|array
     */
    protected $_rateRecord = null;

    protected $_session;

    protected $validator;

    /**
     * Carrier constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param CarrierFactory $carrierFactory
     * @param CoreData $coreData
     * @param Session $session
     * @param Validator $validator
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CarrierFactory $carrierFactory,
        CoreData $coreData,
        Session $session,
        Validator $validator,
        array $data = []
    )
    {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_carrierFactory    = $carrierFactory;
        $this->_coreData          = $coreData;
        $this->_session           = $session;
        $this->validator          = $validator;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateResultFactory->create();
        $rate   = $this->getRate($request);

        if (!empty($rate) && $rate['fee'] >= 0) {
            $fee = $rate['fee'];
            if ($this->_session->getInstallmentPaymentValue()) {
                $quote = $this->_session->getQuote();
                foreach ($quote->getAllVisibleItems() as $item) {
                    $product = $item->getProduct();
                    $fee     = $product->getInstallmentShippingFee() ?: $this->_scopeConfig->getValue('payment/vnpt_epay_is/shipping_fee');
                }
                $rate['fee'] = $fee;
                $originalFee = $fee;
            } else {
                $originalFee = $rate['fee'];
                if ($request->getFreeShipping()) {
                    $rate['fee'] = 0;
                }
                $items = $request->getAllItems();
                if (isset($items[0]) && $rate['fee'] > 0) {
                    $quote = $items[0]->getQuote();
                    $address = $quote->getShippingAddress();
                    $address->setShippingDiscountAmount(0);
                    $address->setShippingAmount($rate['fee']);
                    $address->setBaseShippingAmount($rate['fee']);
                    $this->validator->processShippingAmount($address);
                    $discountAmount = $address->getShippingDiscountAmount();
                }
            }

            // TODO: CHANGE SOURCE CODE FROM QUOTE REQUEST
            $method = $this->_createCustomTableRateMethod($rate['fee']);
            $method->setOriginalPrice($originalFee);
            $method->setDiscountPrice($discountAmount ?? 0);
            $result->append($method);
        }

        return $result;
    }

    /**
     * Get rate
     *
     * @param $request
     * @return array|bool
     */
    public function getRate($request)
    {
        if (!$this->_rateRecord) {
            $request->setMethod($this->_code);
            $this->_rateRecord = $this->_carrierFactory->create()->getRate($request);
        }

        return $this->_rateRecord;
    }

    /**
     * Get the method object based on the shipping price and cost
     *
     * @param float $fee
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function _createCustomTableRateMethod($fee)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($fee);
        $method->setCost($fee);

        return $method;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAvailableShipCountries(DataObject $request)
    {
        $rate = $this->getRate($request);
        if (empty($rate) || $rate['fee'] < 0) {
            return false;
        }

        return parent::checkAvailableShipCountries($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('title')];
    }

    /**
     * Get surcharge config
     *
     * @param $field
     * @return false|string
     */
    private function _getSurchargeConfig($field)
    {
        return $this->getConfigData('surcharge/' . $field);
    }
}
