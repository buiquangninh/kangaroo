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

namespace Lof\FlashSales\Block\FlashSales;

use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Lof\FlashSales\Helper\Data;
use Lof\FlashSales\Model\DateResolver;
use Lof\FlashSales\Model\FlashSales;
use Lof\FlashSales\Model\FlashSales\Image;
use Lof\FlashSales\Model\ResourceModel\FlashSales\Collection;
use Lof\FlashSales\Model\ResourceModel\FlashSales\CollectionFactory as FlashSalesCollectionFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Lof\FlashSales\Model\Adminhtml\System\Config\Source\CategoryHeaderStyle;
use Magento\Catalog\Block\Product\Widget\Html\Pager;

/**
 * Class AbstractEvent
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractEvent extends \Magento\Framework\View\Element\Template
{

    /**
     * column mapper
     *
     * @var array
     */
    public static $columnMapper = [
        12 => 1,
        6 => 2,
        4 => 3,
    ];

    protected $_position;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Instance of pager block
     *
     * @var Pager
     */
    protected $pager;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var FlashSalesCollectionFactory
     */
    protected $flashSalesCollectionFactory;

    /**
     * @var Image
     */
    protected $flashSaleImage;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Statuses titles
     *
     * @var array
     */
    protected $_statuses;

    /**
     * @var DateResolver
     */
    protected $dateResolver;

    /**
     * AbstractEvent constructor.
     * @param Template\Context $context
     * @param Registry $registry
     * @param Data $helperData
     * @param FlashSalesCollectionFactory $flashSalesCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param DateResolver $dateResolver
     * @param Image|null $flashSaleImage
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Registry $registry,
        Data $helperData,
        FlashSalesCollectionFactory $flashSalesCollectionFactory,
        CategoryFactory $categoryFactory,
        DateResolver $dateResolver,
        ?Image $flashSaleImage = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dateResolver = $dateResolver;
        $this->categoryFactory = $categoryFactory;
        $this->flashSaleImage = $flashSaleImage ?? ObjectManager::getInstance()->get(Image::class);
        $this->flashSalesCollectionFactory = $flashSalesCollectionFactory;
        $this->helperData = $helperData;
        $this->_coreRegistry = $registry;
    }

    /**
     * Check availability to display event block
     *
     * @return boolean
     */
    abstract public function canDisplay();

    /**
     * _constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_statuses = [
            FlashSales::STATUS_UPCOMING => __('Up Coming'),
            FlashSales::STATUS_COMING_SOON => __('Opens In'),
            FlashSales::STATUS_ACTIVE => __('Sale Ends In'),
            FlashSales::STATUS_ENDING_SOON => __('Sale Ends In'),
            FlashSales::STATUS_ENDED => __('Closed'),
        ];
    }

    /**
     * @return $this|AbstractEvent
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $category = $this->getCurrentCategory();
        if ($category) {
            $flashSale = $this->getFlashSalesCollection()
                ->addFieldToFilter('category_id', $category->getId())
                ->getFirstItem();

            if ($category->getId() != $flashSale->getCategoryId()) {
                $this->getLayout()->unsetElement('loffs-header-banner');
            }

            if ($this->helperData->getCategoryHeaderStyle() == CategoryHeaderStyle::TYPE4) {
                $this->getLayout()->unsetElement('loffs-header-banner');
            }
        }

        return $this;
    }

    /**
     * @param $position
     */
    public function setPosition($position)
    {
        $this->_position = $position;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->_position;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getProductHeaderStyle()
    {
        return $this->helperData->getProductHeaderStyle();
    }

    /**
     * Retrieve current category model object
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    /**
     * Return catalog event status text
     *
     * @param FlashSales $flashSale
     * @return string
     */
    public function getStatusText($flashSale)
    {
        if (isset($this->_statuses[$flashSale->getStatus()])) {
            return $this->_statuses[$flashSale->getStatus()];
        }

        return '';
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getConfigStoreTimezone()
    {
        return $this->dateResolver->getConfigStoreTimezone();
    }

    /**
     * @param $flashSale
     * @param string $field
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getThumbnail($flashSale, $field = FlashSalesInterface::THUMBNAIL_IMAGE)
    {
        if ($this->flashSaleImage->getUrl($flashSale, $field)) {
            return $this->flashSaleImage->getUrl($flashSale, $field);
        }
        return null;
    }

    /**
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDefaultThumbnail()
    {
        if ($this->helperData->getDefaultThumbnail()) {
            return $this->helperData->getMediaBaseUrl() .
                'lofflashsales/display_settings/'
                . $this->helperData->getDefaultThumbnail();
        }
        return $this->getViewFileUrl('Lof_FlashSales::images/placeholder/default-thumbnail.png');
    }

    /**
     * @return Collection
     */
    public function getFlashSalesCollection()
    {
        return $this->flashSalesCollectionFactory->create();
    }

    /**
     * @param $categoryId
     * @return string|null
     */
    public function getCategoryUrl($categoryId)
    {
        if ($category = $this->getCategory($categoryId)) {
            return $category->getUrl();
        }
        return null;
    }

    /**
     * @return string
     */
    public function getFlashSalesActiveHtml()
    {
        return $this->getChildHtml('flashsales_active');
    }

    /**
     * @return string
     */
    public function getFlashSalesComingHtml()
    {
        return $this->getChildHtml('flashsales_coming');
    }

    /**
     * @return string
     */
    public function getFlashSalesEndingHtml()
    {
        return $this->getChildHtml('flashsales_ending');
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getFlashSalesBanner()
    {
        if ($this->getCurrentCategory()) {
            $categoryId = $this->getCurrentCategory()->getId();
            $collection =  $this->getFlashSalesCollection()
                ->addFieldToFilter('category_id', $categoryId)->getFirstItem();
            if ($collection->getFlashSalesId()) {
                return $collection;
            }
            return null;
        }
        return null;
    }

    /**
     * Retrieve how many events column should be displayed
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getEventColumn()
    {
        $columnMapper = array_flip(self::$columnMapper);
        return $columnMapper[$this->helperData->getEventColumn()];
    }

    /**
     * @return Category
     */
    public function getCategory($categoryId)
    {
        $category =  $this->categoryFactory->create()->load($categoryId);
        if (!$category->getId()) {
            return null;
        }
        return $category;
    }

    /**
     * Return current product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @return array|int|mixed|null
     */
    public function getProductId()
    {
        if ($this->getProduct()) {
            $productId = $this->getProduct()->getId();
        } else {
            $productId = null;
        }

        return $this->getData('product_id') ? $this->getData('product_id') : $productId;
    }

    /**
     * @return Data
     */
    public function getHelperData()
    {
        return $this->helperData;
    }
}
