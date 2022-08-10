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
    'Magento_Ui/js/grid/massactions',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'jquery',
    'mage/translate',
    'uiRegistry'
], function (Massactions, uiAlert, _, $, $t, uiRegistry) {
    'use strict';

    return Massactions.extend({
        defaults: {
            ajaxSettings: {
                method: 'POST',
                dataType: 'json'
            },
            listens: {
                massaction: 'onAction'
            }
        },

        /**
         * Reload data listing
         *
         * @param {Object} data
         */
        onAction: function (data) {
            if (data.action === 'massdelete_qty_limit') {
                this.source.reload({
                    refresh: true
                });
            }

            if (data.action === 'massdelete_discount_amount') {
                this.source.reload({
                    refresh: true
                });
            }
        },


        /**
         * Get Flash sale id by route path
         *
         * @returns {string|null}
         */
        getFlashSalesId: function () {
            var fullPath = window.location.pathname,
                /* matched "['/id/123', '123']" */
                fullMatch = fullPath.match(/(?:\/id\/)(\d*)/);
            if (fullMatch) {
                return fullMatch[1];
            }
            return false;
        },


        /** @inheritdoc */
        _getCallback: function (action, selections) {
            if (action.type === "update") {
                var itemsType = selections.excludeMode ? 'excluded' : 'selected',
                    data = {};

                data[itemsType] = selections[itemsType];
                if (!data[itemsType].length) {
                    data[itemsType] = false;
                }
                _.extend(data, selections.params || {});
                var flashSalesId = this.getFlashSalesId(),
                    callbacks = [
                        {
                            provider: 'lof_flashsales_form.areas.applied_products.applied_products.lof_multiple_applied_products_update_modal.update_lof_multiple_appliedproducts_form_loader',
                            target: 'destroyInserted'
                        },
                        action.callback,
                        {
                            provider: 'lof_flashsales_form.areas.applied_products.applied_products.lof_multiple_applied_products_update_modal.update_lof_multiple_appliedproducts_form_loader',
                            target: 'render',
                            lof_params: {
                                flashsales_id: flashSalesId,
                                data: data,
                                make_post_request: true
                            }
                        }
                    ],
                    args = [];

                return function () {
                    _.each(callbacks, function (loffse) {
                        args = [action, selections];
                        if (loffse.lof_params) {
                            args.unshift(loffse.lof_params);
                        }
                        args.unshift(loffse.target);
                        var callback = uiRegistry.async(loffse.provider);
                        callback.apply(null, args);
                    });
                }
            } else {
                return this._super(action, selections);
            }
        },


        /**
         * Default action callback. Send selections data
         * via POST request.
         *
         * @param {Object} action - Action data.
         * @param {Object} data - Selections data.
         */
        defaultCallback: function (action, data) {
            var itemsType, selections,
            flashsalesId = this.getFlashSalesId();
            if (flashsalesId && action.isAjax) {
                itemsType = data.excludeMode ? 'excluded' : 'selected';
                selections = {};

                selections[itemsType] = data[itemsType];
                selections['flashsales_id'] = flashsalesId;

                if (!selections[itemsType].length) {
                    selections[itemsType] = false;
                }

                _.extend(selections, data.params || {});
                this.request(action.url, selections).done(function (response) {
                    if (!response.error) {
                        this.trigger('massaction', {
                            action: action.type,
                            data: selections
                        });
                    }
                }.bind(this));
            } else {
                this._super();
            }
        },

        /**
         * Send data listing mass action ajax request
         *
         * @param {String} href
         * @param {Object} data
         */
        request: function (href, data) {
            var settings = _.extend({}, this.ajaxSettings, {
                url: href,
                data: data
            });

            $('body').trigger('processStart');

            return $.ajax(settings)
                .done(function (response) {
                    if (response.error) {
                        uiAlert({
                            content: response.message
                        });
                    }
                })
                .fail(function () {
                    uiAlert({
                        content: $t('Sorry, there has been an error processing your request. Please try again later.')
                    });
                })
                .always(function () {
                    $('body').trigger('processStop');
                });
        }
    });
});
