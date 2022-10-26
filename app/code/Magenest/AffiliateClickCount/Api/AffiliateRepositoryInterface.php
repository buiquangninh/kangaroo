<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface AffiliateRepositoryInterface
{

    /**
     * Save Affiliate
     * @param \Magenest\AffiliateClickCount\Api\Data\AffiliateInterface $affiliate
     * @return \Magenest\AffiliateClickCount\Api\Data\AffiliateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Magenest\AffiliateClickCount\Api\Data\AffiliateInterface $affiliate
    );

    /**
     * Retrieve Affiliate
     * @param string $affiliateId
     * @return \Magenest\AffiliateClickCount\Api\Data\AffiliateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($affiliateId);

    /**
     * Retrieve Affiliate matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magenest\AffiliateClickCount\Api\Data\AffiliateSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Affiliate
     * @param \Magenest\AffiliateClickCount\Api\Data\AffiliateInterface $affiliate
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Magenest\AffiliateClickCount\Api\Data\AffiliateInterface $affiliate
    );

    /**
     * Delete Affiliate by ID
     * @param string $affiliateId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($affiliateId);
}

