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

namespace Lof\FlashSales\Cron;

use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\Category\EventList;
use Lof\FlashSales\Model\FlashSales;

class StatusChecker
{

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var EventList
     */
    protected $categoryFlashSaleList;

    /**
     * @param EventList $categoryFlashSaleList
     * @param Data $helperData
     */
    public function __construct(
        EventList $categoryFlashSaleList,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        $this->categoryFlashSaleList = $categoryFlashSaleList;
    }

    /**
     * Change FlashSales status by date and invalidate cache
     *
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $comingSoonEvent = $this->helperData->getComingSoonEvent();
        $endingSoonEvent = $this->helperData->getEndingSoonEvent();
        $eventCollection = $this->categoryFlashSaleList->getFlashSaleCollection()->addVisibilityFilter();
        /** @var \Lof\FlashSales\Model\FlashSales $flashSale */
        foreach ($eventCollection as $flashSale) {
            if ($flashSale->getFromDate() && $flashSale->getToDate()) {
                $timeStart = (new \DateTime($flashSale->getFromDate()))->getTimestamp();
                // Date already in gmt, no conversion
                $timeEnd = (new \DateTime($flashSale->getToDate()))->getTimestamp();
                // Date already in gmt, no conversion
                $timeNow = gmdate('U');
                if ($timeNow <= $timeStart) {
                    $currentDateDistanceAndStart = round(abs($timeNow - $timeStart) / 86400);
                    if ($comingSoonEvent >= $currentDateDistanceAndStart) {
                        $flashSale->setStatus(FlashSales::STATUS_COMING_SOON)->save();
                    } elseif ($comingSoonEvent < $currentDateDistanceAndStart) {
                        $flashSale->setStatus(FlashSales::STATUS_UPCOMING)->save();
                    }
                } elseif ($timeNow > $timeStart && $timeNow <= $timeEnd) {
                    $currentDateDistanceAndEnd = round(abs($timeNow - $timeEnd) / 86400);
                    if ($endingSoonEvent >= $currentDateDistanceAndEnd) {
                        $flashSale->setStatus(FlashSales::STATUS_ENDING_SOON);
                        $flashSale->save();
                    } elseif ($endingSoonEvent < $currentDateDistanceAndEnd) {
                        $flashSale->setStatus(FlashSales::STATUS_ACTIVE)->save();
                    }
                } else {
                    $flashSale->setStatus(FlashSales::STATUS_ENDED)->save();
                }
            }
        }
    }
}
