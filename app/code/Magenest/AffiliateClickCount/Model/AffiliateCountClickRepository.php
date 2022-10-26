<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Model;

use Magenest\AffiliateClickCount\Api\AffiliateCountClickRepositoryInterface;
use Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface;
use Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterfaceFactory;
use Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickSearchResultsInterfaceFactory;
use Magenest\AffiliateClickCount\Model\ResourceModel\AffiliateCountClick as ResourceAffiliateCountClick;
use Magenest\AffiliateClickCount\Model\ResourceModel\AffiliateCountClick\CollectionFactory as AffiliateCountClickCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class AffiliateCountClickRepository implements AffiliateCountClickRepositoryInterface
{

    /**
     * @var ResourceAffiliateCountClick
     */
    protected $resource;

    /**
     * @var AffiliateCountClick
     */
    protected $searchResultsFactory;

    /**
     * @var AffiliateCountClickInterfaceFactory
     */
    protected $affiliateCountClickFactory;

    /**
     * @var AffiliateCountClickCollectionFactory
     */
    protected $affiliateCountClickCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;


    /**
     * @param ResourceAffiliateCountClick $resource
     * @param AffiliateCountClickInterfaceFactory $affiliateCountClickFactory
     * @param AffiliateCountClickCollectionFactory $affiliateCountClickCollectionFactory
     * @param AffiliateCountClickSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceAffiliateCountClick $resource,
        AffiliateCountClickInterfaceFactory $affiliateCountClickFactory,
        AffiliateCountClickCollectionFactory $affiliateCountClickCollectionFactory,
        AffiliateCountClickSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->affiliateCountClickFactory = $affiliateCountClickFactory;
        $this->affiliateCountClickCollectionFactory = $affiliateCountClickCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(
        AffiliateCountClickInterface $affiliateCountClick
    ) {
        try {
            $this->resource->save($affiliateCountClick);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the affiliateCountClick: %1',
                $exception->getMessage()
            ));
        }
        return $affiliateCountClick;
    }

    /**
     * @inheritDoc
     */
    public function get($affiliateCountClickId)
    {
        $affiliateCountClick = $this->affiliateCountClickFactory->create();
        $this->resource->load($affiliateCountClick, $affiliateCountClickId);
        if (!$affiliateCountClick->getId()) {
            throw new NoSuchEntityException(__('AffiliateCountClick with id "%1" does not exist.', $affiliateCountClickId));
        }
        return $affiliateCountClick;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->affiliateCountClickCollectionFactory->create();

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
    public function delete(
        AffiliateCountClickInterface $affiliateCountClick
    ) {
        try {
            $affiliateCountClickModel = $this->affiliateCountClickFactory->create();
            $this->resource->load($affiliateCountClickModel, $affiliateCountClick->getAffiliatecountclickId());
            $this->resource->delete($affiliateCountClickModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the AffiliateCountClick: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($affiliateCountClickId)
    {
        return $this->delete($this->get($affiliateCountClickId));
    }
}
