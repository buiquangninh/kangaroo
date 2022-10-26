<?php


namespace Magenest\AffiliateMultiLevel\Model\ResourceModel\Account\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class Collection
 * @package Magenest\Affiliate\Model\ResourceModel\Account\Grid
 */
class Collection extends \Magenest\AffiliateMultiLevel\Model\ResourceModel\CustomReport\Collection
{
    /**
     * Report sub-collection class name
     * @var string
     */
    protected $_reportCollection = \Magenest\AffiliateMultiLevel\Model\ResourceModel\Account\Collection::class;
}
