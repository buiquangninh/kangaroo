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
    "jquery",
    'jquery-ui-modules/widget',
], function ($) {
    'use strict';

    $.widget('mage.loffsProductGroupCountDown', {
        /** @inheritdoc */
        _create: function () {
            var $groupSelector = $(this.options.loffsGroupSelector),
                ajaxUrl = this.options.ajaxUrl,
                productIds = [];

            $($groupSelector).each(function () {
                productIds.push($(this).data('product-id').toString())
            });

            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                data: {
                    product_ids: productIds
                },
                showLoader: true
            }).done(function (data) {
                $($groupSelector).each(function () {
                    var productId = $(this).data('product-id');
                    if (data.output) {
                        if (data.output[productId]) {
                            $(this).append(data.output[productId])
                        }
                    }
                });
            })
        },
    });

    return $.mage.loffsProductGroupCountDown;
});
