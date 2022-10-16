define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url',
        'Magento_Ui/js/model/messageList'
    ],
    function (
        $,
        Component,
        additionalValidators,
        redirectOnSuccessAction,
        urlBuilder,
        messageList
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magenest_MomoPay/payment/momo-wallet'
            },
            redirectAfterPlaceOrder: false,
            getCode: function () {
                return 'momo_wallet';
            },
            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);

                    this.getPlaceOrderDeferredObject()
                        .fail(function () {
                            self.isPlaceOrderActionAllowed(true);
                        }).done(function (orderId) {
                            $('body').trigger('processStart');
                            $.ajax({
                                url: urlBuilder.build('momo/order/AjaxGetPayUrl'),
                                dataType: "json",
                                type: 'POST',
                                showLoader: true,
                                data: {
                                    order_id: orderId
                                }
                            }).done(function (url) {
                                var history_url = urlBuilder.build('checkout/onepage/failure/order_id/' + orderId)
                                window.history.pushState({}, '', history_url);
                                history.pushState(null, null, history_url);
                                window.addEventListener('popstate', function (event) {
                                    window.location.href(history_url);
                                });
                                window.location.replace(url);
                            }).fail(function (err) {
                                console.log(err);
                                messageList.addErrorMessage({message: err.statusText});
                            });
                            self.afterPlaceOrder();
                            if (self.redirectAfterPlaceOrder) {
                                redirectOnSuccessAction.execute();
                            }
                        }
                    );
                    return true;
                }

                return false;
            },
        });
    }
);
