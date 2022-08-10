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

interface AppliedProductsRepositoryInterface
{

    /**
     * Save AppliedProducts
     * @param \Lof\FlashSales\Api\Data\AppliedProductsInterface $appliedProducts
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Lof\FlashSales\Api\Data\AppliedProductsInterface $appliedProducts
    );

    /**
     * Retrieve AppliedProducts
     * @param string $entityId
     * @return \Lof\FlashSales\Api\Data\AppliedProductsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve AppliedProducts matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\FlashSales\Api\Data\AppliedProductsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete AppliedProducts
     * @param \Lof\FlashSales\Api\Data\AppliedProductsInterface $appliedProducts
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Lof\FlashSales\Api\Data\AppliedProductsInterface $appliedProducts
    );

    /**
     * Delete AppliedProducts by ID
     * @param string $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);

    /**
     * @param string $flashSalesId
     * @param string $productId
     * @return mixed
     */
    public function hasProduct($flashSalesId, $productId);
}
