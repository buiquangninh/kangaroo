<?php

namespace Magenest\PaymentEPay\Controller\Customer;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magenest\PaymentEPay\Model\Config\Source\Bank;
use Magento\Framework\View\Asset\Repository;

class InstallmentPayment extends Action
{

    /**
     * @var TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var StateInterface
     */
    protected $_cacheState;

    /**
     * @var Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Session
     */
    protected $checkoutSession;
    /**
     * @var Bank
     */
    protected $bank;
    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @param Context $context
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface $cacheState
     * @param Pool $cacheFrontendPool
     * @param Session $checkoutSession
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context           $context,
        TypeListInterface $cacheTypeList,
        StateInterface    $cacheState,
        Pool              $cacheFrontendPool,
        Session           $checkoutSession,
        Bank              $bank,
        Repository        $assetRepo,
        PageFactory       $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->bank = $bank;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Flush cache storage
     *
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Installment Payment Information'));

        $params = (array)$this->getRequest()->getPost();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $cart = $this->checkoutSession->getQuote();
        $invalid = false;
        if (!$cart->getAllVisibleItems()) {
            $invalid = true;
        }
        foreach ($cart->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            if (!$product->getIsAllowInstallment()) {
                $invalid = true;
            }
        }
        if ($invalid) {
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
        if (!empty($params)) {
            $bankCode = $params["bankCode"];
            $termIs = $params["termIs"];
            $installmentPaymentValue = [
                "bankCode" => $bankCode,
                "termIs" => $termIs
            ];

            $params['bank_name'] = $this->bank->getBankName($bankCode);
            $params['is_active'] = 1;
            $params['bank_image'] = $this->assetRepo->getUrl('images/installment/' . $bankCode . '.png');
            $params['firstAmount'] = $params['firstAmount_'. $params['bankCode'] . '_' . $params['termIs']];
            $params['userFeeIs'] = $params['userFeeIs_'. $params['bankCode'] . '_' . $params['termIs']];
            $params['amount'] = $params['amount_'. $params['bankCode'] . '_' . $params['termIs']];
            $params['amountIs'] = $params['amountIs_'. $params['bankCode'] . '_' . $params['termIs']];
            $params['nextAmount'] = $params['nextAmount_'. $params['bankCode'] . '_' . $params['termIs']];

            $this->checkoutSession->setInstallmentPaymentValue($installmentPaymentValue);
            $this->checkoutSession->setInstallmentPaymentInformation($params);
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultPage->setUrl('/checkout');
            $resultRedirect->setUrl('/checkout');
            return $resultRedirect;
        }
        return $resultPage;
    }
}
