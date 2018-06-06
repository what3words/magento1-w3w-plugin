/**
 * What3WordsInfo
 * @author Vicki Tingle
 */
var What3WordsInfo = Class.create();
What3WordsInfo.prototype = {

    /**
     * Initialise class
     * @param what3words
     */
    initialize: function(what3words) {
        this.what3words = what3words;
    },

    /**
     * Append address to order info block
     */
    updateOrderInfo: function() {
        if (this.what3words !== '') {
            jQuery('address').append('<br/>/// ' + this.what3words);
        }
    }
};
