<?php
/**
 * Copyright © AffiliateClickCount All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\AffiliateClickCount\Api\Data;

interface AffiliateCountClickSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get AffiliateCountClick list.
     * @return \Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface[]
     */
    public function getItems();

    /**
     * Set customer_id list.
     * @param \Magenest\AffiliateClickCount\Api\Data\AffiliateCountClickInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
