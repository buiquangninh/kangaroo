define(['jquery',
        'uiComponent',
        'ko',
        'Magento_Ui/js/modal/alert',
        'mage/translate',
        'mage/url'],
    function ($, Component, ko, alert, $t, urlBuilder) {
        'use strict';

        function redirectWallet() {
            var url = urlBuilder.build('voucher/wallet/');
            return location.href = url;
        }

        function claimCoupon(rule) {
            var url = urlBuilder.build('voucher/coupon/claimbypoint');
            var tmp = $('#claim' + rule);
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    rule: rule,
                },
                success: function (response) {
                    if (response.success && response.success) {
                        tmp.removeClass('button-coupon-claim').addClass('button-coupon-claimed');
                        tmp.attr('disabled', 'true');
                    }
                    alert({
                        title: $t('Redeem successful!'),
                        modalClass: 'alert',
                        buttons: [
                            {
                                text: $.mage.__('Close'),
                                class: 'action primary accept',
                                id: 'button_yes',
                                click: function () {
                                    this.closeModal(true);
                                    redirectWallet();
                                }
                            }]
                    })
                },
                error: function (xhr, status, errorThrown) {
                    console.log('Error happens. Try again.');
                }
            });
        }

        return Component.extend({
            initialize: function () {
                this._super();
            },
            getCoupon: function (rule) {
                var self = this;
                alert({
                    title: $t('Confirmed exchange of points?'),
                    modalClass: 'alert',
                    buttons: [
                        {
                            text: $.mage.__('Verify'),
                            class: 'action primary accept',
                            id: 'button_yes',
                            click: function () {
                                this.closeModal(true);
                                claimCoupon(rule);
                            }
                        },
                        {
                            text: $.mage.__('Cancel'),
                            class: 'action primary decline',
                            id: 'button_no',
                            click: function () {
                                this.closeModal(true);
                            }
                        }]
                });
            },
        });
    }
);
