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

namespace Lof\FlashSales\Helper;

use Lof\FlashSales\Api\Data\FlashSalesInterface;
use Lof\FlashSales\Model\Adminhtml\System\Config\Source\ConfigData;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\Collection;
use Lof\FlashSales\Model\ResourceModel\AppliedProducts\CollectionFactory as AppliedProductsCollectionFactory;
use Lof\FlashSales\Ui\Component\Form\PriceType;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Json\EncoderInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Store current date at "Y-m-d H:i:s" format
     *
     * @var string
     */
    protected $_now;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;

    /**
     * @var AppliedProductsCollectionFactory
     */
    protected $appliedProductsCollectionFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param AppliedProductsCollectionFactory $appliedProductsCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param IndexerFactory $indexerFactory
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        AppliedProductsCollectionFactory $appliedProductsCollectionFactory,
        StoreManagerInterface $storeManager,
        IndexerFactory $indexerFactory
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->jsonEncoder = $jsonEncoder;
        $this->appliedProductsCollectionFactory = $appliedProductsCollectionFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @param $key
     * @param null $store
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        return $this->scopeConfig->getValue(
            $key,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param $path
     * @param null $id
     * @return bool
     */
    public function hasFlagConfig($path, $id = null)
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $id);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function isEnabled($storeId = null)
    {
        return $this->hasFlagConfig(ConfigData::XML_PATH_MODULE_STATUS, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getSellOverQuantityLimit($storeId = null)
    {
        return $this->hasFlagConfig(ConfigData::XML_PATH_SELL_OVER_QTY_LIMIT, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEventColumn($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_EVENT_COLUMN, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getCategoryHeaderStyle($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_CATEGORY_HEADER_STYLE, $storeId) ?: 1;
    }

    /**
     * @param null $storeId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getProductHeaderStyle($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_PRODUCT_HEADER_STYLE, $storeId) ?: '1';
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEventStyle($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_EVENT_STYLE, $storeId) ?: 'center';
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductTimerMode($storeId = null)
    {
        $timeDisplayStyle = $this->getConfig(ConfigData::XML_PATH_PRODUCT_TIMER, $storeId);
        return $this->jsConfigTimerMode($timeDisplayStyle);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEventCategoryTimerMode($storeId = null)
    {
        $timeDisplayStyle = $this->getConfig(ConfigData::XML_PATH_EVENT_CATEGORY_TIMER, $storeId);
        return $this->jsConfigTimerMode($timeDisplayStyle);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEventListTimerMode($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_EVENT_LIST_TIMER, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getComingSoonEvent($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_EVENT_COMING_SOON_EVENT, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEndingSoonEvent($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_EVENT_ENDING_SOON_EVENT, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getDefaultBanner($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_DEFAULT_BANNER, $storeId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getMediaBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @param $timeDisplayStyle
     * @return string
     */
    public function jsConfigTimerMode($timeDisplayStyle)
    {
        switch ($timeDisplayStyle) {
            case \Lof\FlashSales\Model\Adminhtml\System\Config\Source\CountDownMode::CDM_DAYS_HOURS:
                return '%D days and %H hours';
            case \Lof\FlashSales\Model\Adminhtml\System\Config\Source\CountDownMode::CDM_HOURS_MINUTES_SECONDS:
                return '%H:%M:%S';
            case \Lof\FlashSales\Model\Adminhtml\System\Config\Source\CountDownMode::CDM_DAYS_HOURS_MINUTES_SECONDS:
                return '%D days %H:%M:%S';
            default:
                return '';
        }
    }

    /**
     * Algorithm for calculating price by rule
     *
     * @param $priceType
     * @param int $discountAmount
     * @param float $originalPrice
     * @return float|int
     */
    public function calcPriceRule($priceType, $discountAmount, $originalPrice)
    {
        $priceRule = 0;
        switch ($priceType) {
            case PriceType::TYPE_DECREASE_FIXED:
                $priceRule = max(0, $originalPrice - $discountAmount);
                break;
            case PriceType::TYPE_DECREASE_PERCENTAGE:
                $priceRule = $originalPrice * (1 - $discountAmount / 100);
                break;
        }
        return $priceRule;
    }

    /**
     * Return restricted landing page
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getRestrictedLandingPage($store = null)
    {
        return $this->scopeConfig->getValue(
            ConfigData::XML_PATH_LANDING_PAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Return category browsing mode
     *
     * @return string
     */
    public function getCatalogCategoryViewMode()
    {
        return $this->scopeConfig->getValue(ConfigData::XML_PATH_GRANT_EVENT_VIEW, 'default');
    }

    /**
     * Return display product mode
     *
     * @return string
     */
    public function getDisplayProductMode()
    {
        return $this->scopeConfig->getValue(ConfigData::XML_PATH_DISPLAY_PRODUCT_MODE, 'default');
    }

    /**
     * Return display cart mode
     *
     * @return mixed
     */
    public function getDisplayCartMode()
    {
        return $this->scopeConfig->getValue(ConfigData::XML_PATH_DISPLAY_CART_MODE, 'default');
    }

    /**
     * Return message hidden add to cart
     *
     * @return mixed
     */
    public function getMessageHiddenAddToCart()
    {
        return $this->scopeConfig->getValue(ConfigData::XML_PATH_MESSAGE_HIDDEN_ADD_TO_CART, 'default');
    }

    /**
     * Return message cart button title
     *
     * @return mixed
     */
    public function getCartButtonTitle()
    {
        return $this->scopeConfig->getValue(ConfigData::XML_PATH_CART_BUTTON_TITLE, 'default');
    }

    /**
     * Return category browsing groups
     *
     * @return string[]
     */
    public function getCatalogCategoryViewGroups()
    {
        $groups = $this->scopeConfig->getValue(
            ConfigData::XML_PATH_GRANT_EVENT_VIEW . '_groups',
            'default'
        );

        return $this->convertToArray((string)$groups);
    }

    /**
     * Return display products mode
     *
     * @return string
     */
    public function getCatalogProductPriceMode()
    {
        return $this->scopeConfig->getValue(ConfigData::XML_PATH_GRANT_EVENT_PRODUCT_PRICE, 'default');
    }

    /**
     * Return display products groups
     *
     * @return string[]
     */
    public function getCatalogProductPriceGroups()
    {
        $groups = $this->scopeConfig->getValue(
            ConfigData::XML_PATH_GRANT_EVENT_PRODUCT_PRICE . '_groups',
            'default'
        );

        return $this->convertToArray((string)$groups);
    }

    /**
     * Return adding to cart mode
     *
     * @return string
     */
    public function getCheckoutItemsMode()
    {
        return $this->scopeConfig->getValue(ConfigData::XML_PATH_GRANT_CHECKOUT_ITEMS, 'default');
    }

    /**
     * Return adding to cart groups
     *
     * @return string[]
     */
    public function getCheckoutItemsGroups()
    {
        $groups = $this->scopeConfig->getValue(ConfigData::XML_PATH_GRANT_CHECKOUT_ITEMS . '_groups', 'default');

        return $this->convertToArray((string)$groups);
    }

    /**
     * @param $object
     * @return array
     */
    public function getEventCategoryViewGroups($object)
    {
        if ($object instanceof Product) {
            $groups = $object->getData(FlashSalesInterface::GRANT_EVENT_VIEW_GROUPS);
        } else {
            $permissions = $object->getData('permissions');
            $groups = $permissions[FlashSalesInterface::GRANT_EVENT_VIEW_GROUPS];
        }

        return $this->convertToArray((string)$groups);
    }

    /**
     * @param $product
     * @return array
     */
    public function getEventProductPriceGroups($product)
    {
        $groups = $product->getData(FlashSalesInterface::GRANT_EVENT_PRODUCT_PRICE_GROUPS);

        return $this->convertToArray((string)$groups);
    }

    /**
     * @param $product
     * @return array
     */
    public function getEventCheckoutItemsGroups($product)
    {
        $groups = $product->getData(FlashSalesInterface::GRANT_CHECKOUT_ITEMS_GROUPS);
        return $this->convertToArray((string)$groups);
    }

    /**
     * Convert string value to array
     *
     * @param string $value
     * @return array
     */
    private function convertToArray(string $value): array
    {
        return strlen($value) ? explode(',', $value) : [];
    }

    /**
     * Retrieve current datetTime
     *
     * @return string
     */
    public function getCurrentDateTime()
    {
        if (!$this->_now) {
            return (new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT);
        }
        return $this->_now;
    }

    /**
     * @return Collection
     */
    public function getAppliedProductCollection()
    {
        return $this->appliedProductsCollectionFactory->create();
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductItemSelector($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_PRODUCT_ITEM_SELECTOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductItemActionsSelector($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_PRODUCT_ITEM_ACTIONS_SELECTOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductInfoMainSelector($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_PRODUCT_INFO_MAIN_SELECTOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductInfoPriceSelector($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_PRODUCT_INFO_PRICE_SELECTOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getPageMainSelector($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_PAGE_MAIN_SELECTOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getGroupedSelector($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_GROUPED_SELECTOR, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEnableDiscountAmount($storeId = null)
    {
        return $this->hasFlagConfig(ConfigData::XML_PATH_DISCOUNT_AMOUNT, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getDiscountAmountTypePercent($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_PERCENT, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getDiscountAmountTypeFixed($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_FIXED, $storeId);
    }

    /**
     * @param $saleableItem
     * @return string
     */
    public function customAddToCartButtonHtml($saleableItem)
    {
        if (!$saleableItem->getEntityId()) {
            return '';
        }

        $productId = $saleableItem->getEntityId();
        $buttonTitle = !!$saleableItem->getData('is_default_private_config')
            ? $this->getCartButtonTitle()
            : $saleableItem->getData('cart_button_title');

        $html = '<a class="loffs-private-button" data-product-id="' . (int)$productId . '"
           data-loffsattention="loff-attention-popup">
            <span>' . $buttonTitle . '</span>
        </a>';

        $html .= '<script type="text/javascript">
            require([
                "jquery",
                "Lof_FlashSales/js/loffsattention"
            ], function ($, attention) {
                attention.addAlertData(' . $this->getJsonConfigPopUp($saleableItem) . ')
            });
        </script>';

        return $html;
    }

    /**
     * @param $storeId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getDefaultThumbnail($storeId = null)
    {
        return $this->getConfig(ConfigData::XML_PATH_DEFAULT_THUMBNAIL, $storeId);
    }

    /**
     * @param $saleableItem
     * @return string
     */
    public function getJsonConfigPopUp($saleableItem)
    {
        $message = !!$saleableItem->getData('is_default_private_config')
            ? $this->getMessageHiddenAddToCart()
            : $saleableItem->getData('message_hidden_add_to_cart');
        return $this->jsonEncoder->encode([
            'product_id' => $saleableItem->getEntityId(),
            'name' => $saleableItem->getName(),
            'message' => $message
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function reindexProductPrice()
    {
        $indexerIds = [
            'catalog_product_price',
            'lof_flashsales_productprice',
            'cataloginventory_stock'
        ];

        foreach ($indexerIds as $indexerId) {
            $indexer = $this->indexerFactory->create();
            $indexer->load($indexerId);
            $indexer->reindexAll();
        }
    }
}
