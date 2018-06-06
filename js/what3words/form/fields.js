/**
 * Fields.js
 * @author Vicki Tingle 
 */
var What3WordsField = Class.create();
What3WordsField.prototype = {

    /**
     * Field initializer
     * @param apiKey
     * @param addressIds
     * @param allowedCountries
     * @param isArabic
     * @param placeholderText
     * @param validateUrl
     * @param validateCountryUrl
     * @param delay
     */
    initialize: function (apiKey, addressIds, allowedCountries,
                          isArabic, placeholderText, validateUrl,
                          validateCountryUrl, delay
    ) {
        this.apiKey = apiKey;
        this.billingCountryElem = jQuery('#billing-new-address-form').find('[name="billing[country_id]"]');
        this.shippingCountryElem = jQuery('#co-shipping-form').find('[name="shipping[country_id]"]');
        this.customerCountryElem = jQuery('#country');
        this.isArabic = isArabic;
        this.delay = delay;
        this.resultCount = 3;
        this.validateUrl = validateUrl;
        if (placeholderText == '') {
            this.placeholderText = 'e.g. lock.spout.radar';
        } else {
            this.placeholderText = placeholderText;
        }

        // Instantiate the validation class
        this.validation = new What3WordsValidation(validateUrl, validateCountryUrl);

        var self = this;

        // Make sure the user has jQuery
        if (typeof jQuery != 'undefined') {
            jQuery(document).ready(function () {
                self.checkCountry(allowedCountries);
                // self.initAdminhtmlField();
                self.appendToOption(addressIds);
            });
        }
    },

    /**
     * Generate the input field for the billing form
     * @param iso
     */
    initBillingField: function(iso) {
        this.removeBillingField();
        var self = this,
            billingForm = jQuery('#billing-new-address-form'),
            billingHtml = $('what3words-billing-field').innerHTML,
            direction = 'ltr';
        billingForm.append(billingHtml);
        this.validation.addValidation(iso);

        // If the user is in an Arabic locale, reverse the direction
        if (this.isArabic) {
            direction = 'rtl';
        }

        // Initialise W3W auto-suggest on the input with a country filter
        // based on what's in the address selector
        jQuery('#what3words-billing').w3wAddress({
            key: self.apiKey,
            direction: direction,
            multilingual: true,
            placeholder: self.placeholderText,
            results: self.resultCount,
            typeaheadDelay: self.typeaheadDelay,
            country_filter: iso,
            valid_error: ''
        });

        // If the local is Arabic, reverse the field
        if (this.isArabic) {
            jQuery('typeahead__container').addClass('is-arabic');
        }
    },

    /**
     * Generate input for customer account form
     * @param iso
     */
    initCustomerField: function(iso) {
        this.removeCustomerField();
        var self = this;
        var fieldToFollow = jQuery('.scaffold-form').find("[id='country']"),
            parent = fieldToFollow.parents('.fields'),
            direction = 'ltr';
        var customerFieldHtml = $('what3words-customer-field').innerHTML;
        parent.append(customerFieldHtml);
        this.validation.addValidation(iso);

        // If the user is in an Arabic locale, reverse the direction
        if (this.isArabic) {
            direction = 'rtl';
        }

        // Initialise W3W auto-suggest on the input with a country filter
        // based on what's in the address selector
        jQuery('#what3words-customer-address').w3wAddress({
            key: self.apiKey,
            direction: direction,
            placeholder: self.placeholderText,
            results: self.resultCount,
            typeaheadDelay: self.typeaheadDelay,
            valid_error: '',
            country_filter: iso
        });
    },

    /**
     * Generate the input field for the shipping form
     * @param iso
     */
    initShippingField: function(iso) {
        this.removeShippingField();
        var self = this,
            shippingForm = jQuery('#shipping-new-address-form'),
            shippingHtml = $('what3words-shipping-field').innerHTML,
            direction = 'ltr';
        shippingForm.append(shippingHtml);
        this.validation.addValidation(iso);

        // If the user is in an Arabic locale, reverse the direction
        if (this.isArabic) {
            direction = 'rtl';
        }

        // Initialise W3W auto-suggest on the input
        jQuery('#what3words-shipping').w3wAddress({
            key: self.apiKey,
            direction: direction,
            multilingual: true,
            placeholder: self.placeholderText,
            results: self.resultCount,
            typeaheadDelay: self.typeaheadDelay,
            valid_error: '',
            country_filter: iso
        });

        if (this.isArabic) {
            jQuery('typeahead__container').addClass('is-arabic');
        }
    },

    /**
     * If the input field should be removed, remove it.
     */
    removeShippingField: function() {
        if (jQuery('#what3words-shipping').length) {
            jQuery('.what3words-shipping-field').remove();
        }
    },

    /**
     * If the input field should be removed, remove it.
     */
    removeBillingField: function() {
        if (jQuery('#what3words-billing').length) {
            jQuery('.what3words-billing-field').remove();
        }
    },

    /**
     * If the input field should be removed, remove it.
     */
    removeCustomerField: function() {
        if (jQuery('#what3words-customer-address').length) {
            jQuery('.what3words-customer-field').remove();
        }
    },

    /**
     * Add any saved W3W into address option in select
     * @param ids
     */
     appendToOption: function(ids) {
        var addressIds = JSON.parse(ids),
            currentId = 00000;
        // Get the select elements
        var billingSelect = jQuery('#billing-address-select'),
            shippingSelect = jQuery('#shipping-address-select');
        for (var i = 0; i < addressIds.length; i++) {
            var addressId = addressIds[i]['address_id'];
            // Check so that we don't append multiples
            if (addressId !== currentId) {
                // Find the correct ones
                var billingOptionElement = billingSelect.find('[value="' + addressIds[i]['address_id'] + '"]'),
                    shippingOptionElement = shippingSelect.find('[value="' + addressIds[i]['address_id'] + '"]');
                // Add in W3W
                billingOptionElement.append(', ' + addressIds[i]['w3w']);
                shippingOptionElement.append(', ' + addressIds[i]['w3w']);
            }

            currentId = addressId;
        }
    },

    /**
     * Check if the selected country can use a W3W value
     * @param allowedCountries
     */
   checkCountry: function(allowedCountries) {
        var self = this;
       //Parse our countries JSON string
       var countryArray = JSON.parse(allowedCountries),
           // self = this,
           defaultBillingCountry = self.billingCountryElem.val();
       if (self.billingCountryElem.length) {
           // Do an initial check when the page loads
           if (countryArray.indexOf(defaultBillingCountry) !== -1) {
               this.initBillingField(defaultBillingCountry);
           }
           // When the value of the select is changed, check if it's in the allowed countries
           self.billingCountryElem.on('change', function() {
               var selectedCountry = this.value;
               if (countryArray.indexOf(selectedCountry) == -1) {
                   self.removeBillingField();
               } else {
                   self.initBillingField(selectedCountry);
               }
           });
       }

       //Do the same as above, for the shipping form
        if (self.shippingCountryElem.length) {
           var defaultShippingCountry = self.shippingCountryElem.val();
            if (countryArray.indexOf(defaultShippingCountry) !== -1) {
                this.initShippingField(defaultShippingCountry);
            }
            self.shippingCountryElem.on('change', function() {
                var selectedCountry = this.value;
                if (countryArray.indexOf(selectedCountry) == -1) {
                    self.removeShippingField();
                } else {
                    self.initShippingField(selectedCountry);
                }
            });
        }
        //Do the same as above, for the customer form
        if (self.customerCountryElem.length) {
           var defaultCustomerCountry = self.customerCountryElem.val();
            if (countryArray.indexOf(defaultCustomerCountry) !== -1) {
                this.initCustomerField(defaultCustomerCountry);
            }
            self.customerCountryElem.on('change', function() {
                var selectedCountry = this.value;
                if (countryArray.indexOf(selectedCountry) == -1) {
                    self.removeCustomerField();
                } else {
                    self.initCustomerField(selectedCountry);
                }
            });
        }
   }
};
