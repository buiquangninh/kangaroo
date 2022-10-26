<?php
namespace WeltPixel\GoogleTagManager\Model;

use WeltPixel\GoogleTagManager\lib\Google\Client as Google_Client;

/**
 * Class \WeltPixel\GoogleTagManager\Model\Api
 */
class Api extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Item types
     */
    const TYPE_VARIABLE_DATALAYER = 'v';
    const TYPE_VARIABLE_CONSTANT = 'c';
    const TYPE_TRIGGER_CUSTOM_EVENT = 'customEvent';
    const TYPE_TRIGGER_LINK_CLICK = 'linkClick';
    const TYPE_TRIGGER_PAGEVIEW = 'pageview';
    const TYPE_TRIGGER_DOMREADY = 'domReady';
    const TYPE_TAG_UA = 'ua';
    const TYPE_TAG_AWCT = 'awct';
    const TYPE_TAG_SP = 'sp';

    /**
     * Variable names
     */
    const VARIABLE_UA_TRACKING = 'WP - UA Tracking ID';
    const VARIABLE_EVENTLABEL = 'WP - Event Label';
    const VARIABLE_EVENTVALUE = 'WP - Event Value';

    /**
     * Trigger names
     */
    const TRIGGER_PRODUCT_CLICK = 'WP - Product Click';
    const TRIGGER_GTM_DOM = 'WP - gtm.dom';
    const TRIGGER_ADD_TO_CART = 'WP - Add To Cart';
    const TRIGGER_REMOVE_FROM_CART = 'WP - Remove From Cart';
    const TRIGGER_ALL_PAGES = 'WP - All Pages';
    const TRIGGER_EVENT_IMPRESSION = 'WP - Event Impression';
    const TRIGGER_PROMOTION_CLICK = 'WP - Promotion Click';
    const TRIGGER_CHECKOUT_OPTION = 'WP - Checkout Option';
    const TRIGGER_CHECKOUT_STEPS = 'WP - Checkout Steps';
    const TRIGGER_PROMOTION_VIEW = 'WP - Promotion View';
    const TRIGGER_ADD_TO_WISHLIST = 'WP - Add To Wishlist';
    const TRIGGER_ADD_TO_COMPARE = 'WP - Add To Compare';

    /**
     * Tag names
     */
    const TAG_GOOGLE_ANALYTICS = 'WP - Google Analytics';
    const TAG_PRODUCT_EVENT_CLICK = 'WP - Product Event - Click';
    const TAG_PRODUCT_EVENT_ADD_TO_CART = 'WP - Product Event - Add to Cart';
    const TAG_PRODUCT_EVENT_REMOVE_FROM_CART = 'WP - Product Event - Remove from Cart';
    const TAG_PRODUCT_EVENT_PRODUCT_IMPRESSIONS = 'WP - Product Event - Product Impressions';
    const TAG_CHECKOUT_STEP_OPTION = 'WP - Checkout Step Option';
    const TAG_CHECKOUT_STEP = 'WP - Checkout Step';
    const TAG_PROMOTION_IMPRESSION = 'WP - Promotion Impression';
    const TAG_PROMOTION_CLICK = 'WP - Promotion Click';
    const TAG_PRODUCT_EVENT_ADD_TO_WISHLIST = 'WP - Product Event - Add to Wishlist';
    const TAG_PRODUCT_EVENT_ADD_TO_COMPARE = 'WP - Product Event - Add to Compare';


    /**
     * Return list of variables for api creation
     * @param $uaTrackingId
     * @return array
     */
    private function _getVariables($uaTrackingId)
    {
        $variables = array
        (
            self::VARIABLE_UA_TRACKING => array
            (
                'name' => self::VARIABLE_UA_TRACKING,
                'type' => self::TYPE_VARIABLE_CONSTANT,
                'parameter' => array
                (
                    array
                    (
                        'type' => 'template',
                        'key' => 'value',
                        'value' => $uaTrackingId
                    )
                )
            ),
            self::VARIABLE_EVENTLABEL => array
            (
                'name' => self::VARIABLE_EVENTLABEL,
                'type' => self::TYPE_VARIABLE_DATALAYER,
                'parameter' => array
                (
                    array
                    (
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'eventLabel'
                    )
                )
            ),
            self::VARIABLE_EVENTVALUE => array
            (
                'name' => self::VARIABLE_EVENTVALUE,
                'type' => self::TYPE_VARIABLE_DATALAYER,
                'parameter' => array
                (
                    array
                    (
                        'type' => 'integer',
                        'key' => 'dataLayerVersion',
                        'value' => "2"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'setDefaultValue',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'name',
                        'value' => 'eventValue'
                    )
                )
            )
        );

        return $variables;
    }

    /**
     * Return list of triggers for api creation
     * @return array
     */
    private function _getTriggers()
    {
        $triggers = array
        (
            self::TRIGGER_PRODUCT_CLICK => array
            (
                'name' => self::TRIGGER_PRODUCT_CLICK,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'productClick'
                            )
                        )
                    )
                )
            ),
            self::TRIGGER_GTM_DOM => array
            (
                'name' => self::TRIGGER_GTM_DOM,
                'type' => self::TYPE_TRIGGER_DOMREADY
            ),
            self::TRIGGER_ADD_TO_CART => array
            (
                'name' => self::TRIGGER_ADD_TO_CART,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'addToCart'
                            )
                        )
                    )
                )
            ),
            self::TRIGGER_REMOVE_FROM_CART => array
            (
                'name' => self::TRIGGER_REMOVE_FROM_CART,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'removeFromCart'
                            )
                        )
                    )
                )
            ),
            self::TRIGGER_ALL_PAGES => array
            (
                'name' => self::TRIGGER_ALL_PAGES,
                'type' => self::TYPE_TRIGGER_PAGEVIEW
            ),
            self::TRIGGER_EVENT_IMPRESSION => array
            (
                'name' => self::TRIGGER_EVENT_IMPRESSION,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'impression'
                            )
                        )
                    )
                )
            ),
            self::TRIGGER_PROMOTION_CLICK => array
            (
                'name' => self::TRIGGER_PROMOTION_CLICK,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'promotionClick'
                            )
                        )
                    )
                )
            ),
            self::TRIGGER_CHECKOUT_OPTION => array
            (
                'name' => self::TRIGGER_CHECKOUT_OPTION,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'checkoutOption'
                            )
                        )
                    )
                )
            ),
            self::TRIGGER_CHECKOUT_STEPS => array
            (
                'name' => self::TRIGGER_CHECKOUT_STEPS,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'checkout'
                            )
                        )
                    )
                )
            ),
            self::TRIGGER_PROMOTION_VIEW => array
            (
                'name' => self::TRIGGER_PROMOTION_VIEW,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'promotionView'
                            )
                        )
                    )
                )
            ),
            self::TRIGGER_ADD_TO_WISHLIST => array
            (
                'name' => self::TRIGGER_ADD_TO_WISHLIST,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'addToWishlist'
                            )
                        )
                    )
                )
            ),
            self::TRIGGER_ADD_TO_COMPARE => array
            (
                'name' => self::TRIGGER_ADD_TO_COMPARE,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => array
                (
                    array
                    (
                        'type' => 'equals',
                        'parameter' => array
                        (
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ),
                            array
                            (
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'addToCompare'
                            )
                        )
                    )
                )
            )
        );
        return $triggers;
    }

    /**
     * Return list of tags for api creation
     * @param array $triggers
     * @param bool $ipAnonymization
     * @param bool $displayAdvertising
     * @return array
     */
    private function _getTags($triggers, $ipAnonymization, $displayAdvertising)
    {
        $tags = array
        (
            self::TAG_PRODUCT_EVENT_CLICK => array
            (
                'name' => self::TAG_PRODUCT_EVENT_CLICK,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_PRODUCT_CLICK]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'setTrackerName',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useDebugVersion',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Ecommerce'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableLinkId',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Product Click'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            ),
            self::TAG_PRODUCT_EVENT_ADD_TO_CART => array
            (
                'name' => self::TAG_PRODUCT_EVENT_ADD_TO_CART,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_ADD_TO_CART]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'setTrackerName',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useDebugVersion',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Ecommerce'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableLinkId',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Add to Cart'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'evenValue',
                        'value' => '{{' . self::VARIABLE_EVENTVALUE . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            ),
            self::TAG_PRODUCT_EVENT_REMOVE_FROM_CART => array
            (
                'name' => self::TAG_PRODUCT_EVENT_REMOVE_FROM_CART,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_REMOVE_FROM_CART]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'setTrackerName',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useDebugVersion',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Ecommerce'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableLinkId',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Remove from Cart'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableLinkId',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'evenValue',
                        'value' => '{{' . self::VARIABLE_EVENTVALUE . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            ),
            self::TAG_PRODUCT_EVENT_PRODUCT_IMPRESSIONS => array
            (
                'name' => self::TAG_PRODUCT_EVENT_PRODUCT_IMPRESSIONS,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_EVENT_IMPRESSION]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'setTrackerName',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useDebugVersion',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Ecommerce'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableLinkId',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Impression'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            ),
            self::TAG_GOOGLE_ANALYTICS => array
            (
                'name' => self::TAG_GOOGLE_ANALYTICS,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_ALL_PAGES]
                ),
                'tagFiringOption' => 'oncePerLoad',
                'type' => self::TYPE_TAG_UA,
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'setTrackerName',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useDebugVersion',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useHashAutoLink',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_PAGEVIEW'
                    ),
                    array(
                        'type' => 'boolean',
                        'key' => 'decorateFormsAutoLink',
                        'value' => "false"
                    ),
                    array(
                        'type' => 'boolean',
                        'key' => 'enableLinkId',
                        'value' => "false"
                    ),
                    array(
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    ),
                    array
                    (
                        'type' => 'list',
                        'key' => 'fieldsToSet',
                        'list' => array
                        (
                            array
                            (
                                'type' => 'map',
                                'map' => array
                                (
                                    array
                                    (
                                        'type' => 'template',
                                        'key' => 'fieldName',
                                        'value' => 'anonymizeIp'
                                    ),
                                    array
                                    (
                                        'type' => 'template',
                                        'key' => 'value',
                                        'value' => $ipAnonymization
                                    )
                                )
                            )
                        )
                    )
                )
            ),
            self::TAG_CHECKOUT_STEP_OPTION => array
            (
                'name' => self::TAG_CHECKOUT_STEP_OPTION,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_CHECKOUT_OPTION]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Ecommerce'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Checkout Option'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            ),
            self::TAG_CHECKOUT_STEP => array
            (
                'name' => self::TAG_CHECKOUT_STEP,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_CHECKOUT_STEPS]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Ecommerce'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Checkout'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            ),
            self::TAG_PROMOTION_IMPRESSION => array
            (
                'name' => self::TAG_PROMOTION_IMPRESSION,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_PROMOTION_VIEW]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Promotion'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Promotion View'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            ),
            self::TAG_PROMOTION_CLICK => array
            (
                'name' => self::TAG_PROMOTION_CLICK,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_PROMOTION_CLICK]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Ecommerce'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Promotion Click'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            ),
            self::TAG_PRODUCT_EVENT_ADD_TO_WISHLIST => array
            (
                'name' => self::TAG_PRODUCT_EVENT_ADD_TO_WISHLIST,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_ADD_TO_WISHLIST]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Ecommerce'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Wishlist'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            ),
            self::TAG_PRODUCT_EVENT_ADD_TO_COMPARE => array
            (
                'name' => self::TAG_PRODUCT_EVENT_ADD_TO_COMPARE,
                'firingTriggerId' => array
                (
                    $triggers[self::TRIGGER_ADD_TO_COMPARE]
                ),
                'type' => self::TYPE_TAG_UA,
                'tagFiringOption' => 'oncePerEvent',
                'parameter' => array
                (
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'nonInteraction',
                        'value' => "false"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'useEcommerceDataLayer',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventCategory',
                        'value' => 'Ecommerce'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackType',
                        'value' => 'TRACK_EVENT'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventAction',
                        'value' => 'Compare'
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'enableEcommerce',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'overrideGaSettings',
                        'value' => "true"
                    ),
                    array
                    (
                        'type' => 'boolean',
                        'key' => 'doubleClick',
                        'value' => $displayAdvertising
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'eventLabel',
                        'value' => '{{' . self::VARIABLE_EVENTLABEL . '}}'
                    ),
                    array
                    (
                        'type' => 'template',
                        'key' => 'trackingId',
                        'value' => '{{' . self::VARIABLE_UA_TRACKING . '}}'
                    )
                )
            )
        );

        return $tags;
    }

    /**
     * @param string $uaTrackingId
     * @return array
     */
    public function getVariablesList($uaTrackingId)
    {
        return $this->_getVariables($uaTrackingId);
    }

    /**
     * @return array
     */
    public function getTriggersList()
    {
        return $this->_getTriggers();
    }

    /**
     * @param boolean $ipAnonymization
     * @param boolean $displayAdvertising
     * @param array $triggersMapping
     * @return array
     */
    public function getTagsList($ipAnonymization, $displayAdvertising, $triggersMapping)
    {
        return $this->_getTags($triggersMapping, $ipAnonymization, $displayAdvertising);
    }
}
