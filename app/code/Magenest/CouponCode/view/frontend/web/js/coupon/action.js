define(['jquery',
        'uiComponent',
        'ko',
        'Magento_Ui/js/modal/alert',
        'mage/url'],
    function ($, Component, ko, alert, urlBuilder) {
        'use strict';
        return Component.extend({
            initialize: function () {
                this._super();
            },
            seeMore: function (data) {
                alert({
                    title: 'Description',
                    content: $.mage.__(data),
                    modalClass: 'alert',
                    buttons: [{
                        text: $.mage.__('Continue'),
                        class: 'action primary accept',
                        click: function () {
                            this.closeModal(true);
                        }
                    }]
                });
            },
            claimCoupon: function (couponCode) {
                var url = urlBuilder.build('voucher/coupon/claimajax');
                var tmp = $('#claim ' + couponCode );
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        couponCode: couponCode,
                    },
                    success: function(response) {
                        if (response.success && response.success) {
                            tmp.removeClass('button-coupon-claim').addClass('button-coupon-claimed');
                            tmp.attr('disabled', 'true');
                            window.location.reload();
                        }
                    },
                    error: function (xhr, status, errorThrown) {
                        console.log('Error happens. Try again.');
                    }
                });
            },
            warning: function (data) {
                alert({
                    title: 'Coupon',
                    content: 'Please login to claim coupon ' + data,
                    modalClass: 'alert',
                    buttons: [{
                        text: $.mage.__('Accept'),
                        class: 'action primary accept',
                        click: function () {
                            this.closeModal(true);
                        }
                    }]
                });
            }
        });
    }
);
