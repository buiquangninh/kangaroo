/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_FlashSales
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/insert-form',
    'underscore',
    'mageUtils'
], function ($, Insert, _, utils) {
    'use strict';

    return Insert.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                appliedProductsListing: '${ $.appliedProductsListingProvider }',
                appliedProductsModal: '${ $.appliedProductsModalProvider }'
            }
        },


        /**
         * @param {Object} responseData
         */
        onResponse: function (responseData) {
            if (!responseData.error) {
                this.appliedProductsModal().closeModal();
                this.appliedProductsListing().reload({
                    refresh: true
                });
            }
        },


        /**
         * @param {Object} params
         * @param {Array} ajaxSettings
         * @returns {*}
         */
        requestData: function (params, ajaxSettings) {
            if (params.make_post_request) {
                delete params.make_post_request;
                var query = utils.copy(params);

                ajaxSettings = _.extend({
                    url: this['update_url'],
                    method: 'POST',
                    data: query,
                    dataType: 'json'
                }, ajaxSettings);

                this.loading(true);

                return $.ajax(ajaxSettings);
            }

            return this._super(params, ajaxSettings);
        }

    });
});
