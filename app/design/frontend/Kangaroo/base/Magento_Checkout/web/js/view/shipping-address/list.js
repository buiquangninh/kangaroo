/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'uiLayout',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, _, ko, utils, Component, layout, addressList, checkoutData, selectShippingAddressAction, modal, $t) {
    'use strict';

    var defaultRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'Magento_Checkout/js/view/shipping-address/address-renderer/default',
        provider: 'checkoutProvider'
    };

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/list',
            visible: addressList().length > 0,
            rendererTemplates: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super()
                .initChildren();

            addressList.subscribe(function (changes) {
                    var self = this;

                    changes.forEach(function (change) {
                        if (change.status === 'added') {
                            self.createRendererComponent(change.value, change.index);
                        }
                    });
                },
                this,
                'arrayChange'
            );

            return this;
        },

        /** @inheritdoc */
        initConfig: function () {
            this._super();
            // the list of child components that are responsible for address rendering
            this.rendererComponents = [];

            return this;
        },

        /** @inheritdoc */
        initChildren: function () {
            _.each(addressList(), this.createRendererComponent, this);

            return this;
        },

        /**
         * Create new component that will render given address in the address list
         *
         * @param {Object} address
         * @param {*} index
         */
        createRendererComponent: function (address, index) {
            var rendererTemplate, templateData, rendererComponent;

            if (index in this.rendererComponents) {
                this.rendererComponents[index].address(address);
            } else {
                // rendererTemplates are provided via layout
                rendererTemplate = address.getType() != undefined && this.rendererTemplates[address.getType()] != undefined ? //eslint-disable-line
                    utils.extend({}, defaultRendererTemplate, this.rendererTemplates[address.getType()]) :
                    defaultRendererTemplate;
                templateData = {
                    parentName: this.name,
                    name: index
                };
                rendererComponent = utils.template(rendererTemplate, templateData);
                utils.extend(rendererComponent, {
                    address: ko.observable(address)
                });
                layout([rendererComponent]);
                this.rendererComponents[index] = rendererComponent;
            }
        },

        showAddressAction: function () {
            this.toggleAddressAction();
        },

        selectAddressAction: function () {
            this.toggleAddressAction();
            var address = window.checkoutConfig.selectedAddress;
            if (!address) {
                var address = window.checkoutConfig.originalSelectedAddress;
            }
            selectShippingAddressAction(address);
            checkoutData.setSelectedShippingAddress(address);
        },

        resetAddressAction: function () {
            this.toggleAddressAction();
            var address = window.checkoutConfig.originalSelectedAddress;
            selectShippingAddressAction(address);
            checkoutData.setSelectedShippingAddress(address.getKey());
        },

        toggleAddressAction: function () {
            $('.delivery-address-list > .field.addresses').slideUp(300);
            $('.checkout-shipping-search').slideToggle(300);
            $('.checkout-delivery-address-action').slideDown(300);
        },

        showPopupAddressAction: function () {
            var options = {
                type: 'popup',
                responsive: true,
                modalClass: 'popup-delivery-address',
                title: $t('My Address'),
                buttons: [{
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };

            var popup = modal(options, $('.checkout-shipping-search'));

            popup.openModal();
        }
    });
});
