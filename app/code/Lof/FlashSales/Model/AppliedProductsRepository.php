<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */
declare(strict_types=1);

namespace Lof\FlashSales\Model;

use Lof\FlashSales\Api\AppliedProductsRepositoryInterface;
use Lof\FlashSales\Api\Data\AppliedProductsInterfaceFactory;
use Lof\FlashSales\Api\Data\AppliedProductsSearchResultsInterfaceFactory;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts as ResourceAppliedProducts;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory as AppliedProductsCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AppliedProductsRepository implements AppliedProductsRepositoryInterface
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AppliedProductsInterfaceFactory
     */
    protected $dataAppliedProductsFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var AppliedProductsCollectionFactory
     */
    protected $appliedProductsCollectionFactory;

    /**
     * @var AppliedProductsFactory
     */
    protected $appliedProductsFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var AppliedProductsSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var ResourceAppliedProducts
     */
    protected $resource;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param ResourceAppliedProducts $resource
     * @param AppliedProductsFactory $appliedProductsFactory
     * @param AppliedProductsInterfaceFactory $dataAppliedProductsFactory
     * @param AppliedProductsCollectionFactory $appliedProductsCollectionFactory
     * @param AppliedProductsSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceAppliedProducts $resource,
        AppliedProductsFactory $appliedProductsFactory,
        AppliedProductsInterfaceFactory $dataAppliedProductsFactory,
        AppliedProductsCollectionFactory $appliedProductsCollectionFactory,
        AppliedProductsSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->appliedProductsFactory = $appliedProductsFactory;
        $this->appliedProductsCollectionFactory = $appliedProductsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataAppliedProductsFactory = $dataAppliedProductsFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Lof\FlashSales\Api\Data\AppliedProductsInterface $appliedProducts
    ) {
        /* if (empty($appliedProducts->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $appliedProducts->setStoreId($storeId);
        } */

        $appliedProductsData = $this->extensibleDataObjectConverter->toNestedArray(
            $appliedProducts,
            [],
            \Lof\FlashSales\Api\Data\AppliedProductsInterface::class
        );

        $appliedProductsModel = $this->appliedProductsFactory->create()->setData($appliedProductsData);

        try {
            $this->resource->save($appliedProductsModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the appliedProducts: %1',
                $exception->getMessage()
            ));
        }
        return $appliedProductsModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($entityId)
    {
        $appliedProducts = $this->appliedProductsFactory->create();
        $this->resource->load($appliedProducts, $entityId);
        if (!$appliedProducts->getEntityId()) {
            throw new NoSuchEntityException(__('AppliedProducts with id "%1" does not exist.', $entityId));
        }
        return $appliedProducts->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->appliedProductsCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Lof\FlashSales\Api\Data\AppliedProductsInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Lof\FlashSales\Api\Data\AppliedProductsInterface $appliedProducts
    ) {
        try {
            $appliedProductsModel = $this->appliedProductsFactory->create();
            $this->resource->load($appliedProductsModel, $appliedProducts->getEntityId());
            $this->resource->delete($appliedProductsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the AppliedProducts: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param string $entityId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }

    /**
     * {@inheritdoc}
     * @throws CouldNotDeleteException
     */
    public function deleteByIds($appliedProductIds)
    {
        try {
            foreach ($appliedProductIds as $entityId) {
                $this->delete($this->get($entityId));
            }
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the AppliedProducts: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param string $flashSalesId
     * @param string $productId
     * @return bool|mixed|string
     */
    public function hasProduct($flashSalesId, $productId)
    {
        if (!$flashSalesId || !$productId) {
            return false;
        }
        try {
            return $this->resource->hasProduct($flashSalesId, $productId);
        } catch (\Exception $exception) {
            $exception->getMessage();
        }
        return false;
    }
}
