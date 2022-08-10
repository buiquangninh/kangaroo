<?php

namespace Lof\FlashSalesGraphQl\Model\Resolver\FlashSales;

use Lof\FlashSales\Api\AppliedProductsRepositoryInterface;
use Lof\FlashSales\Api\Data\AppliedProductsInterface;
use Lof\FlashSales\Api\Data\AppliedProductsSearchResultsInterface;
use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Provides applied product associated flash sales event
 */
class AppliedProducts implements ResolverInterface
{
    /**
     * @var AppliedProductsRepositoryInterface
     */
    private $appliedProductsRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var Stock
     */
    private $stockHelper;

    /**
     * @param AppliedProductsRepositoryInterface $appliedProductsRepository
     * @param CollectionFactory $collectionFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param Stock $stockHelper
     */
    public function __construct(
        AppliedProductsRepositoryInterface $appliedProductsRepository,
        CollectionFactory $collectionFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        sortOrderBuilder $sortOrderBuilder,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        Stock $stockHelper
    ) {
        $this->appliedProductsRepository = $appliedProductsRepository;
        $this->collectionFactory         = $collectionFactory;
        $this->filterBuilder             = $filterBuilder;
        $this->searchCriteriaBuilder     = $searchCriteriaBuilder;
        $this->sortOrderBuilder          = $sortOrderBuilder;
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->stockHelper               = $stockHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        /** @var FlashSalesInterface $flashSale */
        $flashSale = $value['model'];

        $searchResults   = $this->getAppliedProduct($flashSale, $args);
        $appliedProducts = [];

        foreach ($searchResults->getItems() as $appliedProduct) {
            $appliedProducts[] = $this->extractAppliedProduct($appliedProduct);
        }

        $pageSize = $searchResults->getSearchCriteria()->getPageSize();

        return [
            'items' => $appliedProducts,
            'total_count' => $searchResults->getTotalCount(),
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $searchResults->getSearchCriteria()->getCurrentPage(),
                'total_pages' => $pageSize ? ((int)ceil($searchResults->getTotalCount() / $pageSize)) : 0
            ]
        ];
    }

    /**
     * @param FlashSalesInterface $flashSale
     * @param $args
     * @return AppliedProductsSearchResultsInterface
     * @throws LocalizedException
     */
    private function getAppliedProduct($flashSale, $args)
    {
        $appliedProductCollection = $this->collectionFactory->create();

        $appliedProductCollection->addFieldToFilter(
            AppliedProductsInterface::FLASHSALES_ID,
            $flashSale->getFlashsalesId()
        );

        $appliedProductIdsAll     = [];
        $appliedProductIdsInStock = [];

        foreach ($appliedProductCollection->getItems() as $item) {
            $appliedProductIdsAll[$item->getProductId()] = $item->getEntityId();
        }

        $productCollection = $this->productCollectionFactory->create();

        $productCollection->addFieldToFilter('entity_id', ['in' => array_keys($appliedProductIdsAll)]);


        $this->stockHelper->addInStockFilterToCollection($productCollection);

        foreach ($productCollection->getAllIds() as $productIdValid) {
            if (isset($appliedProductIdsAll[$productIdValid])) {
                $appliedProductIdsInStock[] = $appliedProductIdsAll[$productIdValid];
            }
        }

        $filters = [];

        $filters[] = $this->filterBuilder
            ->setField('entity_id')
            ->setConditionType('in')
            ->setValue(array_values($appliedProductIdsInStock))
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilters($filters)
            ->setCurrentPage($args['currentPage'])
            ->setPageSize($args['pageSize'])
            ->create();

        $searchCriteria = $this->buildSortOrders($searchCriteria, $args['sort'] ?? null);

        return $this->appliedProductsRepository->getList($searchCriteria);
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @param array|null $argument
     * @return SearchCriteria
     */
    public function buildSortOrders(
        SearchCriteria $searchCriteria,
        $argument = null
    )
    {
        $sortOrders = [];
        if (is_array($argument)) {
            foreach ($argument as $fieldName => $fieldValue) {
                /** @var SortOrder $sortOrder */
                $sortOrders[] = $this->sortOrderBuilder->setField($fieldName)
                    ->setDirection($fieldValue == 'DESC' ? SortOrder::SORT_DESC : SortOrder::SORT_ASC)
                    ->create();
            }
        }

        if (!count($sortOrders)) {
            $sortOrders[] = $this->sortOrderBuilder->setField('entity_id')
                ->setDirection(SortOrder::SORT_ASC)
                ->create();
        }

        $searchCriteria->setSortOrders($sortOrders);

        return $searchCriteria;
    }

    /**
     * @param AppliedProductsInterface $appliedProduct
     * @return array
     */
    private function extractAppliedProduct($appliedProduct)
    {
        return [
            AppliedProductsInterface::ENTITY_ID => $appliedProduct->getEntityId(),
            AppliedProductsInterface::NAME => $appliedProduct->getName(),
            AppliedProductsInterface::FLASHSALES_ID => $appliedProduct->getFlashsalesId(),
            AppliedProductsInterface::PRICE_TYPE => $appliedProduct->getPriceType(),
            AppliedProductsInterface::PRODUCT_ID => $appliedProduct->getProductId(),
            AppliedProductsInterface::TYPE_ID => $appliedProduct->getTypeId(),
            AppliedProductsInterface::ORIGINAL_PRICE => $appliedProduct->getOriginalPrice(),
            AppliedProductsInterface::FLASH_SALE_PRICE => $appliedProduct->getFlashSalePrice(),
            AppliedProductsInterface::SKU => $appliedProduct->getSku(),
            AppliedProductsInterface::QTY_LIMIT => $appliedProduct->getQtyLimit(),
            AppliedProductsInterface::QTY_ORDERED => $appliedProduct->getQtyOrdered(),
            AppliedProductsInterface::DISCOUNT_AMOUNT => $appliedProduct->getDiscountAmount(),
            AppliedProductsInterface::POSITION => $appliedProduct->getPosition(),
        ];
    }
}
