<?php

namespace Magenest\AffiliateOpt\Controller\Account;

use Magenest\AffiliateOpt\Helper\Reports as ReportsHelper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magenest\Affiliate\Controller\Account;
use Magenest\Affiliate\Helper\Data as DataHelper;
use Magenest\Affiliate\Model\AccountFactory;
use Magenest\Affiliate\Model\TransactionFactory;
use Magenest\Affiliate\Model\WithdrawFactory;
use Magenest\AffiliateOpt\Block\Account\Home\CreditChart;

/**
 * Class Credit
 * @package Magenest\AffiliateOpt\Controller\Account
 */
class Credit extends Account
{
    /**
     * @var CreditChart
     */
    private $creditChartBlock;

    /**
     * Credit constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TransactionFactory $transactionFactory
     * @param AccountFactory $accountFactory
     * @param WithdrawFactory $withdrawFactory
     * @param DataHelper $dataHelper
     * @param CustomerSession $customerSession
     * @param Registry $registry
     * @param CreditChart $creditChartBlock
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TransactionFactory $transactionFactory,
        AccountFactory $accountFactory,
        WithdrawFactory $withdrawFactory,
        DataHelper $dataHelper,
        CustomerSession $customerSession,
        Registry $registry,
        CreditChart $creditChartBlock
    ) {
        $this->creditChartBlock = $creditChartBlock;

        parent::__construct(
            $context,
            $resultPageFactory,
            $transactionFactory,
            $accountFactory,
            $withdrawFactory,
            $dataHelper,
            $customerSession,
            $registry
        );
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setData(ReportsHelper::jsonDecode($this->creditChartBlock->creditChartData()));
        return $result;
//        return $this->getResponse()->representJson($this->creditChartBlock->creditChartData());
    }
}
