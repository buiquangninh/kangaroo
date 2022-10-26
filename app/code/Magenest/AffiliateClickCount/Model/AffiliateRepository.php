<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Model;

use Magenest\AffiliateClickCount\Api\AffiliateRepositoryInterface;
use Magenest\AffiliateClickCount\Api\Data\AffiliateInterface;
use Magenest\AffiliateClickCount\Api\Data\AffiliateInterfaceFactory;
use Magenest\AffiliateClickCount\Api\Data\AffiliateSearchResultsInterfaceFactory;
use Magenest\AffiliateClickCount\Model\ResourceModel\Affiliate as ResourceAffiliate;
use Magenest\AffiliateClickCount\Model\ResourceModel\Affiliate\CollectionFactory as AffiliateCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AffiliateRepository implements AffiliateRepositoryInterface
{

    /**
     * @var AffiliateInterfaceFactory
     */
    protected $affiliateFactory;

    /**
     * @var Affiliate
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceAffiliate
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var AffiliateCollectionFactory
     */
    protected $affiliateCollectionFactory;


    /**
     * @param ResourceAffiliate $resource
     * @param AffiliateInterfaceFactory $affiliateFactory
     * @param AffiliateCollectionFactory $affiliateCollectionFactory
     * @param AffiliateSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceAffiliate $resource,
        AffiliateInterfaceFactory $affiliateFactory,
        AffiliateCollectionFactory $affiliateCollectionFactory,
        AffiliateSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->affiliateFactory = $affiliateFactory;
        $this->affiliateCollectionFactory = $affiliateCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(AffiliateInterface $affiliate)
    {
        try {
            $this->resource->save($affiliate);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the affiliate: %1',
                $exception->getMessage()
            ));
        }
        return $affiliate;
    }

    /**
     * @inheritDoc
     */
    public function get($affiliateId)
    {
        $affiliate = $this->affiliateFactory->create();
        $this->resource->load($affiliate, $affiliateId);
        if (!$affiliate->getId()) {
            throw new NoSuchEntityException(__('Affiliate with id "%1" does not exist.', $affiliateId));
        }
        return $affiliate;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->affiliateCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(AffiliateInterface $affiliate)
    {
        try {
            $affiliateModel = $this->affiliateFactory->create();
            $this->resource->load($affiliateModel, $affiliate->getAffiliateId());
            $this->resource->delete($affiliateModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Affiliate: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($affiliateId)
    {
        return $this->delete($this->get($affiliateId));
    }
}

