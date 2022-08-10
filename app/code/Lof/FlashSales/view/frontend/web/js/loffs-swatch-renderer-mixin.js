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
    'underscore',
    'jquery',
], function (_, $) {
    'use strict';

    return function (targetWidget) {
        $.widget('mage.SwatchRenderer', targetWidget, {
            _OnClick: function ($this, $widget) {
                this._super($this, $widget)

                $widget._loadCounterTimer();
                $widget._loadDiscountAmount();
            },

            _loadDiscountAmount: function () {
                var childProductData = this.options.jsonConfig.loffsChildProductData,
                    loffsDiscountAmountSelector = $('.loffs-discount-amount');

                if ($('body.catalog-product-view').length > 0) {
                    if (loffsDiscountAmountSelector.length > 0 && childProductData[this.getProductId()]) {
                        loffsDiscountAmountSelector.html(childProductData[this.getProductId()][0]['discount_amount_html'])
                    }
                }
            },

            _loadCounterTimer: function () {
                var $loffsCountDownSelector = $('.loffs-product-countdowntimer'),
                    $loffsPageMainSelector = $(this.options.jsonConfig.loffsTopContainerSelector),
                    $loffsProductInfoPriceSelector = $(this.options.jsonConfig.loffsProductInfoPriceSelector),
                    swatchOptionsUrl = this.options.jsonConfig.loffsSwatchOptionsUrl,
                    childProductData = this.options.jsonConfig.loffsChildProductData,
                    productHeaderStyle = this.options.jsonConfig.loffsProductHeaderStyle,
                    loffsSwatchOptions;
                $loffsCountDownSelector.remove()
                if (!swatchOptionsUrl) {
                    return;
                }
                if (childProductData[this.getProduct()]) {
                    loffsSwatchOptions = {
                        'product_id': this.getProduct()
                    }
                }

                $.ajax({
                    url: swatchOptionsUrl,
                    cache: true,
                    type: 'GET',
                    dataType: 'json',
                    data: loffsSwatchOptions,
                    showLoader: true
                }).done(function (data) {
                    $loffsCountDownSelector.remove()
                    if (productHeaderStyle === '1') {
                        $loffsPageMainSelector.prepend(data.output);
                    }
                    if (productHeaderStyle === '2') {
                        $loffsProductInfoPriceSelector.append(data.output);
                    }
                    if (productHeaderStyle === '3') {
                        $loffsProductInfoPriceSelector.prepend(data.output);
                    }
                });
            },

        });

        return $.mage.SwatchRenderer;
    };
});
