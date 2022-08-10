define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';
    rendererList.push(
        {
            type: 'kcoin',
            component: 'Magenest_StoreCredit/js/view/payment/method-renderer/kcoin'
        }
    );
    return Component.extend({});
});
