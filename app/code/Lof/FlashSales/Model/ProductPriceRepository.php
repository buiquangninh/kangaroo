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

use Lof\FlashSales\Api\Data\ProductPriceInterfaceFactory;
use Lof\FlashSales\Api\Data\ProductPriceSearchResultsInterfaceFactory;
use Lof\FlashSales\Api\ProductPriceRepositoryInterface;
use Lof\FlashSales\Model\ResourceModel\ProductPrice as ResourceProductPrice;
use Lof\FlashSales\Model\ResourceModel\ProductPrice\CollectionFactory as ProductPriceCollectionFactory;
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
class ProductPriceRepository implements ProductPriceRepositoryInterface
{

    /**
     * @var ProductPriceCollectionFactory
     */
    protected $productPriceCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var ProductPriceFactory
     */
    protected $productPriceFactory;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var ProductPriceSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var ResourceProductPrice
     */
    protected $resource;

    /**
     * @var ProductPriceInterfaceFactory
     */
    protected $dataProductPriceFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @param ResourceProductPrice $resource
     * @param ProductPriceFactory $productPriceFactory
     * @param ProductPriceInterfaceFactory $dataProductPriceFactory
     * @param ProductPriceCollectionFactory $productPriceCollectionFactory
     * @param ProductPriceSearchResultsInterfaceFactory $searchResultsFactory
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
        ResourceProductPrice $resource,
        ProductPriceFactory $productPriceFactory,
        ProductPriceInterfaceFactory $dataProductPriceFactory,
        ProductPriceCollectionFactory $productPriceCollectionFactory,
        ProductPriceSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->productPriceFactory = $productPriceFactory;
        $this->productPriceCollectionFactory = $productPriceCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProductPriceFactory = $dataProductPriceFactory;
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
        \Lof\FlashSales\Api\Data\ProductPriceInterface $productPrice
    ) {
        /* if (empty($productPrice->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $productPrice->setStoreId($storeId);
        } */

        $productPriceData = $this->extensibleDataObjectConverter->toNestedArray(
            $productPrice,
            [],
            \Lof\FlashSales\Api\Data\ProductPriceInterface::class
        );

        $productPriceModel = $this->productPriceFactory->create()->setData($productPriceData);

        try {
            $this->resource->save($productPriceModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the productPrice: %1',
                $exception->getMessage()
            ));
        }
        return $productPriceModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($productPriceId)
    {
        $productPrice = $this->productPriceFactory->create();
        $this->resource->load($productPrice, $productPriceId);
        if (!$productPrice->getId()) {
            throw new NoSuchEntityException(__('ProductPrice with id "%1" does not exist.', $productPriceId));
        }
        return $productPrice->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->productPriceCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Lof\FlashSales\Api\Data\ProductPriceInterface::class
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
        \Lof\FlashSales\Api\Data\ProductPriceInterface $productPrice
    ) {
        try {
            $productPriceModel = $this->productPriceFactory->create();
            $this->resource->load($productPriceModel, $productPrice->getProductpriceId());
            $this->resource->delete($productPriceModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ProductPrice: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($productPriceId)
    {
        return $this->delete($this->get($productPriceId));
    }
}
