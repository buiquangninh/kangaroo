/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function ($, _, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            captionValue: 'tn',
            imports: {
                update: '${ $.parentName }.city_id:value'
            }
        },

        /**
         * Set region to customer address form
         */
        setDifferedFromDefault: function () {
            this._super();

            registry.async(this.parentName + '.' + 'district')(function (element) {
                element.setVisible(false);
            });
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            var self = this,
                option = _.find(this.options(), function (item) {
                    return item.value == self.value();
                });

            if(!_.isUndefined(option)){
                this.source.set(this.dataScope.replace('_id', ''), option.full_name);
            }
            $('.directory-information select[name="ward_id"]').siblings('.nice-select').remove();
            $('.directory-information select[name="ward_id"]').niceSelect();
            this.toggleNextElement($('.directory-information select[name="ward_id"]').siblings('.nice-select'));
        },

        initNiceSelect: function () {
            $('.directory-information select[name="district_id"]').niceSelect();
        },

        toggleNextElement: function (s) {
            $(".nice-select").not(s).removeClass("open");
            s.toggleClass("open");
        }
    });
});

