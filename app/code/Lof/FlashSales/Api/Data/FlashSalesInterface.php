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

namespace Lof\FlashSales\Api\Data;

interface FlashSalesInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const FLASHSALES_ID = 'flashsales_id';
    const EVENT_NAME = 'event_name';
    const FROM_DATE = 'from_date';
    const TO_DATE = 'to_date';
    const SORT_ORDER = 'sort_order';
    const UPDATED_AT = 'updated_at';
    const IS_ACTIVE = 'is_active';
    const IS_ASSIGN_CATEGORY = 'is_assign_category';
    const CREATED_AT = 'created_at';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const CATEGORY_ID = 'category_id';
    const IS_PRIVATE_SALE = 'is_private_sale';
    const THUMBNAIL_IMAGE = 'thumbnail_image';
    const HEADER_BANNER_IMAGE = 'header_banner_image';
    const IS_DEFAULT_PRIVATE_CONFIG = 'is_default_private_config';
    const RESTRICTED_LANDING_PAGE = 'restricted_landing_page';
    const GRANT_EVENT_VIEW = 'grant_event_view';
    const GRANT_EVENT_PRODUCT_PRICE = 'grant_event_product_price';
    const GRANT_CHECKOUT_ITEMS = 'grant_checkout_items';
    const GRANT_CHECKOUT_ITEMS_GROUPS = 'grant_checkout_items_groups';
    const GRANT_EVENT_VIEW_GROUPS = 'grant_event_view_groups';
    const GRANT_EVENT_PRODUCT_PRICE_GROUPS = 'grant_event_product_price_groups';
    const DISPLAY_CART_MODE = 'display_cart_mode';
    const DISPLAY_PRODUCT_MODE = 'display_product_mode';
    const CART_BUTTON_TITLE = 'cart_button_title';
    const MESSAGE_HIDDEN_ADD_TO_CART = 'message_hidden_add_to_cart';
    const STATUS = 'status';
    const STATUS_LABEL = 'status_label';
    const MODEL = 'model';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $flashsalesId
     * @return FlashSalesInterface
     */
    public function setId($flashsalesId);

    /**
     * Get flashsales_id
     * @return string|null
     */
    public function getFlashsalesId();

    /**
     * Set flashsales_id
     * @param string $flashsalesId
     * @return FlashSalesInterface
     */
    public function setFlashsalesId($flashsalesId);

    /**
     * Get event_name
     * @return string|null
     */
    public function getEventName();

    /**
     * Set event_name
     * @param string $eventName
     * @return FlashSalesInterface
     */
    public function setEventName($eventName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Lof\FlashSales\Api\Data\FlashSalesExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Lof\FlashSales\Api\Data\FlashSalesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Lof\FlashSales\Api\Data\FlashSalesExtensionInterface $extensionAttributes
    );

    /**
     * Get from_date
     * @return string|null
     */
    public function getFromDate();

    /**
     * Set from_date
     * @param string $fromDate
     * @return FlashSalesInterface
     */
    public function setFromDate($fromDate);

    /**
     * Get to_date
     * @return string|null
     */
    public function getToDate();

    /**
     * Set to_date
     * @param string $toDate
     * @return FlashSalesInterface
     */
    public function setToDate($toDate);

    /**
     * Get sort_order
     * @return string|null
     */
    public function getSortOrder();

    /**
     * Set sort_order
     * @param string $sortOrder
     * @return FlashSalesInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get is_active
     * @return string|null
     */
    public function getIsActive();

    /**
     * Set is_active
     * @param string $isActive
     * @return FlashSalesInterface
     */
    public function setIsActive($isActive);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return FlashSalesInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return FlashSalesInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get conditions_serialized
     * @return string|null
     */
    public function getConditionsSerialized();

    /**
     * Set conditions_serialized
     * @param string $conditionsSerialized
     * @return FlashSalesInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Get category_id
     * @return string|null
     */
    public function getCategoryId();

    /**
     * Set category_id
     * @param string $categoryId
     * @return FlashSalesInterface
     */
    public function setCategoryId($categoryId);

    /**
     * Get header_banner_image
     * @return string|null
     */
    public function getHeaderBannerImage();

    /**
     * Set header_banner_image
     * @param string $headerBannerImage
     * @return FlashSalesInterface
     */
    public function setHeaderBannerImage($headerBannerImage);

    /**
     * Get is_private_sale
     * @return string|null
     */
    public function getIsPrivateSale();

    /**
     * Set is_private_sale
     * @param string $isPrivateSale
     * @return FlashSalesInterface
     */
    public function setIsPrivateSale($isPrivateSale);

    /**
     * Get is_default_private_config
     * @return string|null
     */
    public function getIsDefaultPrivateConfig();

    /**
     * Set is_default_private_config
     * @param string $isDefaultPrivateConfig
     * @return FlashSalesInterface
     */
    public function setIsDefaultPrivateConfig($isDefaultPrivateConfig);

    /**
     * Get thumbnail_image
     * @return string|null
     */
    public function getThumbnailImage();

    /**
     * Set thumbnail_image
     * @param string $thumbnailImage
     * @return FlashSalesInterface
     */
    public function setThumbnailImage($thumbnailImage);

    /**
     * Get restricted_landing_page
     * @return string|null
     */
    public function getRestrictedEventLandingPage();

    /**
     * Set restricted_landing_page
     * @param string $restrictedLandingPage
     * @return FlashSalesInterface
     */
    public function setRestrictedEventLandingPage($restrictedLandingPage);

    /**
     * Get grant_event_view
     * @return string|null
     */
    public function getGrantEventView();

    /**
     * Set grant_event_view
     * @param string $grantEventView
     * @return FlashSalesInterface
     */
    public function setGrantEventView($grantEventView);

    /**
     * Get grant_event_product_price
     * @return string|null
     */
    public function getGrantEventProductPrice();

    /**
     * Set grant_event_product_price
     * @param string $grantEventProductPrice
     * @return FlashSalesInterface
     */
    public function setGrantEventProductPrice($grantEventProductPrice);

    /**
     * Get grant_checkout_items
     * @return string|null
     */
    public function getGrantCheckoutItems();

    /**
     * Set grant_checkout_items
     * @param string $grantCheckoutItems
     * @return FlashSalesInterface
     */
    public function setGrantCheckoutItems($grantCheckoutItems);

    /**
     * Get grant_event_view_groups
     * @return string|null
     */
    public function getGrantEventViewGroups();

    /**
     * Set grant_event_view_groups
     * @param string $grantEventViewGroups
     * @return FlashSalesInterface
     */
    public function setGrantEventViewGroups($grantEventViewGroups);

    /**
     * Get grant_checkout_items_groups
     * @return string|null
     */
    public function getGrantCheckoutItemsGroups();

    /**
     * Set grant_checkout_items_groups
     * @param string $grantCheckoutItemsGroups
     * @return FlashSalesInterface
     */
    public function setGrantCheckoutItemsGroups($grantCheckoutItemsGroups);

    /**
     * Get grant_event_product_price_groups
     * @return string|null
     */
    public function getGrantEventProductPriceGroups();

    /**
     * Set grant_event_product_price_groups
     * @param string $grantEventProductPriceGroups
     * @return FlashSalesInterface
     */
    public function setGrantEventProductPriceGroups($grantEventProductPriceGroups);

    /**
     * Get cart_button_title
     * @return string|null
     */
    public function getCartButtonTitle();

    /**
     * Set cart_button_title
     * @param string $cartButtonTitle
     * @return FlashSalesInterface
     */
    public function setCartButtonTitle($cartButtonTitle);

    /**
     * Get message_hidden_add_to_cart
     * @return string|null
     */
    public function getMessageHiddenAddToCart();

    /**
     * Set message_hidden_add_to_cart
     * @param string $messageHiddenAddToCart
     * @return FlashSalesInterface
     */
    public function setMessageHiddenAddToCart($messageHiddenAddToCart);

    /**
     * Get display_cart_mode
     * @return string|null
     */
    public function getDisplayCartMode();

    /**
     * Set display_cart_mode
     * @param string $displayCartMode
     * @return FlashSalesInterface
     */
    public function setDisplayCartMode($displayCartMode);

    /**
     * Get display_product_mode
     * @return string|null
     */
    public function getDisplayProductMode();

    /**
     * Set display_product_mode
     * @param string $displayProductMode
     * @return FlashSalesInterface
     */
    public function setDisplayProductMode($displayProductMode);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return FlashSalesInterface
     */
    public function setStatus($status);

    /**
     * Get is_assign_category
     * @return string|null
     */
    public function getIsAssignCategory();

    /**
     * Set is_assign_category
     * @param string $isAssignCategory
     * @return FlashSalesInterface
     */
    public function setIsAssignCategory($isAssignCategory);
}
