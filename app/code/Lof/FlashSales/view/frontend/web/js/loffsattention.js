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
    'uiClass',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, Class, alert) {
    'use strict';

    var loffsAttention = Class.extend({
        product:{},

        addAlertData: function (product) {
            this._create(product)
        },

        _create: function (product) {
            const $el = this;
            $('[data-product-id="' + product['product_id'] + '"][data-loffsattention="loff-attention-popup"]').on('click',function () {
                $el._showPopup(product);
            })
        },

        _showPopup: function (product) {
           const message =  product['message'] === ''
               ? $.mage.__('You cannot add "%1" to the cart.').replace('%1', product['name'])
               : product['message'];
            alert({
                title: $.mage.__('Attention'),
                content: message,
                actions: {
                    always: function(){}
                },
                buttons: [{
                    text: $.mage.__('OK'),
                    class: 'action-primary action-accept',

                    click: function () {
                        this.closeModal(true);
                    }
                }]
            });
        }
    });

    window.loffsAttention = loffsAttention();

    return window.loffsAttention;
});
