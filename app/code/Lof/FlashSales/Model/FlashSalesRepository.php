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

namespace Lof\FlashSales\Model;

use Lof\FlashSales\Api\Data\FlashSalesInterfaceFactory;
use Lof\FlashSales\Api\Data\FlashSalesSearchResultsInterfaceFactory;
use Lof\FlashSales\Api\FlashSalesRepositoryInterface;
use Lof\FlashSales\Model\ResourceModel\FlashSales as ResourceFlashSales;
use Lof\FlashSales\Model\ResourceModel\FlashSales\CollectionFactory as FlashSalesCollectionFactory;
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
class FlashSalesRepository implements FlashSalesRepositoryInterface
{

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var FlashSalesCollectionFactory
     */
    protected $flashSalesCollectionFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var FlashSalesInterfaceFactory
     */
    protected $dataFlashSalesFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var FlashSalesSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var ResourceFlashSales
     */
    protected $resource;

    /**
     * @var FlashSalesFactory
     */
    protected $flashSalesFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param ResourceFlashSales $resource
     * @param FlashSalesFactory $flashSalesFactory
     * @param FlashSalesInterfaceFactory $dataFlashSalesFactory
     * @param FlashSalesCollectionFactory $flashSalesCollectionFactory
     * @param FlashSalesSearchResultsInterfaceFactory $searchResultsFactory
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
        ResourceFlashSales $resource,
        FlashSalesFactory $flashSalesFactory,
        FlashSalesInterfaceFactory $dataFlashSalesFactory,
        FlashSalesCollectionFactory $flashSalesCollectionFactory,
        FlashSalesSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->flashSalesFactory = $flashSalesFactory;
        $this->flashSalesCollectionFactory = $flashSalesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFlashSalesFactory = $dataFlashSalesFactory;
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
        \Lof\FlashSales\Api\Data\FlashSalesInterface $flashSales
    ) {
        /* if (empty($flashSales->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $flashSales->setStoreId($storeId);
        } */

        $flashSalesData = $this->extensibleDataObjectConverter->toNestedArray(
            $flashSales,
            [],
            \Lof\FlashSales\Api\Data\FlashSalesInterface::class
        );

        $flashSalesModel = $this->flashSalesFactory->create()->setData($flashSalesData);

        try {
            $this->resource->save($flashSalesModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the flashSales: %1',
                $exception->getMessage()
            ));
        }
        return $flashSalesModel->getDataModel();
    }

    /**
     * @param string $flashSalesId
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     * @throws NoSuchEntityException
     */
    public function get($flashSalesId)
    {
        $flashSales = $this->flashSalesFactory->create();
        $this->resource->load($flashSales, $flashSalesId);
        if (!$flashSales->getId()) {
            throw new NoSuchEntityException(__('FlashSales with id "%1" does not exist.', $flashSalesId));
        }
        return $flashSales->getDataModel();
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Lof\FlashSales\Api\Data\FlashSalesSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->flashSalesCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Lof\FlashSales\Api\Data\FlashSalesInterface::class
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
     * @param \Lof\FlashSales\Api\Data\FlashSalesInterface $flashSales
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(
        \Lof\FlashSales\Api\Data\FlashSalesInterface $flashSales
    ) {
        try {
            $flashSalesModel = $this->flashSalesFactory->create();
            $this->resource->load($flashSalesModel, $flashSales->getFlashsalesId());
            $this->resource->delete($flashSalesModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the FlashSales: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param string $flashSalesId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($flashSalesId)
    {
        return $this->delete($this->get($flashSalesId));
    }
}
