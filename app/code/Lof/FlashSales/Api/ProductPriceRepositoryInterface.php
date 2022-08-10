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

namespace Lof\FlashSales\Api;

interface ProductPriceRepositoryInterface
{

    /**
     * Save ProductPrice
     * @param \Lof\FlashSales\Api\Data\ProductPriceInterface $productPrice
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Lof\FlashSales\Api\Data\ProductPriceInterface $productPrice
    );

    /**
     * Retrieve ProductPrice
     * @param string $productpriceId
     * @return \Lof\FlashSales\Api\Data\ProductPriceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($productpriceId);

    /**
     * Retrieve ProductPrice matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\FlashSales\Api\Data\ProductPriceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete ProductPrice
     * @param \Lof\FlashSales\Api\Data\ProductPriceInterface $productPrice
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Lof\FlashSales\Api\Data\ProductPriceInterface $productPrice
    );

    /**
     * Delete ProductPrice by ID
     * @param string $productpriceId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($productpriceId);
}
