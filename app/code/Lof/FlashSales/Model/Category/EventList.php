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

namespace Lof\FlashSales\Model\Category;

use Lof\FlashSales\Model\FlashSales;
use Lof\FlashSales\Model\ResourceModel\FlashSales\Collection as FlashSalesCollection;
use Magento\Framework\Registry;

class EventList
{

    /**
     * Store categories events
     *
     * @var array
     */
    protected $flashSalesToCategories = null;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * FlashSales collection factory
     *
     * @var \Lof\FlashSales\Model\ResourceModel\FlashSales\CollectionFactory
     */
    protected $flashSalesCollectionFactory;

    /**
     * FlashSales model factory
     *
     * @var \Lof\FlashSales\Model\ResourceModel\FlashSalesFactory
     */
    protected $flashSaleFactory;

    /**
     * EventList constructor.
     * @param Registry $registry
     * @param \Lof\FlashSales\Model\ResourceModel\FlashSales\CollectionFactory $flashSalesCollectionFactory
     * @param \Lof\FlashSales\Model\ResourceModel\FlashSalesFactory $flashSaleFactory
     */
    public function __construct(
        Registry $registry,
        \Lof\FlashSales\Model\ResourceModel\FlashSales\CollectionFactory $flashSalesCollectionFactory,
        \Lof\FlashSales\Model\ResourceModel\FlashSalesFactory $flashSaleFactory
    ) {
        $this->registry = $registry;
        $this->flashSalesCollectionFactory = $flashSalesCollectionFactory;
        $this->flashSaleFactory = $flashSaleFactory;
    }

    /**
     * Get flashsale in store
     *
     * @param int $categoryId
     * @return FlashSales|false|null
     */
    public function getFlashSaleInStore($categoryId)
    {
        if ($this->registry->registry('current_category')
            && $this->registry->registry('current_category')->getId() == $categoryId
        ) {
            // If category already loaded for page, we don't need to load categories tree
            return $this->registry->registry('current_category')->getFlashSales();
        }
        $flashSalesToCategories = $this->getFlashSaleToCategoriesList();

        if (array_key_exists($categoryId, $flashSalesToCategories)) {
            return $flashSalesToCategories[$categoryId];
        }

        return false;
    }

    /**
     * Get array with flash sale association
     *
     * @return array
     */
    public function getFlashSaleToCategoriesList()
    {
        if ($this->flashSalesToCategories === null) {
            $this->flashSalesToCategories = $this->flashSaleFactory->create()->getCategoryIdsWithFlashSale();

            $flashSaleCollection = $this->getFlashSaleCollection(array_keys($this->flashSalesToCategories));
            foreach ($this->flashSalesToCategories as $catId => $flashsalesId) {
                if ($flashsalesId !== null) {
                    $this->flashSalesToCategories[$catId] = $flashSaleCollection->getItemById($flashsalesId);
                }
            }
        }
        return $this->flashSalesToCategories;
    }

    /**
     * Return flash sale collection
     *
     * @param string[] $categoryIds
     * @return FlashSalesCollection
     */
    public function getFlashSaleCollection(array $categoryIds = null)
    {
        /** @var FlashSalesCollection $collection */
        $collection = $this->flashSalesCollectionFactory->create();
        if ($categoryIds !== null) {
            $collection->addFieldToFilter('category_id', ['in' => $categoryIds]);
        }

        return $collection;
    }
}
