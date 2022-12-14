/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license sliderConfig is
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
 * @copyright   Copyright (c) Magetop (http://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (record) {
            var label = this._super(record);

            if (label !== '') {
                if (record.status == 1) {
                    label = '<span class="grid-severity-notice"><span>' + label + '</span></span>';
                } else {
                    label = '<span class="grid-severity-minor"><span>' + label + '</span></span>';
                }
            }

            return label;
        }
    });
});

