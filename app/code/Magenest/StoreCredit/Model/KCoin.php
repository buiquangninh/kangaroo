<?php
namespace Magenest\StoreCredit\Model;

use Magenest\StoreCredit\Helper\Calculation;
use Magenest\StoreCredit\Helper\Data;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class KCoin extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'kcoin';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Cash On Delivery payment block paths
     *
     * @var string
     */
    protected $_formBlockType = \Magenest\StoreCredit\Block\Form\KCoin::class;

    /**
     * Info instructions block path
     *
     * @var string
     */
    protected $_infoBlockType = \Magento\Payment\Block\Info\Instructions::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;

    /** @var bool */
    protected $_canCapture = true;

    /** @var CustomerFactory */
    private $storeCreditCustomer;

    /** @var TransactionFactory */
    private $transactionFactory;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /** @var Calculation */
    private $storeCreditHelper;

    /**
     * @param Calculation $storeCreditHelper
     * @param CustomerRegistry $customerRegistry
     * @param CustomerFactory $storeCreditCustomer
     * @param TransactionFactory $transactionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     */
    public function __construct(
        Calculation                                             $storeCreditHelper,
        CustomerRegistry                                        $customerRegistry,
        \Magenest\StoreCredit\Model\CustomerFactory             $storeCreditCustomer,
        \Magenest\StoreCredit\Model\TransactionFactory          $transactionFactory,
        \Magento\Framework\Model\Context                        $context,
        \Magento\Framework\Registry                             $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory       $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory            $customAttributeFactory,
        \Magento\Payment\Helper\Data                            $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig,
        \Magento\Payment\Model\Method\Logger                    $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection = null,
        array                                                   $data = [],
        DirectoryHelper                                         $directory = null
    ) {
        $this->storeCreditCustomer = $storeCreditCustomer;
        $this->transactionFactory  = $transactionFactory;
        $this->storeCreditHelper   = $storeCreditHelper;
        $this->customerRegistry    = $customerRegistry;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     * @throws LocalizedException
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote === null || $quote->getCustomerIsGuest()) {
            return false;
        }

        $customer = $this->storeCreditCustomer->create()->loadByCustomerId($quote->getCustomer()->getId());
        return parent::isAvailable($quote)
            && $this->storeCreditHelper->isEnabledSpending($quote->getStoreId(), $quote->getCustomer()->getId(), $quote)
            && $customer->getMpCreditBalance() > $quote->getGrandTotal();
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return $this|KCoin
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        parent::capture($payment, $amount);
        if (!$payment->getOrder() || !$payment->getOrder()->getCustomerId()) {
            throw new LocalizedException(__("Can't find related customer/order to capture payment. Please use another method or try again later."));
        }
        $customer = $this->customerRegistry->retrieve($payment->getOrder()->getCustomerId());
        $data     = new DataObject([
           'customer_id_form' => $customer->getId(),
           'customer_email'   => $customer->getEmail(),
           'amount'           => -$amount,
           'order_id'         => $payment->getOrder()->getIncrementId(),
           'increment_id'     => $payment->getOrder()->getIncrementId(),
           'customer_note'    => '',
           'admin_note'       => ''
        ]);
        $this->transactionFactory->create()->createTransaction(Data::ACTION_SPENDING_ORDER, $customer, $data);
        return $this;
    }
}
