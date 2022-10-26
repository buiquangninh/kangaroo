<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Api;

interface AffiliateCountClickRepositoryInterface
{

    /**
     * Save AffiliateCountClick
     * @param \Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface $affiliateCountClick
     * @return \Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface $affiliateCountClick
    );

    /**
     * Retrieve AffiliateCountClick
     * @param string $affiliatecountclickId
     * @return \Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($affiliatecountclickId);

    /**
     * Retrieve AffiliateCountClick matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete AffiliateCountClick
     * @param \Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface $affiliateCountClick
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface $affiliateCountClick
    );

    /**
     * Delete AffiliateCountClick by ID
     * @param string $affiliatecountclickId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($affiliatecountclickId);
}
