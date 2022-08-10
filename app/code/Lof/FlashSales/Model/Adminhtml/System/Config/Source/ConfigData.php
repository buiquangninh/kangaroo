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

namespace Lof\FlashSales\Model\Adminhtml\System\Config\Source;

class ConfigData
{

    /**
     * Module status config path
     */
    const XML_PATH_MODULE_STATUS= 'lofflashsales/general/enabled';

    const XML_PATH_SELL_OVER_QTY_LIMIT = 'lofflashsales/general/sell_over_quantity_limit';

    /**
     * Config display settings path
     */
    const XML_PATH_EVENT_COLUMN = 'lofflashsales/display_settings/category_display_mode/event_column';

    const XML_PATH_DEFAULT_BANNER = 'lofflashsales/display_settings/category_display_mode/default_banner';

    const XML_PATH_DEFAULT_THUMBNAIL = 'lofflashsales/display_settings/category_display_mode/default_thumbnail';

    const XML_PATH_CATEGORY_HEADER_STYLE = 'lofflashsales/display_settings/category_display_mode/category_header_style';

    const XML_PATH_PRODUCT_HEADER_STYLE = 'lofflashsales/display_settings/product_display_mode/product_header_style';

    const XML_PATH_EVENT_STYLE = 'lofflashsales/display_settings/event_display_mode/event_style';

    /**
     * Config count down timer mode path
     */
    const XML_PATH_PRODUCT_TIMER = 'lofflashsales/display_settings/countdown_timer_mode/product_timer';

    const XML_PATH_EVENT_LIST_TIMER = "lofflashsales/display_settings/countdown_timer_mode/event_list_timer";

    const XML_PATH_EVENT_CATEGORY_TIMER = "lofflashsales/display_settings/countdown_timer_mode/event_category_timer";

    const XML_PATH_EVENT_COMING_SOON_EVENT = "lofflashsales/display_settings/countdown_timer_mode/coming_soon_event";

    const XML_PATH_EVENT_ENDING_SOON_EVENT = "lofflashsales/display_settings/countdown_timer_mode/ending_soon_event";

    /**
     * Config discount amount display
     */
    const XML_PATH_DISCOUNT_AMOUNT = "lofflashsales/display_settings/discount_amount_display/enable_discount_amount";

    const XML_PATH_PERCENT = "lofflashsales/display_settings/discount_amount_display/percent";

    const XML_PATH_FIXED = "lofflashsales/display_settings/discount_amount_display/fixed";

    /**#@+
     * Grant modes
     */
    const GRANT_ALL = 1;

    const GRANT_CUSTOMER_GROUP = 2;

    const GRANT_NONE = 0;

    /**
     * Configuration path for Event browsing mode
     */
    const XML_PATH_GRANT_EVENT_VIEW = "lofflashsales/private_sale_permissions/grant_event_view";

    /**
     * Configuration path for display products mode
     */
    const XML_PATH_GRANT_EVENT_PRODUCT_PRICE = 'lofflashsales/private_sale_permissions/grant_event_product_price';

    /**
     * Configuration path for adding to cart mode
     */
    const XML_PATH_GRANT_CHECKOUT_ITEMS = 'lofflashsales/private_sale_permissions/grant_checkout_items';

    /**
     * Configuration path for restricted landing page
     */
    const XML_PATH_LANDING_PAGE = 'lofflashsales/private_sale_permissions/restricted_landing_page';

    /**
     * Configuration path for display product mode
     */
    const XML_PATH_DISPLAY_PRODUCT_MODE = 'lofflashsales/private_sale_permissions/display_product_mode';

    /**
     * Configuration path for display cart mode
     */
    const XML_PATH_DISPLAY_CART_MODE = 'lofflashsales/private_sale_permissions/display_cart_mode';

    /**
     * Configuration path for cart button title
     */
    const XML_PATH_CART_BUTTON_TITLE = 'lofflashsales/private_sale_permissions/cart_button_title';

    /**
     * Configuration path for message hidden add to cart
     */
    const XML_PATH_MESSAGE_HIDDEN_ADD_TO_CART = 'lofflashsales/private_sale_permissions/message_hidden_add_to_cart';

    /**
     * Advanced
     */
    const XML_PATH_PRODUCT_ITEM_SELECTOR = 'lofflashsales/advanced/product_item_selector';

    const XML_PATH_PRODUCT_ITEM_ACTIONS_SELECTOR = 'lofflashsales/advanced/product_item_actions_selector';

    const XML_PATH_PRODUCT_INFO_MAIN_SELECTOR = 'lofflashsales/advanced/product_info_main_selector';

    const XML_PATH_PRODUCT_INFO_PRICE_SELECTOR = 'lofflashsales/advanced/product_info_price_selector';

    const XML_PATH_PAGE_MAIN_SELECTOR = 'lofflashsales/advanced/page_main_selector';

    const XML_PATH_GROUPED_SELECTOR = 'lofflashsales/advanced/grouped_selector';
}
