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
use Lof\FlashSales\Model\FlashSales;
use Magento\Catalog\Model\Category;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Url;
use Magento\Framework\UrlFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PermissionsData extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var FlashSales
     */
    protected $_flashSalesModel;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlFactory
     */
    protected $_urlFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param FlashSales $flashSalesModel
     * @param Url $customerUrl
     * @param UrlFactory $_urlFactory
     * @param ScopeConfigInterface $_scopeConfig
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Data $helperData,
        StoreManagerInterface $storeManager,
        FlashSales $flashSalesModel,
        Url $customerUrl,
        UrlFactory $_urlFactory,
        ScopeConfigInterface $_scopeConfig
    ) {
        $this->_scopeConfig = $_scopeConfig;
        $this->customerUrl = $customerUrl;
        $this->_urlFactory = $_urlFactory;
        $this->_flashSalesModel = $flashSalesModel;
        $this->_helperData = $helperData;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @param null $category
     * @param null $product
     * @param null $storeId
     * @param null $customerGroupId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isAllowedCategoryView($category = null, $product = null, $storeId = null, $customerGroupId = null)
    {
        if ($category instanceof Category) {
            if ($permissions = $category->getData('permissions')) {
                $viewMode = !!$permissions[FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG] ?
                    $this->_helperData->getCatalogCategoryViewMode($storeId) :
                    $category->getPermissions()[FlashSalesInterface::GRANT_EVENT_VIEW];

                return !!$permissions[FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG] ? $this->isAllowedGrant(
                    $viewMode,
                    $this->_helperData->getCatalogCategoryViewGroups($storeId),
                    $customerGroupId
                ) : $this->isAllowedGrant(
                    $viewMode,
                    $this->_helperData->getEventCategoryViewGroups($category),
                    $customerGroupId
                );
            }
        }

        if ($product instanceof Product) {
            $viewMode = !!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG) ?
                $this->_helperData->getCatalogCategoryViewMode($storeId) :
                $product->getData(FlashSalesInterface::GRANT_EVENT_VIEW);

            return !!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG) ? $this->isAllowedGrant(
                $viewMode,
                $this->_helperData->getCatalogCategoryViewGroups($storeId),
                $customerGroupId
            ) : $this->isAllowedGrant(
                $viewMode,
                $this->_helperData->getEventCategoryViewGroups($product),
                $customerGroupId
            );
        }

        return true;
    }

    /**
     * @param $product
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function isAllowAddToCart($product)
    {
        if ($product->getData(FlashSalesInterface::FLASHSALES_ID)) {
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $checkoutItemsGroups = !!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG) ?
                $this->_helperData->getCheckoutItemsGroups() :
                $product->getData(FlashSalesInterface::GRANT_CHECKOUT_ITEMS_GROUPS);

            $displayCartMode = !!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG) ?
                $this->_helperData->getDisplayCartMode() :
                $product->getData(FlashSalesInterface::DISPLAY_CART_MODE);

            if (!!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG)) {
                if ($this->_helperData->getCheckoutItemsMode() == ConfigData::GRANT_CUSTOMER_GROUP) {
                    if (!in_array($customerGroupId, $checkoutItemsGroups)) {
                        return true;
                    }
                }

                if ($this->_helperData->getCheckoutItemsMode() == ConfigData::GRANT_NONE && $displayCartMode != 1) {
                    return true;
                }

                if ($this->_helperData->getCheckoutItemsMode() == ConfigData::GRANT_ALL) {
                    return false;
                }
            } else {
                if ($product->getData(FlashSalesInterface::GRANT_CHECKOUT_ITEMS) == ConfigData::GRANT_CUSTOMER_GROUP) {
                    if (!in_array($customerGroupId, $checkoutItemsGroups)) {
                        return true;
                    }
                }

                if ($product->getData(FlashSalesInterface::GRANT_CHECKOUT_ITEMS) == ConfigData::GRANT_NONE
                    && $displayCartMode != 1
                ) {
                    return true;
                }

                if ($product->getData(FlashSalesInterface::GRANT_CHECKOUT_ITEMS) == ConfigData::GRANT_ALL) {
                    return false;
                }
            }

            if (isset($displayCartMode) && $displayCartMode == 1) {
                return false;
            }
        }

        return false;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getCartButtonTitle($product)
    {
        if (!!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG)) {
            return $this->_helperData->getCartButtonTitle();
        }
        return $product->getCartButtonTitle();
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getMessageHiddenAddToCart($product)
    {
        if (!!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG)) {
            return $this->_helperData->getMessageHiddenAddToCart();
        }
        return $product->getMessageHiddenAddToCart();
    }

    /**
     * @param $product
     * @param null $storeId
     * @param null $customerGroupId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isAllowedProductPrice($product, $storeId = null, $customerGroupId = null)
    {
        if (!!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG)) {
            return $this->isAllowedGrant(
                $this->_helperData->getCatalogProductPriceMode($storeId),
                $this->_helperData->getCatalogProductPriceGroups($storeId),
                $customerGroupId
            );
        }

        return $this->isAllowedGrant(
            $product->getData(FlashSalesInterface::GRANT_EVENT_PRODUCT_PRICE),
            $this->_helperData->getEventProductPriceGroups($product),
            $customerGroupId
        );
    }

    /**
     * @param $product
     * @param null $storeId
     * @param null $customerGroupId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isAllowedCheckoutItems($product, $storeId = null, $customerGroupId = null)
    {
        if (!!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG)) {
            return $this->isAllowedGrant(
                $this->_helperData->getCheckoutItemsMode($storeId),
                $this->_helperData->getCheckoutItemsGroups($storeId),
                $customerGroupId
            );
        }

        return $this->isAllowedGrant(
            $product->getData(FlashSalesInterface::GRANT_CHECKOUT_ITEMS),
            $this->_helperData->getEventCheckoutItemsGroups($product),
            $customerGroupId
        );
    }

    /**
     * @param null $category
     * @param null $product
     * @return string
     */
    public function getLandingPageUrl($category = null, $product = null)
    {
        if ($product instanceof Product) {
            return !!$product->getData(FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG) ?
                $this->redirectRestrictPage($this->_helperData->getRestrictedLandingPage()) :
                $this->redirectRestrictPage($product->getRestrictedLandingPage());

        }
        if ($category instanceof Category) {
            return !!$category->getData('permissions')[FlashSalesInterface::IS_DEFAULT_PRIVATE_CONFIG] ?
                $this->redirectRestrictPage($this->_helperData->getRestrictedLandingPage()) :
                $this->redirectRestrictPage($category->getData('permissions')
                [FlashSalesInterface::RESTRICTED_LANDING_PAGE]);
        }
        return $this->_getUrl('', ['_direct' => $this->_helperData->getRestrictedLandingPage()]);
    }

    /**
     * @param $restrictedLandingPage
     * @return string
     */
    public function redirectRestrictPage($restrictedLandingPage)
    {
        switch ($restrictedLandingPage) {
            case \Lof\FlashSales\Model\Adminhtml\System\Config\Source\Page::ALLOW_LOGIN:
                if (!$this->customerSession->isLoggedIn()) {
                    return $this->_urlFactory->create()->getUrl('customer/account/login');
                } else {
                    return $this->customerUrl->getDashboardUrl();
                }
                break;
            case \Lof\FlashSales\Model\Adminhtml\System\Config\Source\Page::ALLOW_REGISTER:
                if (!$this->customerSession->isLoggedIn()) {
                    return $this->_urlFactory->create()->getUrl('customer/account/create');
                } else {
                    return $this->customerUrl->getDashboardUrl();
                }
                break;
        }
        return $this->_getUrl(
            '',
            [
                '_direct' => $restrictedLandingPage
            ]
        );
    }

    /**
     *
     * @param string $mode
     * @param string[] $groups
     * @param int|null $customerGroupId
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function isAllowedGrant($mode, $groups, $customerGroupId = null)
    {
        if ($mode == ConfigData::GRANT_CUSTOMER_GROUP) {
            if (!$groups) {
                return false;
            }

            if ($customerGroupId === null) {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
            }

            return in_array($customerGroupId, $groups);
        }

        return $mode == ConfigData::GRANT_ALL;
    }
}
