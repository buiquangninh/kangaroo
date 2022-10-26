<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Api\Data;

interface AffiliateSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Affiliate list.
     * @return \Magenest\AffiliateClickCount\Api\Data\AffiliateInterface[]
     */
    public function getItems();

    /**
     * Set customer_id list.
     * @param \Magenest\AffiliateClickCount\Api\Data\AffiliateInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

