/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_Smtp
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */
define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "mage/translate",
    "jquery/ui"
], function ($, alert, $t) {
    "use strict";

    $.widget('magetop.testconnection', {
        options: {
            ajaxUrl: '',
            testConnection: '#email_marketing_general_test_connection',
            appID: '#email_marketing_general_app_id',
            secretKey: '#email_marketing_general_secret_key',
        },
        _create: function () {
            var self = this;

            $(this.options.testConnection).click(function (e) {
                e.preventDefault();
                self._ajaxSubmit();
            });
        },

        _ajaxSubmit: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                data: {
                    appID: $(this.options.appID).val(),
                    secretKey: $(this.options.secretKey).val()
                },
                dataType: 'json',
                showLoader: true,
                success: function (result) {
                    alert({
                        title: result.status ? $t('Success') : $t('Error'),
                        content: result.content
                    });
                }
            });
        }
    });

    return $.magetop.testconnection;
});
