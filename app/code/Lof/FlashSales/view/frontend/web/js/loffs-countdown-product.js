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
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.loffsProductSimpleCountDown', {
        /** @inheritdoc */
        _create: function () {
            var $loffsPageMainSelector = $(this.options.loffsTopContainerSelector),
                $loffsProductInfoPriceSelector = $(this.options.loffsProductInfoPriceSelector),
                productHeaderStyle = this.options.loffsProductHeaderStyle,
                productId = this.options.productId,
                ajaxUrl = this.options.ajaxUrl;

            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                data: {
                    product_id: productId
                },
                showLoader: true
            }).done(function (data) {
                if (productHeaderStyle === '1') {
                    $loffsPageMainSelector.prepend(data.output);
                }
                if (productHeaderStyle === '2') {
                    $loffsProductInfoPriceSelector.append(data.output);
                }
                if (productHeaderStyle === '3') {
                    $loffsProductInfoPriceSelector.prepend(data.output);
                }
            })
        }
    });

    return $.mage.loffsProductSimpleCountDown;
});
