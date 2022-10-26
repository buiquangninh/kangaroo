define(['Magento_Customer/js/customer-data'], function (customerData) {
    return function () {
        const setAnalyticsData = function (value) {
            if (typeof(_cdp365Analytics) === 'undefined') {
                _cdp365Analytics = {};
            }
            _cdp365Analytics.user_identify = {
                login_id: value.customer_id,
                user_name: value.fullname,
                phone: value.phone_number,
                email: value.email
            }
        }

        let customer = customerData.get('customer');
        setAnalyticsData(customer());
        customer.subscribe(value => setAnalyticsData(value));
    };
});
