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

namespace Lof\FlashSales\Api\Data;

interface AppliedProductsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get AppliedProducts list.
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface[]
     */
    public function getItems();

    /**
     * Set flashsales_id list.
     * @param \Lof\FlashSales\Api\Data\AppliedProductsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
