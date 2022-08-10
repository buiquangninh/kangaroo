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

namespace Lof\FlashSales\Observer;

use Lof\FlashSales\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderCompleteObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    private $_helperData;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * OrderCompleteObserver constructor.
     *
     * @param Data $_helperData
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Data $_helperData,
        ResourceConnection $resourceConnection
    ) {
        $this->_helperData = $_helperData;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param Observer $observer
     *
     * @return OrderCompleteObserver
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order) {
            foreach ($order->getAllItems() as $item) {
                $connection = $this->resourceConnection->getConnection();
                $appliedProduct = $this->_helperData->getAppliedProductCollection()
                    ->join(
                        ['flashsales' => $connection->getTableName('lof_flashsales_events')],
                        'main_table.flashsales_id = flashsales.flashsales_id',
                        ['status', 'is_active']
                    )
                    ->addFieldToFilter('sku', $item->getSku())
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('status', ['in' => [2, 3]])
                    ->getFirstItem();

                if ($appliedProduct && $appliedProduct->getQtyLimit()) {
                    $qtyOrderedPrevious = $appliedProduct->getQtyOrdered();
                    $appliedProduct->setQtyLimit(
                        $appliedProduct->getQtyLimit() - $item->getQtyOrdered()
                    )->setQtyOrdered(
                        $qtyOrderedPrevious + $item->getQtyOrdered()
                    )->save();
                }
            }
        }

        return $this;
    }
}
