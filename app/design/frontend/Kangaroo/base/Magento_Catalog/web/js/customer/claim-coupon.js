define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, customerData, alert, $t) {
    return function(config) {
        let customerCouponObs = customerData.get('customer_coupon');
        let customerCoupon = customerCouponObs().customer_coupon;
        if (customerCoupon) {
            reloadCouponState(customerCoupon);
        }
        customerCouponObs.subscribe(function (newValue) {
            reloadCouponState(typeof newValue.customer_coupon != "undefined" ? newValue.customer_coupon : []);
        });

        $('body').on('click', '.coupon-save-action:not(.action-saved)', function() {
            if (!customerData.get('customer')().fullname) {
                window.location.href = config.loginUrl;
                return;
            }
            let element = $(this);
            $.ajax({
                url: config.url,
                type: 'POST',
                dataType: 'json',
                data: {
                    coupon_code: element.data('coupon-code')
                },
                success: function(response) {
                    element.addClass('action-saved').html(config.savedLabel);

                    alert({
                        title: $t('Claim Coupon'),
                        content: response.message,
                    });
                },
                error: function (xhr, status, errorThrown) {
                    alert({
                        title: $t('Claim Coupon'),
                        content: errorThrown,
                    });
                }
            });
        });

        function reloadCouponState(customerCoupon) {
            $('.coupon-save-action').each((index, element) => {
                element = $(element);
                let couponCodeToClaim = element.data('coupon-code');
                if (customerCoupon.includes(couponCodeToClaim)) {
                    element.addClass('action-saved').html(config.savedLabel);
                }
            });
        }
    }
});
