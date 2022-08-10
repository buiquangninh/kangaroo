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

namespace Lof\FlashSales\Model\Data;

use Lof\FlashSales\Api\Data\FlashSalesInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class FlashSales extends \Magento\Framework\Api\AbstractExtensibleObject implements FlashSalesInterface
{

    /**
     * @return mixed|string|null
     */
    public function getId()
    {
        return $this->_get(self::FLASHSALES_ID);
    }

    /**
     * @param string $flashsalesId
     * @return FlashSalesInterface|FlashSales
     */
    public function setId($flashsalesId)
    {
        return $this->setData(self::FLASHSALES_ID, $flashsalesId);
    }

    /**
     * Get flashsales_id
     * @return string|null
     */
    public function getFlashsalesId()
    {
        return $this->_get(self::FLASHSALES_ID);
    }

    /**
     * Set flashsales_id
     * @param string $flashsalesId
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setFlashsalesId($flashsalesId)
    {
        return $this->setData(self::FLASHSALES_ID, $flashsalesId);
    }

    /**
     * Get event_name
     * @return string|null
     */
    public function getEventName()
    {
        return $this->_get(self::EVENT_NAME);
    }

    /**
     * Set event_name
     * @param string $eventName
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setEventName($eventName)
    {
        return $this->setData(self::EVENT_NAME, $eventName);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\FlashSales\Api\Data\FlashSalesExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Lof\FlashSales\Api\Data\FlashSalesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\FlashSales\Api\Data\FlashSalesExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get from_date
     * @return string|null
     */
    public function getFromDate()
    {
        return $this->_get(self::FROM_DATE);
    }

    /**
     * Set from_date
     * @param string $fromDate
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setFromDate($fromDate)
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * Get to_date
     * @return string|null
     */
    public function getToDate()
    {
        return $this->_get(self::TO_DATE);
    }

    /**
     * Set to_date
     * @param string $toDate
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setToDate($toDate)
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * Get sort_order
     * @return string|null
     */
    public function getSortOrder()
    {
        return $this->_get(self::SORT_ORDER);
    }

    /**
     * Set sort_order
     * @param string $sortOrder
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Get is_active
     * @return string|null
     */
    public function getIsActive()
    {
        return $this->_get(self::IS_ACTIVE);
    }

    /**
     * Set is_active
     * @param string $isActive
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get conditions_serialized
     * @return string|null
     */
    public function getConditionsSerialized()
    {
        return $this->_get(self::CONDITIONS_SERIALIZED);
    }

    /**
     * Set conditions_serialized
     * @param string $conditionsSerialized
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setConditionsSerialized($conditionsSerialized)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * Get category_id
     * @return string|null
     */
    public function getCategoryId()
    {
        return $this->_get(self::CATEGORY_ID);
    }

    /**
     * Set category_id
     * @param string $categoryId
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    /**
     * Get header_banner_image
     * @return string|null
     */
    public function getHeaderBannerImage()
    {
        return $this->_get(self::HEADER_BANNER_IMAGE);
    }

    /**
     * Set header_banner_image
     * @param string $headerBannerImage
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setHeaderBannerImage($headerBannerImage)
    {
        return $this->setData(self::HEADER_BANNER_IMAGE, $headerBannerImage);
    }

    /**
     * Get is_private_sale
     * @return string|null
     */
    public function getIsPrivateSale()
    {
        return $this->_get(self::IS_PRIVATE_SALE);
    }

    /**
     * Set is_private_sale
     * @param string $isPrivateSale
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setIsPrivateSale($isPrivateSale)
    {
        return $this->setData(self::IS_PRIVATE_SALE, $isPrivateSale);
    }

    /**
     * Get is_default_private_config
     * @return string|null
     */
    public function getIsDefaultPrivateConfig()
    {
        return $this->_get(self::IS_DEFAULT_PRIVATE_CONFIG);
    }

    /**
     * Set is_default_private_config
     * @param string $isDefaultPrivateConfig
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
     */
    public function setIsDefaultPrivateConfig($isDefaultPrivateConfig)
    {
        return $this->setData(self::IS_DEFAULT_PRIVATE_CONFIG, $isDefaultPrivateConfig);
    }

    /**
     * Get thumbnail_image
     * @return string|null
     */
    public function getThumbnailImage()
    {
        return $this->_get(self::THUMBNAIL_IMAGE);
    }

    /**
     * Set thumbnail_image
     * @param string $thumbnailImage
     * @return \Lof\FlashSales\Api\Data\FlashSalesInterface
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
        return $this->_get(self::RESTRICTED_LANDING_PAGE);
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
        return $this->_get(self::GRANT_EVENT_VIEW);
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
        return $this->_get(self::GRANT_EVENT_PRODUCT_PRICE);
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
        return $this->_get(self::GRANT_CHECKOUT_ITEMS);
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
        return $this->_get(self::GRANT_EVENT_VIEW_GROUPS);
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
        return $this->_get(self::GRANT_CHECKOUT_ITEMS_GROUPS);
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
        return $this->_get(self::GRANT_EVENT_PRODUCT_PRICE_GROUPS);
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
        return $this->_get(self::CART_BUTTON_TITLE);
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
        return $this->_get(self::MESSAGE_HIDDEN_ADD_TO_CART);
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
        return $this->_get(self::DISPLAY_CART_MODE);
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
        return $this->_get(self::DISPLAY_PRODUCT_MODE);
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
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * @param string $status
     *
     * @return FlashSalesInterface|FlashSales
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return mixed|string|null
     */
    public function getIsAssignCategory()
    {
        return $this->_get(self::IS_ASSIGN_CATEGORY);
    }

    /**
     * @param string $isAssignCategory
     * @return FlashSalesInterface|FlashSales
     */
    public function setIsAssignCategory($isAssignCategory)
    {
        return $this->setData(self::IS_ASSIGN_CATEGORY, $isAssignCategory);
    }
}
