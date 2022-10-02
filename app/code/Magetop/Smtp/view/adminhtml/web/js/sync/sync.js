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
    'jquery',
    'Magetop_Smtp/js/model/sync'
], function ($, Sync) {
    'use strict';

    $.widget('magetop.sync', {
        options: {
            ajaxUrl: '',
            websiteId: '',
            storeId: '',
            estimateUrl: '',
            buttonElement: '#email_marketing_general_synchronization_sync',
            saveLog: '#email_marketing_general_synchronization_sync_log',
            prefix: '#mp-synchronize',
            console: '.email_marketing_general_synchronization_sync_console'
        },
        _create: function () {
            var self = this;

            $(this.options.buttonElement).click(function (e) {
                e.preventDefault();
                Sync.process(self.options);
            });

            $(this.options.saveLog).click(function (e) {
                e.preventDefault();
                Sync.saveLog(self.options.console);
            });
        },
    });

    return $.magetop.sync;
});
