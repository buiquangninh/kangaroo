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

namespace Lof\FlashSales\Model;

use Lof\FlashSales\Api\Data\FlashSalesExtensionInterface;
use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts;
use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Lof\FlashSales\Api\Data\FlashSalesInterfaceFactory;
use Lof\FlashSales\Model\ResourceModel\FlashSales\Collection;
use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as RuleCollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\Combine;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Rule\Model\AbstractModel;
use Magento\Store\Model\Store;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;

/**
 * FlashSale model
 *
 * @method FlashSales setStoreId(int $storeId)
 * @method int getStoreId()
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FlashSales extends AbstractModel implements FlashSalesInterface, IdentityInterface
{

    /**
     * Flash Sale statuses
     */
    const STATUS_UPCOMING = 'upcoming';

    const STATUS_COMING_SOON = 'comingsoon';

    const STATUS_ACTIVE = 'active';

    const STATUS_ENDING_SOON = 'endingsoon';

    const STATUS_ENDED = 'ended';

    const CACHE_TAG = 'lof_flashsales_events';

    /**#@+
     * Available events
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**#@+
     * Available event Types
     */
    const TYPE_PRIVATE_SALES = 1;
    const TYPE_FLASH_SALES = 0;

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * @var string
     */
    protected $_eventPrefix = 'lof_flashsales_events';

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var FlashSalesInterfaceFactory
     */
    protected $flashsalesDataFactory;

    /**
     * @var CombineFactory
     */
    protected $_combineFactory;

    /**
     * @var CollectionFactory
     */
    protected $_actionCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var array
     */
    protected $_productIds = [];

    /**
     * @var Iterator
     */
    protected $resourceIterator;

    /**
     * @var array
     */
    protected $_productAttributes = [];

    /**
     * @var Product
     */
    protected $_productResource;

    /**
     * @var Data
     */
    private $_helperData;

    /**
     * @var ConfigurableType
     */
    private $configurableType;

    /**
     * @var GroupedType
     */
    private $groupedType;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var array
     */
    private $cacheParentIdsByChild = [];

    /**
     * Status mapper
     *
     * @var array
     */
    public static $statusMapper = [
        \Lof\FlashSales\Model\FlashSales::STATUS_UPCOMING => 0,
        \Lof\FlashSales\Model\FlashSales::STATUS_COMING_SOON => 1,
        \Lof\FlashSales\Model\FlashSales::STATUS_ACTIVE => 2,
        \Lof\FlashSales\Model\FlashSales::STATUS_ENDING_SOON => 3,
        \Lof\FlashSales\Model\FlashSales::STATUS_ENDED => 4
    ];

    /**
     * FlashSales constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FlashSalesInterfaceFactory $flashsalesDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param ResourceModel\FlashSales $resource
     * @param ResourceModel\FlashSales\Collection $resourceCollection
     * @param CombineFactory $combineFactory
     * @param RuleCollectionFactory $actionCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Iterator $resourceIterator
     * @param ProductFactory $productFactory
     * @param AppliedProducts $productResource
     * @param Data $helperData
     * @param ConfigurableType $configurableType
     * @param GroupedType $groupedType
     * @param DateTime $dateTime
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FlashSalesInterfaceFactory $flashsalesDataFactory,
        DataObjectHelper $dataObjectHelper,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        ResourceModel\FlashSales $resource,
        Collection $resourceCollection,
        CombineFactory $combineFactory,
        RuleCollectionFactory $actionCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        Iterator $resourceIterator,
        ProductFactory $productFactory,
        AppliedProducts $productResource,
        Data $helperData,
        ConfigurableType $configurableType,
        GroupedType $groupedType,
        DateTime $dateTime,
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        $this->groupedType = $groupedType;
        $this->configurableType = $configurableType;
        $this->_helperData = $helperData;
        $this->_productResource = $productResource;
        $this->resourceIterator = $resourceIterator;
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->flashsalesDataFactory = $flashsalesDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_combineFactory = $combineFactory;
        $this->_actionCollectionFactory = $actionCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Lof\FlashSales\Model\ResourceModel\FlashSales::class);
        $this->setIdFieldName('flashsales_id');
    }

    /**
     * @return $this|FlashSales
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($this->dateTime->formatDate(true));
        }
        $this->setUpdatedAt($this->dateTime->formatDate(true));
        return $this;
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Validate time data for event
     *
     * @return true|array - returns true if validation passed successfully. Array with error
     * description otherwise
     */
    public function validateTime()
    {
        $this->resolveDate($this, 'from_date');
        $this->resolveDate($this, 'to_date');
        $dateStartUnixTime = strtotime($this->getData('from_date'));
        $dateEndUnixTime = strtotime($this->getData('to_date'));
        $dateIsOk = $dateEndUnixTime > $dateStartUnixTime;
        if ($dateIsOk) {
            return true;
        } else {
            return [__('Please make sure the end date follows the start date.')];
        }
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $dateIdentifier
     * @return void
     */
    private function resolveDate(\Magento\Framework\Model\AbstractModel $object, $dateIdentifier)
    {
        $date = $object->getData($dateIdentifier);
        if ($date instanceof \DateTimeInterface) {
            $object->setData($dateIdentifier, $date->format('Y-m-d H:i:s'));
        } elseif (!is_string($date) || empty($date)) {
            $object->setData($dateIdentifier, null);
        }
    }

    /**
     * Apply event status by date
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function applyStatusByDates()
    {
        $comingSoonEvent = $this->_helperData->getComingSoonEvent();
        $endingSoonEvent = $this->_helperData->getEndingSoonEvent();

        $this->resolveDate($this, 'from_date');
        $this->resolveDate($this, 'to_date');
        if ($this->getFromDate() && $this->getToDate()) {
            $timeStart = (new \DateTime($this->getFromDate()))->getTimestamp();
            // Date already in gmt, no conversion
            $timeEnd = (new \DateTime($this->getToDate()))->getTimestamp();
            // Date already in gmt, no conversion
            $timeNow = gmdate('U');
            if ($timeNow <= $timeStart) {
                $currentDateDistanceAndStart = round(abs($timeNow - $timeStart) / 86400);
                if ($comingSoonEvent >= $currentDateDistanceAndStart) {
                    $this->setStatus(self::STATUS_COMING_SOON);
                } elseif ($comingSoonEvent < $currentDateDistanceAndStart) {
                    $this->setStatus(self::STATUS_UPCOMING);
                }
            } elseif ($timeNow > $timeStart && $timeNow <= $timeEnd) {
                $currentDateDistanceAndEnd = round(abs($timeNow - $timeEnd) / 86400);
                if ($endingSoonEvent >= $currentDateDistanceAndEnd) {
                    $this->setStatus(self::STATUS_ENDING_SOON);
                } elseif ($endingSoonEvent < $currentDateDistanceAndEnd) {
                    $this->setStatus(self::STATUS_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_ENDED);
            }
        }
        return $this;
    }

    /**
     * Get status column value
     * Set status column if it wasn't set
     *
     * @return string
     */
    public function getStatus()
    {
        if (!$this->hasData('status')) {
            $this->applyStatusByDates();
        }
        $statusMapper = array_flip(self::$statusMapper);
        return $statusMapper[$this->getData('status')];
    }

    /**
     * Set status column
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData('status', self::$statusMapper[$status]);
        return $this;
    }

    /**
     * Retrieve category ids with events
     *
     * @param int|string|Store $storeId
     * @return array
     */
    public function getCategoryIdsWithFlashSale($storeId = null)
    {
        return $this->_getResource()->getCategoryIdsWithFlashSale($storeId);
    }

    /**
     * @return Combine|\Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }

    /**
     * @return \Magento\CatalogRule\Model\Rule\Action\Collection|\Magento\Rule\Model\Action\Collection
     */
    public function getActionsInstance()
    {
        return $this->_actionCollectionFactory->create();
    }

    /**
     * @param null $attributes
     * @return array
     */
    public function getMatchingProductIds($attributes = null)
    {
        $this->_productIds = [];
        $this->setCollectedAttributes([]);
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $this->getConditions()->collectValidatedAttributes($productCollection);
        if ($attributes && !is_array($attributes)) {
            $attributes = [$attributes];
        }
        $this->_productAttributes = $attributes;
        $this->resourceIterator->walk(
            $productCollection->addAttributeToSelect($this->_productAttributes, 'left')->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
                'attributes' => $this->getCollectedAttributes(),
                'product' => $this->productFactory->create()
            ]
        );
        return $this->_productIds;
    }

    /**
     * @param $args
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $product->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $result = $this->getConditions()->validate($product);
        if ($result) {
            switch ($product->getTypeId()) {
                case \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE:
                    $configurableProductGroupId = $this->getParentIdsByChild($product->getId());
                    if (is_array($configurableProductGroupId) && $configurableProductGroupId) {
                        $configurableProductGroupId[] = $product->getId();
                        foreach ($configurableProductGroupId as $childId) {
                            $relatedProduct = $this->productFactory->create()->load($childId);
                            $this->mappingProductData($relatedProduct);
                        }
                    }
                    $groupProductId = $this->groupedType->getParentIdsByChild($product->getId());
                    if (is_array($groupProductId) && $groupProductId) {
                        $groupProductId[] = $product->getId();
                        foreach ($groupProductId as $childId) {
                            $relatedProduct = $this->productFactory->create()->load($childId);
                            $this->mappingProductData($relatedProduct);
                        }
                    }
                    break;
                case Configurable::TYPE_CODE:
                    $productTypeInstance = $product->getTypeInstance();
                    $relatedProducts = $productTypeInstance->getUsedProducts($product);
                    foreach ($relatedProducts as $relatedProduct) {
                        $this->mappingProductData($relatedProduct);
                    }
                    break;
                case Grouped::TYPE_CODE:
                case Type::TYPE_CODE:
                    if ($product->getTypeId() == Type::TYPE_CODE
                        && $this->_productResource->isFixedPriceType($product->getId())
                    ) {
                        break;
                    }
                    $childrenGroupIds = $product->getTypeInstance()->getChildrenIds($product->getId(), false);
                    $childrenIds = array_merge([], ...$childrenGroupIds);
                    $children = $this->productCollectionFactory->create()
                        ->addAttributeToSelect($this->_productAttributes, 'left')
                        ->addFieldToFilter('entity_id', ['in' => $childrenIds]);
                    foreach ($children as $child) {
                        $this->mappingProductData($child);
                    }
                    break;
                default:
                    break;
            }
            $this->mappingProductData($product);
        }
    }

    /**
     * Get parent ids by child with cache use
     *
     * @param int $childId
     * @return array
     */
    private function getParentIdsByChild($childId)
    {
        if (!isset($this->cacheParentIdsByChild[$childId])) {
            $this->cacheParentIdsByChild[$childId] = $this->configurableType->getParentIdsByChild($childId);
        }

        return $this->cacheParentIdsByChild[$childId];
    }

    /**
     * @param $product
     */
    private function mappingProductData($product)
    {
        if ($product && $product->getId()) {
            foreach ($this->_productAttributes as $attribute) {
                $productData[$attribute] = $product->getData($attribute);
                $this->_productIds[$product->getId()] = $productData;
            }
        }
    }

    /**
     * Retrieve flash sales model with flash sales data
     * @return FlashSalesInterface
     */
    public function getDataModel()
    {
        $flashsalesData = $this->getData();

        $flashsalesDataObject = $this->flashsalesDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $flashsalesDataObject,
            $flashsalesData,
            FlashSalesInterface::class
        );

        return $flashsalesDataObject;
    }

    /**
     * Getter for conditions field set ID
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param $product
     * @return $this
     */
    public function addIndexToProduct($product)
    {
        $this->getResource()->addIndexToProduct($product);
        return $this;
    }

    /**
     * @param $productPermissions
     * @param $productId
     * @param $storeId
     * @return $this
     */
    public function getIndexProductPrivate($productPermissions, $productId, $storeId)
    {
        $this->getResource()->getIndexProductPrivate($productPermissions, $productId, $storeId);
        return $this;
    }

    /**
     * @param $categoryId
     * @param $storeId
     * @return array
     */
    public function getIndexForCategory($categoryId, $storeId)
    {
        try {
            return $this->getResource()->getIndexForCategory($categoryId, $storeId);
        } catch (LocalizedException $e) {
            return [];
        }
    }

    /**
     * @return string|null
     */
    public function getAppliedProduct()
    {
        return $this->getResourceName();
    }

    /**
     * @return mixed|string|null
     */
    public function getFlashsalesId()
    {
        return $this->getData(self::FLASHSALES_ID);
    }

    /**
     * @param string $flashsalesId
     * @return FlashSalesInterface|FlashSales
     */
    public function setFlashsalesId($flashsalesId)
    {
        return $this->setData(self::FLASHSALES_ID, $flashsalesId);
    }

    /**
     * @return mixed|string|null
     */
    public function getEventName()
    {
        return $this->getData(self::EVENT_NAME);
    }

    /**
     * @param string $eventName
     * @return FlashSalesInterface|FlashSales
     */
    public function setEventName($eventName)
    {
        return $this->setData(self::EVENT_NAME, $eventName);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return ExtensionAttributesInterface
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param FlashSalesExtensionInterface $extensionAttributes
     * @return FlashSales
     */
    public function setExtensionAttributes(
        FlashSalesExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * @return mixed|string|null
     */
    public function getFromDate()
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * @param string $fromDate
     * @return FlashSalesInterface|FlashSales
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * @return mixed|string|null
     */
    public function getToDate()
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * @param string $toDate
     * @return FlashSalesInterface|FlashSales
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * @return mixed|string|null
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @param string $sortOrder
     * @return FlashSalesInterface|FlashSales
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @return mixed|string|null
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @param string $isActive
     * @return FlashSalesInterface|FlashSales
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @return mixed|string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return FlashSalesInterface|FlashSales
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return mixed|string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return FlashSalesInterface|FlashSales
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @return mixed|string|null
     */
    public function getConditionsSerialized()
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * @param string $conditionsSerialized
     * @return FlashSalesInterface|FlashSales
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * @return mixed|string|null
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @param string $categoryId
     * @return FlashSalesInterface|FlashSales
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    /**
     * @return mixed|string|null
     */
    public function getHeaderBannerImage()
    {
        return $this->getData(self::HEADER_BANNER_IMAGE);
    }

    /**
     * @param string $headerBannerImage
     * @return FlashSalesInterface|FlashSales
     */
    public function setHeaderBannerImage($headerBannerImage)
    {
        return $this->setData(self::HEADER_BANNER_IMAGE, $headerBannerImage);
    }

    /**
     * @return mixed|string|null
     */
    public function getIsPrivateSale()
    {
        return $this->getData(self::IS_PRIVATE_SALE);
    }

    /**
     * @param string $isPrivateSale
     * @return FlashSalesInterface|FlashSales
     */
    public function setIsPrivateSale($isPrivateSale)
    {
        return $this->setData(self::IS_PRIVATE_SALE, $isPrivateSale);
    }

    /**
     * @return mixed|string|null
     */
    public function getIsDefaultPrivateConfig()
    {
        return $this->getData(self::IS_DEFAULT_PRIVATE_CONFIG);
    }

    /**
     * @param string $isDefaultPrivateConfig
     * @return FlashSalesInterface|FlashSales
     */
    public function setIsDefaultPrivateConfig($isDefaultPrivateConfig)
    {
        return $this->setData(self::IS_DEFAULT_PRIVATE_CONFIG, $isDefaultPrivateConfig);
    }

    /**
     * @return mixed|string|null
     */
    public function getThumbnailImage()
    {
        return $this->getData(self::THUMBNAIL_IMAGE);
    }

    /**
     * @param string $thumbnailImage
     * @return FlashSalesInterface|FlashSales
     */
    public function setThumbnailImage($thumbnailImage)
    {
        return $this->setData(self::THUMBNAIL_IMAGE, $thumbnailImage);
    }

    /**
     * @return mixed|string|null
     */
    public function getRestrictedEventLandingPage()
    {
        return $this->getData(self::RESTRICTED_LANDING_PAGE);
    }

    /**
     * @param string $restrictedLandingPage
     * @return FlashSalesInterface|FlashSales
     */
    public function setRestrictedEventLandingPage($restrictedLandingPage)
    {
        return $this->setData(self::RESTRICTED_LANDING_PAGE, $restrictedLandingPage);
    }

    /**
     * @return mixed|string|null
     */
    public function getGrantEventView()
    {
        return $this->getData(self::GRANT_EVENT_VIEW);
    }

    /**
     * @param string $grantEventView
     * @return FlashSalesInterface|FlashSales
     */
    public function setGrantEventView($grantEventView)
    {
        return $this->setData(self::GRANT_EVENT_VIEW, $grantEventView);
    }

    /**
     * @return mixed|string|null
     */
    public function getGrantEventProductPrice()
    {
        return $this->getData(self::GRANT_EVENT_PRODUCT_PRICE);
    }

    /**
     * @param string $grantEventProductPrice
     * @return FlashSalesInterface|FlashSales
     */
    public function setGrantEventProductPrice($grantEventProductPrice)
    {
        return $this->setData(self::GRANT_EVENT_PRODUCT_PRICE, $grantEventProductPrice);
    }

    /**
     * @return mixed|string|null
     */
    public function getGrantCheckoutItems()
    {
        return $this->getData(self::GRANT_CHECKOUT_ITEMS);
    }

    /**
     * @param string $grantCheckoutItems
     * @return FlashSalesInterface|FlashSales
     */
    public function setGrantCheckoutItems($grantCheckoutItems)
    {
        return $this->setData(self::GRANT_CHECKOUT_ITEMS, $grantCheckoutItems);
    }

    /**
     * @return mixed|string|null
     */
    public function getGrantEventViewGroups()
    {
        return $this->getData(self::GRANT_EVENT_VIEW_GROUPS);
    }

    /**
     * @param string $grantEventViewGroups
     * @return FlashSalesInterface|FlashSales
     */
    public function setGrantEventViewGroups($grantEventViewGroups)
    {
        return $this->setData(self::GRANT_EVENT_VIEW_GROUPS, $grantEventViewGroups);
    }

    /**
     * @return mixed|string|null
     */
    public function getGrantCheckoutItemsGroups()
    {
        return $this->getData(self::GRANT_CHECKOUT_ITEMS_GROUPS);
    }

    /**
     * @param string $grantCheckoutItemsGroups
     * @return FlashSalesInterface|FlashSales
     */
    public function setGrantCheckoutItemsGroups($grantCheckoutItemsGroups)
    {
        return $this->setData(self::GRANT_CHECKOUT_ITEMS_GROUPS, $grantCheckoutItemsGroups);
    }

    /**
     * @return mixed|string|null
     */
    public function getGrantEventProductPriceGroups()
    {
        return $this->getData(self::GRANT_EVENT_PRODUCT_PRICE_GROUPS);
    }

    /**
     * @param string $grantEventProductPriceGroups
     * @return FlashSalesInterface|FlashSales
     */
    public function setGrantEventProductPriceGroups($grantEventProductPriceGroups)
    {
        return $this->setData(self::GRANT_EVENT_PRODUCT_PRICE_GROUPS, $grantEventProductPriceGroups);
    }

    /**
     * @return mixed|string|null
     */
    public function getCartButtonTitle()
    {
        return $this->getData(self::CART_BUTTON_TITLE);
    }

    /**
     * @param string $cartButtonTitle
     * @return FlashSalesInterface|FlashSales
     */
    public function setCartButtonTitle($cartButtonTitle)
    {
        return $this->setData(self::CART_BUTTON_TITLE, $cartButtonTitle);
    }

    /**
     * @return mixed|string|null
     */
    public function getMessageHiddenAddToCart()
    {
        return $this->getData(self::MESSAGE_HIDDEN_ADD_TO_CART);
    }

    /**
     * @param string $messageHiddenAddToCart
     * @return FlashSalesInterface|FlashSales
     */
    public function setMessageHiddenAddToCart($messageHiddenAddToCart)
    {
        return $this->setData(self::MESSAGE_HIDDEN_ADD_TO_CART, $messageHiddenAddToCart);
    }

    /**
     * @return mixed|string|null
     */
    public function getDisplayCartMode()
    {
        return $this->getData(self::DISPLAY_CART_MODE);
    }

    /**
     * @param string $displayCartMode
     * @return FlashSalesInterface|FlashSales
     */
    public function setDisplayCartMode($displayCartMode)
    {
        return $this->setData(self::DISPLAY_CART_MODE, $displayCartMode);
    }

    /**
     * @return mixed|string|null
     */
    public function getDisplayProductMode()
    {
        return $this->getData(self::DISPLAY_PRODUCT_MODE);
    }

    /**
     * @param string $displayProductMode
     * @return FlashSalesInterface|FlashSales
     */
    public function setDisplayProductMode($displayProductMode)
    {
        return $this->setData(self::DISPLAY_PRODUCT_MODE, $displayProductMode);
    }

    /**
     * @return mixed|string|null
     */
    public function getIsAssignCategory()
    {
        return $this->getData(self::IS_ASSIGN_CATEGORY);
    }

    /**
     * @param string $isAssignCategory
     * @return FlashSalesInterface|FlashSales
     */
    public function setIsAssignCategory($isAssignCategory)
    {
        return $this->setData(self::IS_ASSIGN_CATEGORY, $isAssignCategory);
    }

    /**
     * Prepare flash sales Available.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Prepare flash sales Available.
     *
     * @return array
     */
    public function getAvailableEventTypes()
    {
        return [self::TYPE_PRIVATE_SALES => __('Private Sales'), self::TYPE_FLASH_SALES => __('Flash Sales')];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [
            self::CACHE_TAG . '_' . $this->getId(),
            \Magento\Catalog\Model\Category::CACHE_TAG . '_' . $this->getCategoryId(),
            \Magento\Catalog\Model\Product::CACHE_PRODUCT_CATEGORY_TAG . '_' . $this->getCategoryId(),
        ];
    }
}
