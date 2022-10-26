<?php
namespace Magenest\PaymentEPay\Controller\Customer;

use Magenest\PaymentEPay\Api\Data\HandlePaymentInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class InstallmentPaymentAmount extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    protected $handlePaymentInterface;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context          $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface    $cacheState,
        \Magento\Framework\App\Cache\Frontend\Pool     $cacheFrontendPool,
        \Magento\Checkout\Model\Session                $checkoutSession,
        \Magento\Framework\View\Result\PageFactory     $resultPageFactory,
        HandlePaymentInterface                         $handlePaymentInterface,
        PriceCurrencyInterface                         $priceCurrency,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->handlePaymentInterface = $handlePaymentInterface;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        //lấy dữ liệu từ ajax gửi sang
        $response = $this->getRequest()->getParams();
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $this->checkoutSession->setInstallmentPaymentAmount($response['amount']);
        $amount = $response['amount'];
        $response = $this->handlePaymentInterface->handleInstallmentPaymentListing($amount);

        foreach ($response as &$data) {
            foreach ($data['listDuration'] as $key1 => $data1) {
                $data['listDuration'][$key1]['userFeeIs']   = $this->formatPrice($data1['userFeeIs']);
                $data['listDuration'][$key1]['userFeeIsVal']   = $data1['userFeeIs'];
                $data['listDuration'][$key1]['merFeeIs']   = $this->formatPrice($data1['merFeeIs']);
                $data['listDuration'][$key1]['merFeeIsVal']   = $data1['merFeeIs'];
                $data['listDuration'][$key1]['amountIs']   = $this->formatPrice($data1['amountIs']);
                $data['listDuration'][$key1]['amountIsVal']   = $data1['amountIs'];
                $data['listDuration'][$key1]['firstAmount']   = $this->formatPrice($data1['firstAmount']);
                $data['listDuration'][$key1]['firstAmountVal']   = $data1['firstAmount'];
                $data['listDuration'][$key1]['nextAmount']   = $this->formatPrice($data1['nextAmount']);
                $data['listDuration'][$key1]['nextAmountVal']   = $data1['nextAmount'];
                $data['listDuration'][$key1]['amount']   = $this->formatPrice($amount);
                $data['listDuration'][$key1]['amountVal']   = $amount;
//                if($data1['termIs'] == 1) {
//                    unset($data['listDuration'][$key1]);
//                }
            }
        }

        return $resultJson->setData($response);
    }

    private function formatPrice($price)
    {
        return $this->priceCurrency->format($price);
    }
}
