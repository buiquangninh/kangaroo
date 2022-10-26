define(['jquery', 'Magento_Ui/js/modal/confirm', "Magento_Customer/js/customer-data"], function ($, confirmation, customerData) {
    'use strict';

    var widgetMixin = {
        submitForm: function (form) {
            if (form.find("#is_preorder").val() === '1' || this.isCartPreorder()) {
                if (form.find("#is_preorder").val() === '1') {
                    this.options.addToCartButtonTextDefault = $('button#product-addtocart-button').attr('title');
                }
                confirmation({
                    title: $.mage.__('Confirm'),
                    content: $.mage.__('To add this product, the current cart has to be cleared. Do you wish to continue?'),
                    actions: {
                        confirm: () => this.ajaxSubmit(form)
                    }
                });
            } else {
                this.ajaxSubmit(form);
            }
        },

        isCartPreorder: function () {
            let cart = customerData.get('cart')();
            return cart.items.some((item) => item.is_preorder == 1);
        }
    };

    return function (targetWidget) {
        $.widget('mage.catalogAddToCart', targetWidget, widgetMixin);
        return $.mage.catalogAddToCart;
    };
});
