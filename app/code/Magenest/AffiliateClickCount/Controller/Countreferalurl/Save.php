<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Controller\Countreferalurl;

use Magenest\Affiliate\Block\Js\Hash;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Magenest\AffiliateClickCount\Model\AffiliateCountClickRepository;
use Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface;
use \Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magenest\Affiliate\Model\ResourceModel\Account\Collection as AccountCollection;

class Save extends \Magento\Framework\App\Action\Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var Json
     */
    protected $serializer;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var Http
     */
    protected $http;
    /**
     * @var AffiliateCountClickRepository
     */
    protected AffiliateCountClickRepository $affiliateCountClickRepository;

    /**
     * @var AffiliateCountClickInterface
     */
    protected AffiliateCountClickInterface $affiliateCountClickInterface;
    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var CookieManagerInterface
     */
    protected CookieManagerInterface $cookieManager;
    /**
     * @var AccountCollection
     */
    protected AccountCollection $accountCollection;

    /**
     * @param Context $context
     * @param LoggerInterface $logger
     * @param AffiliateCountClickInterface $affiliateCountClickInterface
     * @param CustomerSession $customerSession
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AffiliateCountClickRepository $affiliateCountClickRepository
     */
    public function __construct(
        Context $context,
        LoggerInterface  $logger,
        AffiliateCountClickInterface $affiliateCountClickInterface,
        CustomerSession $customerSession,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CookieManagerInterface $cookieManager,
        AccountCollection $accountCollection,
        AffiliateCountClickRepository $affiliateCountClickRepository
    ) {
        $this->accountCollection = $accountCollection;
        $this->cookieManager = $cookieManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerSession = $customerSession;
        $this->affiliateCountClickInterface = $affiliateCountClickInterface;
        $this->affiliateCountClickRepository = $affiliateCountClickRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        try {
            $referUrl = $this->getRequest()->getParam('referalUrl');
            if (isset($referUrl)) {
                $data = [];
                $guest = $this->cookieManager->getCookie(Hash::CUSTOMER_GUEST);
                $customerId = $this->customerSession->getCustomer()->getId() ? $this->customerSession->getCustomer()->getId()  : $guest;
                $affiliateCode = substr($referUrl, strpos($referUrl, '#user_id') + 8);
                $checkReferUrlLink = $this->accountCollection->addFieldToFilter('code', $affiliateCode)->getLastItem();
                if(!empty($checkReferUrlLink->getData())){
                    if (isset($customerId)) {
                        $searchCriteria = $this->searchCriteriaBuilder
                            ->addFilter('affiliate_code', $affiliateCode)
                            ->addFilter('referal_url', $referUrl)
                            ->addFilter('customer_id', $customerId)
                            ->create();
                        $affiliateRepositoryExits = $this->affiliateCountClickRepository->getList($searchCriteria);
                        foreach ($affiliateRepositoryExits->getItems() as $item) {
                            $data[] = $item->getAffiliatecountclickId();
                        }

                        if (empty($data)) {
                            $this->affiliateCountClickInterface->setCustomerId($customerId);
                            $this->affiliateCountClickInterface->setReferalUrl($referUrl);
                            $this->affiliateCountClickInterface->setCreatedAt(date("Y-m-d H:i:s"));
                            $this->affiliateCountClickInterface->setUpdatedAt(date("Y-m-d H:i:s"));
                            $this->affiliateCountClickInterface->setAffiliateCode($affiliateCode);
                            $this->affiliateCountClickRepository->save($this->affiliateCountClickInterface);
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
