/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */
var config = {};
if (typeof window.MGT_EM !== 'undefined') {
    config = {
        config: {
            mixins: {
                'Magento_Checkout/js/view/billing-address': {
                    'Magetop_Smtp/js/view/billing-address-mixins' : true
                },
                'Magento_Checkout/js/view/shipping': {
                    'Magetop_Smtp/js/view/shipping-mixins' : true
                }
            }
        }
    };
}
