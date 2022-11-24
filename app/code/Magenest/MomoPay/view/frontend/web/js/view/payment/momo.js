define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'momo_wallet',
                component: 'Magenest_MomoPay/js/view/payment/method-renderer/momo-wallet'
            },
            {
                type: 'momo_atm',
                component: 'Magenest_MomoPay/js/view/payment/method-renderer/momo-atm'
            },
            {
                type: 'momo_cc',
                component: 'Magenest_MomoPay/js/view/payment/method-renderer/momo-cc'
            },
            {
                type: 'momo_vts',
                component: 'Magenest_MomoPay/js/view/payment/method-renderer/momo-vts'
            },
            // other payment method renderers if required
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
