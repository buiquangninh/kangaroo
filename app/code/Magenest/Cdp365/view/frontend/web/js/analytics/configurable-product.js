define(['jquery'], function ($) {
    'use strict';

    var cdpMixin = {

        /**
         * Added confirming for modal closing
         *
         * @returns {Element}
         */
        _CalcProducts: function ($skipAttributeId) {
            var result = this._super($skipAttributeId);
            $('body').trigger('update-configurable-options', [result]);
            return result;
        }
    };

    return function (targetWidget) {
        // Example how to extend a widget by mixin object
        $.widget('mage.SwatchRenderer', targetWidget, cdpMixin); // the widget alias should be like for the target widget

        return $.mage.SwatchRenderer; //  the widget by parent alias should be returned
    };
});
