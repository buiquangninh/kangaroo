<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\Smtp\Plugin\Model\Resolver\Products\DataProvider\ProductSearch;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\ProductSearch\ProductCollectionSearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magetop\Smtp\Helper\Data;
use Zend_Log_Exception;

/**
 * Class ProductSearchCriteriaBuilder
 * @package Magetop\Smtp\Plugin\Model\Resolver\Products\DataProvider\ProductSearch
 */
class ProductSearchCriteriaBuilder
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * ProductSearchCriteriaBuilder constructor.
     *
     * @param Data $helperData
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        Data $helperData,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->helperData         = $helperData;
        $this->filterBuilder      = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @param ProductCollectionSearchCriteriaBuilder $subject
     * @param callable $proceed
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return SearchCriteria
     */
    public function aroundBuild(
        ProductCollectionSearchCriteriaBuilder $subject,
        callable $proceed,
        SearchCriteriaInterface $searchCriteria
    ) {
        /** @var SearchCriteria $searchCriteriaForCollection */
        $searchCriteriaForCollection = $proceed($searchCriteria);

        if ($this->helperData->isEnabled()) {
            $filterGroups     = [];
            $filterAttributes = [
                'created_at',
                'news_from_date',
                'news_to_date'
            ];
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    if (in_array($filter->getField(), $filterAttributes, true)) {
                        $time         = strtotime($filter->getValue());
                        $time         = date('Y-m-d H:i:s', $time);
                        $customFilter = $this->filterBuilder
                            ->setField($filter->getField())
                            ->setValue($time)
                            ->setConditionType($filter->getConditionType())
                            ->create();

                        $this->filterGroupBuilder->addFilter($customFilter);
                        $createdAtGroup = $this->filterGroupBuilder->create();
                        $filterGroups[] = $createdAtGroup;
                    }
                }
            }

            if (!empty($filterGroups)) {
                $searchCriteriaForCollection->setFilterGroups($filterGroups);
            }
        }

        return $searchCriteriaForCollection;
    }
}
