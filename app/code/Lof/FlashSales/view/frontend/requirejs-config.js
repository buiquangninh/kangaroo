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

var config = {
    map: {
        '*': {
            loffsCounterTimer: 'Lof_FlashSales/js/loffscountdown',
            loffsProductCounterTimer: 'Lof_FlashSales/js/loffs-countdown-product',
            loffsProductGroupCounterTimer: 'Lof_FlashSales/js/loffs-countdown-product-group',
            countDown: 'Lof_FlashSales/js/jquery.countdown.min'
        }
    },
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Lof_FlashSales/js/loffs-swatch-renderer-mixin': true
            }
        }
    },
    paths: {
        countDown: 'Lof_FlashSales/js/jquery.countdown.min',
    },
    shim: {
        countDown: {
            deps: ['jquery'],
        },
        loffsCounterTimer: {
            deps: ['jquery'],
        }
    }
};
