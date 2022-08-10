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

namespace Lof\FlashSales\Block\Widget;

use Lof\FlashSales\Model\ResourceModel\FlashSales\Collection;
use Magento\Widget\Block\BlockInterface;

class FlashSales extends \Lof\FlashSales\Block\FlashSales\AbstractEvent implements BlockInterface
{

    /**
     * @var string
     */
    protected $_template = "widget/grid.phtml";

    /**
     * Const
     */
    const DEFAULT_ORDERBY = 'flashsales_id';
    const DEFAULT_SORTORDER = 'DESC';
    const DEFAULT_SHOW_PAGER = false;
    const DEFAULT_EVENTS_PER_PAGE = 5;
    const DEFAULT_EVENTS_COUNT = 10;
    const DEFAULT_COLUMN = 2;

    /**
     * @return bool|mixed
     */
    public function canDisplay()
    {
        return $this->helperData->isEnabled();
    }

    /**
     * @return mixed
     */
    public function getFlashSalesWidgetCollection()
    {
        switch ($this->getData('event_type')) {
            case 'mixed':
                return $this->createMixedCollection();
            case 'active':
                return $this->createActiveCollection();
            case 'comingsoon':
                return $this->createComingCollection();
            case 'endingsoon':
                return $this->createEndingCollection();
            case 'ended':
                return $this->createEndedCollection();
            case 'upcoming':
                return $this->createUpComingCollection();
            default:
                return $this->createMixedCollection();
        }
    }

    /**
     * Retrieve how many events column should be displayed
     *
     * @return int
     */
    public function getColumn()
    {
        if (!$this->hasData('column')) {
            $this->setData('column', self::DEFAULT_COLUMN);
        }

        $columnMapper = array_flip(self::$columnMapper);
        return $columnMapper[$this->getData('column')];
    }

    /**
     * @return Collection
     */
    public function createMixedCollection()
    {
        $collection = $this->flashSalesCollectionFactory->create();

        $collection = $collection->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1))
            ->setOrder($this->getOrderBy(), $this->getSortOrder());

        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect event attribute matches
         * several allowed values from condition    simultaneously
         */
        $collection->distinct(true);

        return $collection;
    }

    /**
     * @return Collection
     */
    public function createUpComingCollection()
    {
        $collection = $this->flashSalesCollectionFactory->create();

        $collection = $collection->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1))
            ->setOrder($this->getOrderBy(), $this->getSortOrder())->addFieldToFilter('status', 0);

        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect event attribute matches
         * several allowed values from condition    simultaneously
         */
        $collection->distinct(true);

        return $collection;
    }

    /**
     * @return Collection
     */
    public function createEndedCollection()
    {
        $collection = $this->flashSalesCollectionFactory->create();

        $collection = $collection->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1))
            ->setOrder($this->getOrderBy(), $this->getSortOrder())->addFieldToFilter('status', 4);

        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect event attribute matches
         * several allowed values from condition    simultaneously
         */
        $collection->distinct(true);

        return $collection;
    }

    /**
     * @return Collection
     */
    public function createActiveCollection()
    {
        $collection = $this->flashSalesCollectionFactory->create();

        $collection = $collection->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1))
            ->setOrder($this->getOrderBy(), $this->getSortOrder())->addFieldToFilter('status', 2);

        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect event attribute matches
         * several allowed values from condition    simultaneously
         */
        $collection->distinct(true);

        return $collection;
    }

    /**
     * @return Collection
     */
    public function createComingCollection()
    {
        $collection = $this->flashSalesCollectionFactory->create();

        $collection = $collection->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1))
            ->setOrder($this->getOrderBy(), $this->getSortOrder())->addFieldToFilter('status', 1);

        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect event attribute matches
         * several allowed values from condition    simultaneously
         */
        $collection->distinct(true);

        return $collection;
    }

    /**
     * @return Collection
     */
    public function createEndingCollection()
    {
        $collection = $this->flashSalesCollectionFactory->create();

        $collection = $collection->setPageSize($this->getPageSize())
            ->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1))
            ->setOrder($this->getOrderBy(), $this->getSortOrder())->addFieldToFilter('status', 3);

        /**
         * Prevent retrieval of duplicate records. This may occur when multiselect event attribute matches
         * several allowed values from condition    simultaneously
         */
        $collection->distinct(true);

        return $collection;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPagerHtml()
    {
        if ($this->showPager() && $this->getFlashSalesWidgetCollection()->getSize() > $this->getEventsPerPage()) {
            if (!$this->pager) {
                $this->pager = $this->getLayout()->createBlock(
                    \Magento\Catalog\Block\Product\Widget\Html\Pager::class,
                    $this->getWidgetPagerBlockName()
                );

                $this->pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getData('page_var_name'))
                    ->setLimit($this->getEventsPerPage())
                    ->setTotalLimit($this->getEventsCount())
                    ->setCollection($this->getFlashSalesWidgetCollection());
            }
            if ($this->pager instanceof \Magento\Framework\View\Element\AbstractBlock) {
                return $this->pager->toHtml();
            }
        }

        return '';
    }

    /**
     * Get widget block name
     *
     * @return string
     */
    private function getWidgetPagerBlockName()
    {
        $pageName = $this->getData('page_var_name');
        $pagerBlockName = 'widget.lofflashsales.list.pager';

        if (!$pageName) {
            return $pagerBlockName;
        }

        return $pagerBlockName . '.' . $pageName;
    }

    /**
     * Retrieve how many events should be displayed on page
     *
     * @return int
     */
    protected function getPageSize()
    {
        return $this->showPager() ? $this->getEventsPerPage() : $this->getEventsCount();
    }

    /**
     * Retrieve how many events should be displayed
     *
     * @return int
     */
    public function getEventsPerPage()
    {
        if (!$this->hasData('events_per_page')) {
            $this->setData('events_per_page', self::DEFAULT_EVENTS_PER_PAGE);
        }

        return $this->getData('events_per_page');
    }

    /**
     * Retrieve how many events should be displayed
     *
     * @return int
     */
    public function getEventsCount()
    {
        if ($this->hasData('events_count')) {
            return $this->getData('events_count');
        }

        if (null === $this->getData('companies_count')) {
            $this->setData('events_count', self::DEFAULT_EVENTS_COUNT);
        }

        return $this->getData('events_count');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }

        return (bool)$this->getData('show_pager');
    }

    /**
     * Get order by
     *
     * @return string
     */
    public function getOrderBy()
    {
        if (!$this->hasData('orderby')) {
            $this->setData('orderby', self::DEFAULT_ORDERBY);
        }

        return $this->getData('orderby');
    }

    /**
     * Get sort order
     *
     * @return string
     */
    public function getSortOrder()
    {
        if (!$this->hasData('sortorder')) {
            $this->setData('sortorder', self::DEFAULT_SORTORDER);
        }

        return $this->getData('sortorder');
    }
}
