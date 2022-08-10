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

namespace Lof\FlashSales\Model\ResourceModel;

use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FlashSales extends \Magento\Rule\Model\ResourceModel\AbstractResource
{

    const EVENT_FROM_PARENT_LAST = 2;

    /**
     * var which represented flashsale collection
     *
     * @var array
     */
    protected $_flashsaleCategories;

    /**
     * Child to parent list
     *
     * @var array
     */
    protected $_childToParentList;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Store manager instance
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Category collection factory
     *
     * @var CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * FlashSales constructor.
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $categoryCollectionFactory
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        StoreManagerInterface $storeManager,
        CollectionFactory $categoryCollectionFactory,
        $connectionName = null
    ) {
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('lof_flashsales_events', 'flashsales_id');
    }

    /**
     * @inheritDoc
     */
    public function getConnection()
    {
        return $this->metadataPool->getMetadata(FlashSalesInterface::class)->getEntityConnection();
    }

    /**
     * Get catalog rules product price for specific date, website and
     * customer group
     *
     * @param $storeId
     * @param $productId
     * @param $dateTime
     * @return float|false
     * @throws LocalizedException
     */
    public function getFlashSalesPrice($storeId, $productId, $dateTime)
    {
        $data = $this->getFlashSalesPrices($storeId, [$productId], $dateTime);
        if (isset($data[$productId])) {
            return $data[$productId];
        }

        return false;
    }

    /**
     * @param $storeId
     * @param $productIds
     * @param $dateTime
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFlashSalesPrices($storeId, $productIds, $dateTime)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['loffs_pp' => $this->getTable('lof_flashsales_productprice')], ['product_id', 'flash_sale_price'])
            ->join(['main_table' => $this->getMainTable()], "main_table.flashsales_id = loffs_pp.flashsales_id", [])
            ->where('main_table.is_active = ?', 1)
            ->where('loffs_pp.store_id = ?', $storeId)
            ->where('loffs_pp.product_id IN(?)', $productIds)
            ->where(
                'main_table.from_date is null or main_table.from_date <= ?',
                $dateTime
            )->where('main_table.to_date is null or main_table.to_date >= ?', $dateTime);

        return $connection->fetchPairs($select);
    }

    /**
     * Add permission index to product model
     *
     * @param Product $product
     * @return $this
     * @throws LocalizedException
     */
    public function addIndexToProduct($product)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['main_table' => $this->getMainTable()],
            [
                FlashSalesInterface::GRANT_EVENT_VIEW,
                FlashSalesInterface::GRANT_EVENT_PRODUCT_PRICE,
                FlashSalesInterface::GRANT_CHECKOUT_ITEMS,
                FlashSalesInterface::GRANT_CHECKOUT_ITEMS_GROUPS,
                FlashSalesInterface::GRANT_EVENT_PRODUCT_PRICE_GROUPS,
                FlashSalesInterface::GRANT_EVENT_VIEW_GROUPS,
                FlashSalesInterface::RESTRICTED_LANDING_PAGE,
                FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG,
                FlashSalesInterface::DISPLAY_CART_MODE,
                FlashSalesInterface::CART_BUTTON_TITLE,
                FlashSalesInterface::MESSAGE_HIDDEN_ADD_TO_CART,
                FlashSalesInterface::FLASHSALES_ID,
                FlashSalesInterface::IS_PRIVATE_SALE,
                FlashSalesInterface::FROM_DATE,
                FlashSalesInterface::TO_DATE
            ]
        )
            ->join(
                ['loffs_pp' => 'lof_flashsales_productprice'],
                'main_table.flashsales_id = loffs_pp.flashsales_id',
                []
            )
            ->where('loffs_pp.store_id = ?', $product->getStoreId())
            ->where('loffs_pp.product_id = ?', $product->getId())
            ->where('main_table.is_active = ?', 1)
            ->order('main_table.sort_order ASC');

        $productData = $connection->fetchRow($select);
        if ($productData) {
            $product->addData($productData);
        }

        return $this;
    }

    /**
     * @param $productPermissions
     * @param $productId
     * @param $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getIndexProductPrivate($productPermissions, $productId, $storeId)
    {
        $connection = $this->getConnection();
        if (!is_array($productId)) {
            $productId = [$productId];
        }

        $select = $connection->select()->from(
            ['main_table' => $this->getMainTable()],
            [
                'grant_event_view',
                'grant_event_view_groups',
                'grant_event_product_price_groups',
                'display_product_mode',
                'is_default_private_config',
                'loffs_pp.product_id'
            ]
        )
            ->join(
                ['loffs_pp' => 'lof_flashsales_productprice'],
                'main_table.flashsales_id = loffs_pp.flashsales_id',
                []
            )
            ->where('loffs_pp.store_id = ?', $storeId)
            ->where('loffs_pp.product_id IN (?)', $productId)
            ->where('main_table.is_active = ?', 1)
            ->where('main_table.is_private_sale = ?', 1)
            ->order('main_table.sort_order ASC');

        $permission = $connection->fetchAll($select);
        if ($permission) {
            $productPermissions->addData($permission);
        }

        return $permission;
    }

    /**
     * Retrieve permission index for category or categories with specified customer group and website id
     *
     * @param int|int[] $categoryId
     * @param int $storeId
     * @return array
     * @throws LocalizedException
     */
    public function getIndexForCategory($categoryId, $storeId = null): array
    {
        $connection = $this->getConnection();
        if (!is_array($categoryId)) {
            $categoryId = [$categoryId];
        }

        $select = $connection->select()
            ->from(['main_table' => $this->getMainTable()])
            ->join(
                ['loffs_s' => $this->getTable('lof_flashsales_store')],
                "main_table.flashsales_id = loffs_s.flashsales_id",
                []
            )
            ->where('main_table.is_active = ?', 1)
            ->where('main_table.category_id IN (?)', $categoryId)
            ->where('main_table.is_private_sale = ?', 1)
            ->where('main_table.is_assign_category = ?', 1)
            ->order('main_table.sort_order ASC');

        if ($storeId !== null) {
            $select->where('loffs_s.store_id IN (?)', [(int) $storeId, 0]);
        }

        return ($storeId !== null)
            ? $connection->fetchAssoc($select)
            : $connection->fetchAll($select);
    }

    /**
     * @param null $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Select_Exception
     */
    public function getCategoryIdsWithFlashSale($storeId = null)
    {
        $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        /* @var $select \Magento\Framework\DB\Select */
        $select = $this->_categoryCollectionFactory->create()->setStoreId(
            $this->storeManager->getStore($storeId)->getId()
        )->addIsActiveFilter()->addPathsFilter(
            \Magento\Catalog\Model\Category::TREE_ROOT_ID . '/' . $rootCategoryId
        )->getSelect();

        $parts = $select->getPart(\Magento\Framework\DB\Select::FROM);

        if (isset($parts['main_table'])) {
            $categoryCorrelationName = 'main_table';
        } else {
            $categoryCorrelationName = 'e';
        }

        $meta = $this->metadataPool->getMetadata(CategoryInterface::class);
        $categoryIdentifierFiled = $meta->getIdentifierField();

        $select->reset(\Magento\Framework\DB\Select::COLUMNS);
        $select->columns([$categoryIdentifierFiled, 'level', 'path'], $categoryCorrelationName);

        $select->joinLeft(
            ['event' => $this->getMainTable()],
            'event.category_id = ' . $categoryCorrelationName . '.' . $categoryIdentifierFiled,
            'flashsales_id'
        )->order(
            $categoryCorrelationName . '.level ASC'
        );

        $this->_flashsaleCategories = $this->getConnection()->fetchAssoc($select);

        if (empty($this->_flashsaleCategories)) {
            return [];
        }

        foreach ($this->_flashsaleCategories as $categoryId => $category) {
            if ($category['flashsales_id'] === null && isset($category['level']) && $category['level'] > 2) {
                $result[$categoryId] = $this->_getFlashSaleFromParent($categoryId, self::EVENT_FROM_PARENT_LAST);
            } else {
                if ($category['flashsales_id'] !== null) {
                    $result[$categoryId] = $category['flashsales_id'];
                } else {
                    $result[$categoryId] = null;
                }
            }
        }

        return $result;
    }

    /**
     * Retrieve Event from close parent
     *
     * @param int $categoryId
     * @param int $flag
     * @return int
     */
    protected function _getFlashSaleFromParent($categoryId, $flag = 2)
    {
        if (isset($this->_childToParentList[$categoryId])) {
            $parentId = $this->_childToParentList[$categoryId];
        }
        if (!isset($parentId)) {
            return null;
        }
        $flashsalesId = null;
        if (isset($this->_flashsaleCategories[$parentId])) {
            $flashsalesId = $this->_flashsaleCategories[$parentId]['flashsales_id'];
        }
        if ($flag == self::EVENT_FROM_PARENT_LAST) {
            if (isset($flashsalesId) && $flashsalesId !== null) {
                return $flashsalesId;
            } else {
                if ($flashsalesId === null) {
                    return $this->_getFlashSaleFromParent($parentId, $flag);
                }
            }
        }
        return null;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Lof\FlashSales\Model\FlashSales|AbstractModel $object
     * @return Select
     * @throws LocalizedException
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(FlashSalesInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), Store::DEFAULT_STORE_ID];

            $select->join(
                ['loffss' => $this->getTable('lof_flashsales_store')],
                $this->getMainTable() . '.' . $linkField . ' = loffss.' . $linkField,
                ['store_id']
            )
                ->where('is_active = ?', 1)
                ->where('loffss.store_id in (?)', $stores)
                ->order('store_id DESC')
                ->limit(1);
        }

        return $select;
    }

    /**
     * Load an object
     *
     * @param \Lof\FlashSales\Model\FlashSales|AbstractModel $object
     * @param mixed $value
     * @param string $field field to load by (defaults to model id)
     * @return $this
     * @throws \Exception
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        $flashSalesId = $this->getFlashSalesId($object, $value, $field);
        if ($flashSalesId) {
            $this->entityManager->load($object, $flashSalesId);
        }
        return $this;
    }

    /**
     * Get block id.
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field
     * @return bool|int|string
     * @throws \Exception
     */
    private function getFlashSalesId(AbstractModel $object, $value, $field = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(FlashSalesInterface::class);
        if (!is_numeric($value) && $field === null) {
            $field = 'flashsales_id';
        } elseif (!$field) {
            $field = $entityMetadata->getIdentifierField();
        }
        $entityId = $value;
        if ($field != $object->getStoreId()) {
            $select = $this->_getLoadSelect($field, $value, $object);
            $result = $this->getConnection()->fetchCol($select);
            $entityId = count($result) ? $result[0] : false;
        }
        return $entityId;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     * @throws LocalizedException
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(FlashSalesInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['loffss' => $this->getTable('lof_flashsales_store')], 'store_id')
            ->join(
                ['loffse' => $this->getMainTable()],
                'loffss.' . $linkField . ' = loffse.' . $linkField,
                []
            )->where('loffse.' . $entityMetadata->getIdentifierField() . ' = :flashsales_id');
        ;

        return $connection->fetchCol($select, ['flashsales_id' => (int)$id]);
    }

    /**
     * Save an object.
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function _afterSave($object)
    {
        $this->entityManager->save($object);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
