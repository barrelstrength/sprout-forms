/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

if (typeof Craft.SproutForms === typeof undefined) {
    Craft.SproutForms = {};
}

(function($) {

    Craft.SproutForms.ElementIntegration = Garnish.Base.extend({

        options: null,
        modal: null,

        /**
         * The constructor.
         */
        init: function() {
            // init method
            console.log("HLOA");
            this.initDropdowns();
        },

        /**
         * Adds edit buttons to existing integrations.
         */
        initDropdowns: function() {
            var that = this;

            // Select the current
            $(".btn")[0].click(function(){
                console.log("as");
            });
        },

    });

})(jQuery);