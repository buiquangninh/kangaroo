/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function (Component, quote, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_SalesRule/summary/discount'
        },
        totals: quote.getTotals(),

        /**
         * @return {*|Boolean}
         */
        isDisplayed: function () {
            return this.isFullMode() && this.getPureValue() != 0; //eslint-disable-line eqeqeq
        },

        /**
         * @return {*}
         */
        getCouponCode: function () {
            if (!this.totals()) {
                return null;
            }

            return this.totals()['coupon_code'];
        },

        /**
         * @return {*}
         */
        getCouponLabel: function () {
            if (!this.totals()) {
                return null;
            }

            return this.totals()['coupon_label'];
        },

        /**
         * Get discount title
         *
         * @returns {null|String}
         */
        getTitle: function () {
            var discountSegments;

            if (!this.totals()) {
                return null;
            }

            discountSegments = this.totals()['total_segments'].filter(function (segment) {
                return segment.code.indexOf('discount') !== -1;
            });

            return discountSegments.length ? discountSegments[0].title : null;
        },

        /**
         * @return {Number}
         */
        getPureValue: function () {
            var price = 0;

            if (this.totals() && this.totals()['discount_amount']) {
                price = parseFloat(this.totals()['discount_amount'] + this.totals()['shipping_discount_amount']);
            }

            return price;
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            return this.getFormattedPrice(this.getPureValue());
        },

        getShippingDiscountValue: function () {
            var price = 0;

            if (this.totals() && this.totals()['shipping_discount_amount']) {
                price = parseFloat(-this.totals()['shipping_discount_amount']);
            }
            if (this.totals()['shipping_discount_amount'] <= 0) {
                var method = quote.shippingMethod();
                var amount = method.amount;
                if (method.extension_attributes.discount_price) {
                    amount = method.extension_attributes.discount_price;
                }
                price = amount - method.extension_attributes.original_price;
                price = parseFloat(price);
            }

            return this.getFormattedPrice(price);
        },
        isShippingDisplayed: function () {
            var method = quote.shippingMethod();
            if (method && method.extension_attributes) {
                var amount = method.amount;
                if (method.extension_attributes.discount_price) {
                    amount = method.extension_attributes.discount_price;
                }
                return amount < method.extension_attributes.original_price;
            }
            return false;
        },
        getShippingDiscountTitle: function () {
            return $t('Shipping Discount')
        }
    });
});
