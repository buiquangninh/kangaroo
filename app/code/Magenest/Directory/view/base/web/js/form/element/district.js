/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/lib/validation/validator'
], function ($, _, registry, Select, validator) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            captionValue: 'tn',
            imports: {
                update: '${ $.parentName }.city_id:value'
            },
            listens: {
                '${ $.parentName }.city_id:value': 'validate',
            },
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
            self.validate();
        },

        initNiceSelect: function () {
            $('.directory-information select[name="district_id"]').niceSelect();
        },

        toggleNextElement: function (s) {
            $(".nice-select").not(s).removeClass("open");
            s.toggleClass("open");
        },

        validate: function () {
            $('#directory-error').hide();

            var value = this.value(),
                result = validator(this.validation, value, this.validationParams),
                isValid = this.disabled() || !this.visible() || result.passed;

            this.error.valueHasMutated();
            //TODO: Implement proper result propagation for form
            if (this.source && !isValid) {
                $('#directory-error').show();
                this.source.set('params.invalid', true);
            }

            return {
                valid: isValid,
                target: this
            };
        },
    });
});

