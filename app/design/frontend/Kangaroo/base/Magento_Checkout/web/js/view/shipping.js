/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/form/form',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data',
    'uiRegistry',
    'mage/translate',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/shipping-rate-service'
], function (
    $,
    _,
    Component,
    ko,
    customer,
    addressList,
    addressConverter,
    quote,
    createShippingAddress,
    selectShippingAddress,
    shippingRatesValidator,
    formPopUpState,
    shippingService,
    selectShippingMethodAction,
    rateRegistry,
    setShippingInformationAction,
    stepNavigator,
    modal,
    checkoutDataResolver,
    checkoutData,
    customerData,
    registry,
    $t,
    priceUtils,
    getPaymentInformation,
    selectBillingAddress
) {
    'use strict';

    var popUp = null;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping',
            shippingFormTemplate: 'Magento_Checkout/shipping-address/form',
            shippingMethodListTemplate: 'Magento_Checkout/shipping-address/shipping-method-list',
            shippingMethodItemTemplate: 'Magento_Checkout/shipping-address/shipping-method-item',
            imports: {
                countryOptions: '${ $.parentName }.shippingAddress.shipping-address-fieldset.country_id:indexedOptions'
            }
        },
        visible: ko.observable(!quote.isVirtual()),
        errorValidationMessage: ko.observable(false),
        isCustomerLoggedIn: customer.isLoggedIn,
        isFormPopUpVisible: formPopUpState.isVisible,
        isFormInline: addressList().length === 0,
        isNewAddressAdded: ko.observable(false),
        saveInAddressBook: 1,
        quoteIsVirtual: quote.isVirtual(),
        loginFormComponent: false,
        telephoneAddressField: false,
        cartCustomerMessage: ko.observable(""),
        saveCompanyVat: ko.observable(false),
        companyExtraInfo: false,
        shippingStep: false,
        customerAddress: ko.computed(function () {
            var address = $t('You do not have a shipping address. Please enter the address now');
            var customerAddress = quote.shippingAddress();
            if (customerAddress && customerAddress['firstname'] && customerAddress['customAttributes']['ward'] && customerAddress['customAttributes']['district']) {
                address = "<span class='customer-name'>" + customerAddress['firstname'] + ' ' + customerAddress['lastname'] + "</span>"
                    +"<span class='customer-telephone'>" + customerAddress['telephone'] + "</span>"
                    +"<span class='customer-address'>" + customerAddress['street'].join(', ') + " "
                    + customerAddress['customAttributes']['ward']['value'] + ", "
                    + customerAddress['customAttributes']['district']['value'] + ", "
                    + customerAddress['city'] + "</span>";
            }
            return address;
        }),
        showAddNewAddress: ko.observable(true),
        popup: false,

        /**
         * @return {exports}
         */
        initialize: function () {
            var self = this,
                hasNewAddress,
                fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

            this._super();

            if (!quote.isVirtual()) {
                stepNavigator.registerStep(
                    'shipping',
                    '',
                    $t('Shipping'),
                    this.visible, _.bind(this.navigate, this),
                    this.sortOrder
                );
            }
            getPaymentInformation();

            checkoutDataResolver.resolveShippingAddress();

            hasNewAddress = addressList.some(function (address) {
                return address.getType() == 'new-customer-address'; //eslint-disable-line eqeqeq
            });

            this.isNewAddressAdded(hasNewAddress);

            this.isFormPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });

            quote.shippingMethod.subscribe(function () {
                self.errorValidationMessage(false);
            });

            registry.async('checkoutProvider')(function (checkoutProvider) {
                var shippingAddressData = checkoutData.getShippingAddressFromData();
                if (shippingAddressData) {
                    checkoutProvider.set(
                        'shippingAddress',
                        $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                    );
                }
                checkoutProvider.on('shippingAddress', function (shippingAddrsData) {
                    if (shippingAddrsData.street && !_.isEmpty(shippingAddrsData.street[0])) {
                        checkoutData.setShippingAddressFromData(shippingAddrsData);
                    }
                });
                shippingRatesValidator.initFields(fieldsetName);
            });

            registry.async('checkout.steps.shipping-step.shippingAddress.after-form.additional-data.vat_information_wrapper')(function (companyExtraInfo) {
                self.companyExtraInfo = companyExtraInfo;
            });

            registry.async('checkout.steps.shipping-step.shippingAddress.after-form.additional-data.vat_information_wrapper.save_vat_invoice')(function (saveCompanyVat) {
                self.saveCompanyVat = saveCompanyVat.value;
            });

            registry.async('checkout.steps.shipping-step.shippingAddress')(function (shippingStep) {
                self.shippingStep = shippingStep;
            });

            registry.async('checkout.steps.shipping-step.shippingAddress.customer-email')(function (loginForm) {
                self.loginFormComponent = loginForm;
            });

            registry.async('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.telephone')(function (telephoneField) {
                self.telephoneAddressField = telephoneField;
            });

            registry.async('checkout.steps.shipping-step.shippingAddress.after-form.additional-data.customer_note')(function (customerNote) {
                self.cartCustomerMessage = customerNote.value;
            });

            return this;
        },

        /**
         * Navigator change hash handler.
         *
         * @param {Object} step - navigation step
         */
        navigate: function (step) {
            step && step.isVisible(true);
        },

        /**
         * @return {*}
         */
        getPopUp: function () {
            var self = this,
                buttons;

            if (!popUp) {
                buttons = this.popUpForm.options.buttons;
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                        class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                        click: self.saveNewAddress.bind(self)
                    },
                    {
                        text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                        class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',

                        /** @inheritdoc */
                        click: this.onClosePopUp.bind(this)
                    }
                ];

                /** @inheritdoc */
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                /** @inheritdoc */
                this.popUpForm.options.opened = function () {
                    // Store temporary address for revert action in case when user click cancel action
                    self.temporaryAddress = $.extend(true, {}, checkoutData.getShippingAddressFromData());
                };
                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },

        /**
         * Revert address and close modal.
         */
        onClosePopUp: function () {
            checkoutData.setShippingAddressFromData($.extend(true, {}, this.temporaryAddress));
            registry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.telephone').visible(false);
            this.getPopUp().closeModal();
        },

        /**
         * Show address form popup
         */
        showFormPopUp: function () {
            registry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.telephone').visible(true);
            this.isFormPopUpVisible(true);
        },

        /**
         * Save new shipping address
         */
        saveNewAddress: function () {
            var addressData,
                newShippingAddress;

            this.source.set('params.invalid', false);
            this.triggerShippingDataValidateEvent();

            if (!this.source.get('params.invalid')) {
                addressData = this.source.get('shippingAddress');
                // if user clicked the checkbox, its value is true or false. Need to convert.
                addressData['save_in_address_book'] = this.saveInAddressBook ? 1 : 0;

                // New address must be selected as a shipping address
                newShippingAddress = createShippingAddress(addressData);
                $('.selected-item').removeClass('selected-item').addClass('not-selected-item');
                selectShippingAddress(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress($.extend(true, {}, addressData));
                this.getPopUp().closeModal();
                this.isNewAddressAdded(true);
                this.popup.closeModal();
            }
        },

        /**
         * Shipping Method View
         */
        rates: shippingService.getShippingRates(),
        isLoading: shippingService.isLoading,
        isSelected: ko.computed(function () {
            return quote.shippingMethod() ?
                quote.shippingMethod()['carrier_code'] + '_' + quote.shippingMethod()['method_code'] :
                null;
        }),

        /**
         * @param {Object} shippingMethod
         * @return {Boolean}
         */
        selectShippingMethod: function (shippingMethod) {
            selectShippingMethodAction(shippingMethod);
            checkoutData.setSelectedShippingRate(shippingMethod['carrier_code'] + '_' + shippingMethod['method_code']);

            return true;
        },

        /**
         * Set shipping information handler
         */
        setShippingInformation: function () {
            if (this.validateShippingInformation()) {
                quote.billingAddress(null);
                checkoutDataResolver.resolveBillingAddress();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend(true, {}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                });

                selectBillingAddress(quote.shippingAddress());

                setShippingInformationAction().done(
                    function () {
                        stepNavigator.next();
                    }
                );
            }
        },

        /**
         * @return {Boolean}
         */
        validateShippingInformation: function (skipShippingMethod = false) {
            var shippingAddress,
                addressData,
                loginFormSelector = 'form[data-role=email-with-possible-login]',
                emailValidationResult = customer.isLoggedIn(),
                field,
                option = _.isObject(this.countryOptions) && this.countryOptions[quote.shippingAddress().countryId],
                messageContainer = registry.get('checkout.errors').messageContainer;

            if (!quote.shippingMethod() && !skipShippingMethod) {
                this.errorValidationMessage(
                    $t('The shipping method is missing. Select the shipping method and try again.')
                );

                return false;
            }

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                emailValidationResult = Boolean($(loginFormSelector).valid());
            }

            if (this.isFormInline) {
                this.source.set('params.invalid', false);
                this.triggerShippingDataValidateEvent();

                if (!skipShippingMethod) {
                    if (!quote.shippingMethod()['method_code']) {
                        this.errorValidationMessage(
                            $t('The shipping method is missing. Select the shipping method and try again.')
                        );
                    }

                    if (emailValidationResult &&
                        this.source.get('params.invalid') ||
                        !quote.shippingMethod()['method_code'] ||
                        !quote.shippingMethod()['carrier_code']
                    ) {
                        this.focusInvalid();

                        return false;
                    }
                }

                if (emailValidationResult &&
                    this.source.get('params.invalid')
                ) {
                    this.focusInvalid();

                    return false;
                }

                shippingAddress = quote.shippingAddress();
                addressData = addressConverter.formAddressDataToQuoteAddress(
                    this.source.get('shippingAddress')
                );

                //Copy form data to quote shipping address object
                for (field in addressData) {
                    if (addressData.hasOwnProperty(field) &&  //eslint-disable-line max-depth
                        shippingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(shippingAddress[field], addressData[field])
                    ) {
                        shippingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(shippingAddress[field], addressData[field])) {
                        shippingAddress = addressData;
                        break;
                    }
                }

                if (customer.isLoggedIn()) {
                    shippingAddress['save_in_address_book'] = 1;
                }
                selectShippingAddress(shippingAddress);
            } else if (customer.isLoggedIn() &&
                option &&
                option['is_region_required'] &&
                !quote.shippingAddress().region
            ) {
                messageContainer.addErrorMessage({
                    message: $t('Please specify a regionId in shipping address.')
                });

                return false;
            }

            if (!emailValidationResult) {
                $(loginFormSelector).focus();

                return false;
            }

            return true;
        },

        /**
         * Trigger Shipping data Validate Event.
         */
        triggerShippingDataValidateEvent: function () {
            this.source.trigger('shippingAddress.data.validate');

            if (this.source.get('shippingAddress.custom_attributes')) {
                this.source.trigger('shippingAddress.custom_attributes.data.validate');
            }
        },

        toggleLoginForm: function () {
            this.loginFormComponent.isPasswordVisible(true);
            $('#customer-email-fieldset').removeClass('fieldset-login-phone');
            $('.phone-login').hide();
            $('.mage-error[for=customer-email-telephone]').remove();
            var customerEmailMetaData = $('#customer-email-telephone').attr('data-validate', '{required:true, \'validate-custom-mob-email-rule\':true}')
                .data('validate', '{required:true, \'validate-custom-mob-email-rule\':true}').metadata();
            delete customerEmailMetaData.validate['validate-custom-mob-checkout-email-rule'];
            customerEmailMetaData.validate['validate-custom-mob-email-rule'] = true;

            this.telephoneAddressField.visible(true);
            $('.login-btn-container').remove();
            $('.form-shipping-address .street.admin__control-fields').addClass('w-100')
        },
        getISActive: function () {
            return window.checkoutConfig.payment.vnpt_epay_is.ISData.is_active == 1;
        },
        getBankName: function () {
            return window.checkoutConfig.payment.vnpt_epay_is.ISData.bank_name;
        },
        imagePath: function () {
            return window.checkoutConfig.payment.vnpt_epay_is.ISData.bank_image;
        },
        getTerms: function () {
            return window.checkoutConfig.payment.vnpt_epay_is.ISData.termIs;
        },
        getAmountIs: function () {
            return this.getFormattedPrice(window.checkoutConfig.payment.vnpt_epay_is.ISData.amountIs ?? 0);
        },
        getAmounts: function () {
            return this.getFormattedPrice(window.checkoutConfig.payment.vnpt_epay_is.ISData.amount ?? 0);
        },
        getNextAmount: function () {
            return this.getFormattedPrice(window.checkoutConfig.payment.vnpt_epay_is.ISData.nextAmount ?? 0);
        },
        getFirstAmount: function () {
            return this.getFormattedPrice(window.checkoutConfig.payment.vnpt_epay_is.ISData.firstAmount ?? 0);
        },
        getUserFeeIs : function () {
            return this.getFormattedPrice(window.checkoutConfig.payment.vnpt_epay_is.ISData.userFeeIs ?? 0);
        },

        openAddressPage: function () {
            window.location.href = window.checkoutConfig.defaultAddressUrl;
        },
        onEnter: function () {
            var searchInput = $("#search-address-input").val().toLowerCase();
            if (event.keyCode === 13 || (event.keyCode === 8 && !searchInput)) {
                this.searchAddressData();
            }
            return true;
        },

        searchAddressData: function () {
            var searchInput = $("#search-address-input").val().toLowerCase();
            var customerData = window.customerData;
            var found = 0;
            $('.address-entry').each(function (index, ele) {
                var innerText = $(ele).text();
                var replaced = innerText.replace(/\s+/g, ' ').toLowerCase();
                if (replaced.includes(searchInput)) {
                    $(ele).show();
                    found++;
                } else {
                    $(ele).hide();
                }
            });

            if (searchInput) {
                $('.result-total-num').html(found);
                $('.result-total-query').html('"' + $("#search-address-input").val() + '"');
                $('.result-total').show();
            } else {
                $('.result-total').hide();
            }
        },

        getTotalItems: function () {
            return quote.getItems().length + " " + $t('Items');
        },

        getSubtotal: function () {
            return priceUtils.formatPrice(quote.getTotals()()['base_grand_total'], quote.getPriceFormat());
        },

        getShippingPrice: function () {
            var item = quote.shippingMethod();
            var price = item.price_excl_tax;

            if (item && item['extension_attributes'] && item['extension_attributes']['discount_price'] > 0) {
                price -= item['extension_attributes']['discount_price'];
            }
            return quote.shippingMethod() ? priceUtils.formatPrice(price, quote.getPriceFormat()) : null;
        },

        isDiscounted: function () {
            var item = quote.shippingMethod();
            return item &&
                item['extension_attributes'] &&
                item['extension_attributes']['original_price'] &&
                (item['price_excl_tax'] !== item['extension_attributes']['original_price']
                    || item['extension_attributes']['discount_price'] > 0 && item['extension_attributes']['discount_price'] !== item['extension_attributes']['original_price']
                ); //eslint-disable-line
        },

        getShippingOriginalPrice: function () {
            return quote.shippingMethod() ? priceUtils.formatPrice(quote.shippingMethod()['extension_attributes']['original_price'], quote.getPriceFormat()) : null;
        },

        getShippingMethod: function () {
            return quote.shippingMethod() ? quote.shippingMethod()['method_title'] : null;
        },

        getShippingCarrier: function () {
            return quote.shippingMethod() ? quote.shippingMethod()['carrier_title'] : null;
        },

        validateForm: function () {
            var selected = true;
            this.saveCompanyVat(true);
            this.companyExtraInfo.elems().every( function (elem) {
                var validateResult = elem.validate();
                if (!validateResult || !validateResult.valid) {
                    selected = false;
                    return false;
                }
                return true;
            });
            this.saveCompanyVat(selected);
            if (selected) {
                this.shippingStep.setShippingInformation();
            }
        },
        unSaveCompanyVat: function () {
            this.saveCompanyVat(false);
        },
        setShowAddNewBtn: function () {
            this.showAddNewAddress(this.getRegion('address-list')()[0]._elems < 1);
        },
        toggleAddressAction: function () {
            $('.delivery-address-list > .field.addresses').hide();
            $('.checkout-shipping-search').slideToggle(300);
            $('.checkout-delivery-address-action').slideDown(300);
        },

        toggleAddressActionClose: function () {
            $('.delivery-address-list > .field.addresses').show();
            $('.checkout-shipping-search').hide();
            $('.checkout-delivery-address-action').hide();
        },

        showPopupAddressAction: function () {
            if (!this.popup) {
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

                this.popup = modal(options, $('.checkout-shipping-search'));
            }

            this.popup.openModal();
        },

        getFormattedPrice: function (price) {
            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },
    });
});
