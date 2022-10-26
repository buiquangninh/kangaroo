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
    "jquery",
    'moment',
    'uiClass',
    'countDown',
    'moment-timezone-with-data',
    'jquery-ui-modules/widget',
], function ($, moment, Class) {
    'use strict';
    var loffsCounterTimer = Class.extend({

        initCountDown: function (timezone) {
            $('.js-loffs-countdown').each(function () {
                var currentDate = new Date();
                var $el = $(this),
                    finalDate = $el.data('countdown'),
                    convertTime = moment.tz(finalDate, timezone),
                    format = $el.data('format');
                $el.countdown(convertTime.toDate())
                    .on('update.countdown', function (event) {
                        if (Math.round((convertTime.toDate() - currentDate)/(1000 * 3600 * 24)-1) < 1) {
                            $('#day_checker').remove();
                        }
                        $el.html(event.strftime(format));
                    })
                    .on('finish.countdown', function (event) {
                        $el.html('').parent().addClass('disabled');
                    });
            })
        }
    });
    window.loffsCounterTimer = loffsCounterTimer();

    return window.loffsCounterTimer;
});
