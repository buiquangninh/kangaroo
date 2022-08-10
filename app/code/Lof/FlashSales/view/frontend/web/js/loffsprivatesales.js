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
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    $.widget('mage.loffsPrivateSales', {
        options: {},

        _create: function () {
            if (this.element) {
                const $productClass = this.element.parents(this.options['product_item_selector']),
                    $productInfoClass = $(this.options['product_info_selector']),
                    $actionClass = $productClass.find(this.options['actions_selector']),
                    $actionButton = $productClass.find('.actions-primary'),
                    $stockText = $actionButton.find('.stock'),
                    html = $(this.options['html']);

                if (!$productClass) {
                    return;
                }

                if (!this.options['price_code'] && this.options['price_code'] !== 'wishlist_configured_price') {
                    if ($actionButton[0]) {
                        $actionButton[0].prepend(html[2]);
                        $actionButton[0].prepend(html[0]);
                        eval($('<div>').html(html[2]).find('script').html())
                        $stockText.remove();
                    }
                }

                if (this.options['price_code'] === 'final_price') {
                    if ($productInfoClass[0]) {
                        $productInfoClass[0].append(html[2]);
                        $productInfoClass[0].append(html[0]);
                        eval($('<div>').html(html[2]).find('script').html())
                    }
                }

                if (this.options['price_code'] === 'wishlist_configured_price') {
                    if ($actionClass[0]) {
                        $actionClass[0].prepend(html[2]);
                        $actionClass[0].prepend(html[0]);
                        eval($('<div>').html(html[2]).find('script').html())
                    }
                }
            }
        }
    });

    return $.mage.loffsPriceBox;
});
