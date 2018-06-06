/**
 * Validation.js
 * @author Vicki Tingle 
 */
var What3WordsValidation = Class.create();
What3WordsValidation.prototype = {

    /**
     * Initialise the class
     * @param validateUrl
     */
    initialize: function(validateUrl) {
        this.result = false;
        this.validationFired = false;
        this.validateUrl = validateUrl;
        this.saveType = null;
        var self = this;
    },

    /**
     * Make the AJAX call to the w3w PHP wrapper
     * @param inputValue
     * @param iso
     * @param callback
     */
    ajaxCall: function(inputValue, iso, callback) {
        var self = this;
        self.result = false;
        jQuery.ajax({
            url: self.validateUrl,
            data: {
                what3words: inputValue,
                iso: iso
            },
            success: function (data, status, xhr) {
                // If the input value doesn't match a W3W string, don't validate
                var response = JSON.parse(data);
                if (!inputValue.match(/^[a-z]+\b.[a-z]+\b.[a-z]+$/g)) {
                    self.result = false;
                    return callback(false);
                } else {
                    if (!response.success && !response.country) {
                        jQuery('.typeahead__query').removeClass('valid');
                        jQuery('#advice-validate-what3words-what3words-billing').text(response.message);
                        jQuery('#advice-validate-what3words-what3words-shipping').text(response.message);
                        jQuery('#advice-validate-what3words-what3words-customer-address').text(response.message);
                    } else if ((response.success && response.country) || response.success) {
                        jQuery('#advice-validate-what3words-what3words-billing').text('');
                        jQuery('#advice-validate-what3words-what3words-shipping').text('');
                        jQuery('#advice-validate-what3words-what3words-customer-address').text('');
                    }
                    return callback(response.success);
                }
            }
        });
    },

    /**
     * Hook into Magento core validation
     * Validate by format and validity of words & location
     */
    addValidation: function(iso) {
        var self = this;
    if (jQuery('.validate-what3words').length) {
        self.result = false;
        Validation.addAllThese([
            ['validate-what3words',
                'Please re-enter or edit your 3 word address, and select the correct one when the AutoSuggest list displays.',
                function (inputValue, el) {
                    // Is this shipping, billing or a customer?
                    var dataType = jQuery(el).attr('data-type');
                    if (Validation.get('IsEmpty').test(inputValue)) {
                        self.result = false;
                        return true;
                    }

                    // Check if we should validate
                    if (self.validationFired === false) {
                        self.validationFired = true;
                        self.ajaxCall(inputValue, iso, function(returnValue) {
                            // Submit the different forms depending on where we are
                            self.result = returnValue;
                            if (dataType == 'billing') {
                                billing.save();
                            } else if (dataType == 'shipping') {
                                shipping.save();
                            } else if (dataType == 'customer') {
                                jQuery('#form-validate').submit();
                            }
                            self.validationFired = false;
                        });
                    }
                    return self.result;
                }]
            ])
        }
    }
};
