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

namespace Lof\FlashSales\Ui\Component\Listing\Column;

use Lof\FlashSales\Model\FlashSales;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $statuses = [
            FlashSales::STATUS_UPCOMING => __('UPCOMING'),
            FlashSales::STATUS_COMING_SOON => __('COMING SOON'),
            FlashSales::STATUS_ACTIVE => __('ACTIVE'),
            FlashSales::STATUS_ENDING_SOON => __('ENDING SOON'),
            FlashSales::STATUS_ENDED => __('ENDED')
        ];

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $status = $item[$this->getData('name')];
                $statusMapper = $this->getStatusMapper($status);
                $cssClass = $this->getClassStatus((int)$status);
                $item[$this->getData('name')] = '<span class="' . $cssClass . '">' . $statuses[$statusMapper] .'</span>';
            }
        }

        return $dataSource;
    }

    /**
     * @param $status
     * @return mixed
     */
    public function getStatusMapper($status)
    {
        $statusMapper = array_flip(FlashSales::$statusMapper);
        return $statusMapper[$status];
    }

    /**
     * @param $statusMapper
     * @return string
     */
    private function getClassStatus($statusMapper)
    {
        $classResult = 'loffs-status-box ';

        switch ($statusMapper) {
            case 0: $classResult .= 'loffs-status-upcoming'; break;
            case 1: $classResult .= 'loffs-status-coming'; break;
            case 2: $classResult .= 'loffs-status-active'; break;
            case 3: $classResult .= 'loffs-status-ending'; break;
            default: $classResult = 'loffs-status-ended';
        }

        return $classResult;
    }
}
