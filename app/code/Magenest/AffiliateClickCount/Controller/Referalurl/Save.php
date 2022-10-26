<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Controller\Referalurl;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Magenest\AffiliateClickCount\Model\AffiliateRepository;
use Magenest\AffiliateClickCount\Api\Data\AffiliateInterface;
use \Magento\Customer\Model\Session as CustomerSession;

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
     * @var AffiliateRepository
     */
    protected AffiliateRepository $affiliateRepository;

    /**
     * @var AffiliateInterface
     */
    protected AffiliateInterface $affiliateInterface;
    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;
    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    public function __construct(
        Context $context,
        LoggerInterface  $logger,
        AffiliateInterface $affiliateInterface,
        CustomerSession $customerSession,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AffiliateRepository $affiliateRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerSession = $customerSession;
        $this->affiliateInterface = $affiliateInterface;
        $this->affiliateRepository = $affiliateRepository;
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
                $customerId = $this->customerSession->getCustomer()->getId();
                if (isset($customerId)) {
                    $affiliateCode = substr($referUrl, strpos($referUrl, '#user_id') + 8);
                    $searchCriteria = $this->searchCriteriaBuilder
                        ->addFilter('affiliate_code', $affiliateCode)
                        ->addFilter('referal_url', $referUrl)
                        ->create();
                    $affiliateRepositoryExits = $this->affiliateRepository->getList($searchCriteria);
                    foreach ($affiliateRepositoryExits->getItems() as $item) {
                        $data[] = $item->getAffiliateId();
                    }

                    if (empty($data)) {
                        $this->affiliateInterface->setCustomerId($customerId);
                        $this->affiliateInterface->setReferalUrl($referUrl);
                        $this->affiliateInterface->setCreatedAt(date("Y-m-d H:i:s"));
                        $this->affiliateInterface->setUpdatedAt(date("Y-m-d H:i:s"));
                        $this->affiliateInterface->setAffiliateCode($affiliateCode);
                        $this->affiliateRepository->save($this->affiliateInterface);
                    }
                }
            }

        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
