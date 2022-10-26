/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'ko',
    'jquery',
    'uiRegistry',
    'Magento_Checkout/js/model/sidebar',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/action/select-billing-address'
], function (Component, ko, $, registry, sidebarModel, quote, priceUtils, checkoutData, shippingService, selectShippingMethodAction, selectBillingAddress) {
    'use strict';

    return Component.extend({
        shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item',

        rates: shippingService.getShippingRates(),
        selectedShippingMethod: quote.shippingMethod(),
        methodListProvider: false,
        selectedPaymentMethod: ko.observable('Select Payment Method'),
        customerNote: {
            value: ko.observable('')
        },
        discountComponent: false,
        shipping: false,

        initialize: function () {
            var self = this;
            self._super();
            quote.shippingMethod.subscribe(function (value) {
                self.selectedShippingMethod = value;
                $('input#' + value.carrier_code + '_' + value.method_code).attr('checked', true);
            });

            selectBillingAddress(quote.shippingAddress());
            quote.shippingAddress.subscribe(function (value) {
                selectBillingAddress(value);
            });

            quote.paymentMethod.subscribe(function (value) {
                if (value !== null) {
                    $('input#' + value.method).attr('checked', true);
                    registry.async('checkout.steps.billing-step.payment.payments-list.' + value.method)(function (method) {
                        self.selectedPaymentMethod(method.getTitle());
                        $('.payment-method-title  #' + value.method).attr('checked', true);
                    })
                }
            });

            registry.async('checkout.steps.billing-step.payment.payments-list')(function (methodListProvider) {
                self.methodListProvider = methodListProvider;
            });

            registry.async('checkout.steps.shipping-step.shippingAddress.after-form.additional-data.customer_note')(function (customerNote) {
                self.customerNote = customerNote;
            });

            registry.async('checkout.sidebar.summary.totals.discount')(function (discountComponent) {
                self.discountComponent = discountComponent;
            });

            registry.async('checkout.steps.shipping-step.shippingAddress')(function (shipping) {
                self.shipping = shipping;
            });

            return self;
        },

        setModalElement: function (element) {
            sidebarModel.setPopup($(element));
        },

        showPopupDiscount: function () {
            $('.payment-option.opc-payment-additional.discount-code').modal('openModal')
        },

        getShippingMethod: function () {
            return quote.shippingMethod() ? quote.shippingMethod()['method_title'] : null;
        },

        getShippingCarrier: function () {
            return quote.shippingMethod() ? quote.shippingMethod()['carrier_title'] : null;
        },

        getShippingPrice: function () {
            return quote.shippingMethod() ? this.getFormattedPrice(quote.shippingMethod()['price_excl_tax']) : null;
        },

        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },

        selectShippingMethod: function (method, ele) {
            this.selectedShippingMethod = method;
            $('.payment-step-shipping').attr('checked', false);
            $(ele).find('input.payment-step-shipping').attr('checked', true);
        },

        submitShippingMethod: function () {
            selectShippingMethodAction(this.selectedShippingMethod);
            this.shipping.setShippingInformation();
            checkoutData.setSelectedShippingRate(this.selectedShippingMethod['carrier_code'] + '_' + this.selectedShippingMethod['method_code']);
        },

        getItemsCount: function () {
            return quote.getItems() ? quote.getItems().length : 0;
        },

        getSubtotal: function () {
            return this.getFormattedPrice(quote.totals() ? quote.totals()['subtotal_incl_tax'] : 0);
        },

        getShippingDiscount: function () {
            return this.getFormattedPrice(quote.totals() ? quote.totals()['shipping_discount_amount'] : 0);
        },

        getGrandTotal: function () {
            return this.getFormattedPrice(quote.totals() ? quote.totals()['grand_total'] : 0);
        },

        selectPaymentMethod: function (method) {
            this.selectedPaymentMethod(method.getTitle());
            return true;
        },

        getDiscountTitle: function () {
            return this.discountComponent.getTitle();
        },

        getDiscountValue: function () {
            return this.getFormattedPrice(this.discountComponent ? this.discountComponent.getPureValue() : 0);
        },

        getCouponCode: function () {
            return this.discountComponent.getCouponCode();
        },

        isDiscountDisplayed: function () {
            return this.discountComponent && this.discountComponent.isDisplayed();
        },

        isDiscounted: function (item) {
            return item &&
                item['extension_attributes'] &&
                item['extension_attributes']['original_price'] &&
                (item['price_excl_tax'] !== item['extension_attributes']['original_price']
                || item['extension_attributes']['discount_price'] > 0 && item['extension_attributes']['discount_price'] !== item['extension_attributes']['original_price']
                ); //eslint-disable-line
        },

        getPrice: function (item) {
            var price = 0;
            if (item && item['extension_attributes'] && item['extension_attributes']['discount_price'] > 0) {
                price = item['extension_attributes']['discount_price'];
            } else {
                price = item.price_excl_tax;
            }
            return price;
        }
    });
});
