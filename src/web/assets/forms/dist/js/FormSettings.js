/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

if (typeof Craft.SproutForms === typeof undefined) {
    Craft.SproutForms = {};
}

(function($) {

    Craft.SproutForms.FormSettings = Garnish.Base.extend({

        options: null,
        modal: null,

        /**
         * The constructor.
         */
        init: function() {
            // init method
            this.initButtons();
        },

        /**
         * Adds edit buttons to existing integrations.
         */
        initButtons: function() {
            var that = this;

            // Add listeners to all the items that start with sproutform-field-
            $("a[id^='sproutform-integration']").each(function(i, el) {
                var integrationId = $(el).data('integrationid');

                if (integrationId) {
                    that.addListener($("#sproutform-integration-" + integrationId), 'activate', 'editIntegration');
                }
            });

            this.modal = Craft.SproutForms.IntegrationModal.getInstance();

            this.modal.on('saveIntegration', $.proxy(function(e) {
                var integration = e.integration;
                // Let's update the name if the integration is updated
                this.resetIntegration(integration);
            }, this));

            this.addListener($("#add-integration"), 'activate', 'createDefaultIntegration');
        },

        /**
         * Renames | update icon |
         * of an existing integration after edit it
         *
         * @param integration
         */
        resetIntegration: function(integration) {
            var $integrationDiv = $("#sproutform-integration-" + integration.id);
            $integrationDiv.html(integration.name);
        },

        createDefaultIntegration: function(type) {
            var that = this;
            var integrationsWrapper = $("#integrations-wrapper");
            var currentIntegration = $("#integrationsOptions").val();
            var formId = $("#formId").val();

            var data = {type: currentIntegration, formId: formId};

            Craft.postActionRequest('sprout-forms/integrations/create-integration', data, $.proxy(function(response, textStatus) {
                if (textStatus === 'success') {
                    var integration = response.integration;
                    // Add integration edit link
                    integrationsWrapper.prepend($([
                        '<div class="active-field-header">',
                        '<a href="#" class="btn small integrations-btn" id ="sproutform-integration-' + integration.id + '" data-integrationid="' + integration.id + '">' + integration.name + '</a>',
                        '</div>'
                    ].join('')));

                    that.addListener($("#sproutform-integration-" + integration.id), 'activate', 'editIntegration');

                } else {
                    // something went wrong
                }
            }, this));

        },

        editIntegration: function(option) {
            var option = option.currentTarget;

            var integrationId = $(option).data('integrationid');
            // Make our field available to our parent function
            //this.$field = $(option);
            this.base($(option));

            this.modal.editIntegration(integrationId);
        },

    });

})(jQuery);