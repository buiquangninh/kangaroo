define([
    'jquery',
    'fotorama/fotorama',
    'underscore',
    'matchMedia',
    'mage/template',
    'text!mage/gallery/gallery.html',
    'uiClass',
    'mage/translate'
], function ($, fotorama, _, mediaCheck, template, galleryTpl, Class, $t) {
    'use strict';

    var mixin = {

        initGallery: function () {
            var breakpoints = {},
                settings = this.settings,
                config = this.config,
                tpl = template(galleryTpl, {
                    next: $t('Next'),
                    previous: $t('Previous')
                }),
                mainImageIndex,
                $element = settings.$element,
                $fotoramaElement,
                $fotoramaStage;

            if (settings.breakpoints) {
                _.each(_.values(settings.breakpoints), function (breakpoint) {
                    var conditions;

                    _.each(_.pairs(breakpoint.conditions), function (pair) {
                        conditions = conditions ? conditions + ' and (' + pair[0] + ': ' + pair[1] + ')' :
                            '(' + pair[0] + ': ' + pair[1] + ')';
                    });
                    breakpoints[conditions] = breakpoint.options;
                });
                settings.breakpoints = breakpoints;
            }

            _.extend(config, config.options,
                {
                    options: undefined,
                    click: false,
                    breakpoints: null
                }
            );
            settings.currentConfig = config;

            $element
                .css('min-height', settings.$element.height())
                .append(tpl);

            $fotoramaElement = $element.find('[data-gallery-role="gallery"]');

            $fotoramaStage = $fotoramaElement.find('.fotorama__stage');
            $fotoramaStage.css('position', 'absolute');

            $fotoramaElement.fotorama(config);
            $fotoramaElement.find('.fotorama__stage__frame.fotorama__active')
                .one('f:load', function () {
                    // Remove placeholder when main gallery image loads.
                    $element.find('.gallery-placeholder__image').remove();
                    $element
                        .removeClass('_block-content-loading')
                        .css('min-height', '');

                    $fotoramaStage.css('position', '');
                });
            settings.$elementF = $fotoramaElement;
            settings.fotoramaApi = $fotoramaElement.data('fotorama');

            $.extend(true, config, this.startConfig);

            // mainImageIndex = getMainImageIndex(config.data);
            //
            // if (mainImageIndex) {
            //     this.settings.fotoramaApi.show({
            //         index: mainImageIndex,
            //         time: 0
            //     });
            // }
        },
    };

    return function (target) { // target == Result that Magento_Ui/.../columns returns.
        return target.extend(mixin); // new result that all other modules receive
    };
});
