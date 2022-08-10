/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
], function ($, _, registry, Abstract) {
    'use strict';

    return Abstract.extend({
        saveCompanyVat: false,

        initialize: function () {
            var self = this;
            registry.async('checkout.steps.shipping-step.shippingAddress.after-form.additional-data.vat_information_wrapper.save_vat_invoice')(function (saveCompanyVat) {
                self.saveCompanyVat = saveCompanyVat.value;
            });
            return self._super();
        },

        validate: function () {
            if (!this.saveCompanyVat()) {
                return {
                    valid: true,
                    target: this
                };
            }

            return this._super();
        },
    });
});

