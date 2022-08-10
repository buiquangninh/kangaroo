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

namespace Lof\FlashSales\Api;

interface FlashSalesRepositoryInterface
{
    /**
     * Save FlashSales
     * @param \Lof\FlashSales\Api\Data\FlashSalesInterface $flashSales
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Lof\FlashSales\Api\Data\FlashSalesInterface $flashSales
    );

    /**
     * Retrieve FlashSales
     * @param string $flashsalesId
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($flashsalesId);

    /**
     * Retrieve FlashSales matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\FlashSales\Api\Data\FlashSalesSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete FlashSales
     * @param \Lof\FlashSales\Api\Data\FlashSalesInterface $flashSales
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Lof\FlashSales\Api\Data\FlashSalesInterface $flashSales
    );

    /**
     * Delete FlashSales by ID
     * @param string $flashsalesId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($flashsalesId);
}
