<?php
/**
 * Copyright © SalesPerson All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magenest\SalesPerson\Rewrite\Magento\Sales\Model\ResourceModel\Order\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\InventoryShippingAdminUi\Model\ResourceModel\GetAllocatedSourcesForOrder;
use Magento\Sales\Model\ResourceModel\Order;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /** @var GetAllocatedSourcesForOrder */
    private $getAllocatedSourcesForOrder;

    /**
     * Collection constructor.
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param GetAllocatedSourcesForOrder $getAllocatedSourcesForOrder
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param TimezoneInterface|null $timeZone
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        GetAllocatedSourcesForOrder $getAllocatedSourcesForOrder,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_order_grid',
        $resourceModel = Order::class,
        TimezoneInterface $timeZone = null
    ) {
        $this->_authSession = $authSession;
        $this->getAllocatedSourcesForOrder = $getAllocatedSourcesForOrder;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, $timeZone);
    }

    /**
     * @return $this|Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        if ($this->_authSession->getUser()->getRole()->getId() != 1 && $this->_authSession->getUser()->getData("is_salesperson") != 1) {
            $this->getSelect()
                ->joinLeft(
                    ['so' => 'sales_order'],
                    'main_table.entity_id = so.entity_id'
                );
            $this->addFieldToFilter("assigned_to", $this->_authSession->getUser()->getId());
        }
        /*$this->getSelect()
            ->joinLeft(
                ['ss' => 'sales_shipment'],
                'main_table.entity_id = ss.order_id',
                ['shipment_id' => 'ss.entity_id']
            )->joinLeft(
                ['is' => 'inventory_source'],
                'ss.entity_id = '
            );*/
        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        if (!empty($item->getEntityId())) {
            $item->setData('allocated_sources', implode(', ', $this->getAllocatedSourcesForOrder->execute((int)$item->getEntityId())));
        }

        return parent::beforeAddLoadedItem($item);
    }
}
