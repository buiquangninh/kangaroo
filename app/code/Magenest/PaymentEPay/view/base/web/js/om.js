define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/modal',
    'mage/dataPost',
    'mageUtils',
    'underscore',
    'mage/template',
    'jquery/ui',
    'mage/validation',
    'mage/mage',
    'mage/backend/form',
    'mage/calendar',
    'jquery/validate'
], function ($, confirm, modal, dataPost, utils, _) {
    'use strict';

    $.widget('mage.om', {
        options: {
            confirmDialog: null,
            cancelDialog: null,
            storeDialog: null,
            confirmForm: null,
            storeForm: null,
            cancelForm: null
        },

        /**
         * @private
         */
        _create: function () {
            var self = this;
            this._super();

            return this;
        },

        /**
         * Show confirm dialog
         *
         * @param message
         * @param action
         */
        showConfirmOnePartPaymentDialog: function (message, action) {
            if (!this.options.confirmDialog) {
                this.options.confirmDialog = modal({
                    title: $.mage.__(message),
                    innerScroll: true,
                    modalClass: '_image-box',
                    buttons: [
                        {
                            text: $.mage.__('Cancel'),
                            class: 'action action-hide-popup action-dismiss',
                            click: function () {
                                this.closeModal();
                            }
                        },
                        {
                            text: $.mage.__('Confirm'),
                            class: 'action primary action-accept',
                            click: function () {
                                    $('body').trigger('processStart');
                                    console.log(action);
                                    document.location.href = action;
                                    this.closeModal();
                            }
                        }
                    ],
                    clickableOverlay: true
                });
            }

            this.options.confirmDialog.openModal();
        }
    });

    return $.mage.om;
});
