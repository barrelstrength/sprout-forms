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
        $lightswitches: null,

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

            this.$lightswitches = $('.sproutforms-integration-row .lightswitch');

            this.addListener(this.$lightswitches, 'click', 'onChange');

            this.modal = Craft.SproutForms.IntegrationModal.getInstance();

            this.modal.on('saveIntegration', $.proxy(function(e) {
                var integration = e.integration;
                // Let's update the name if the integration is updated
                this.resetIntegration(integration);
            }, this));

            this.addListener($("#integrationsOptions"), 'change', 'createDefaultIntegration');
        },

        onChange: function(ev) {
            var lightswitch = ev.currentTarget;
            var integrationId = lightswitch.id;
            var enabled = $(lightswitch).attr('aria-checked');
            enabled = enabled == 'true' ? 1 : 0;
            var formId = $("#formId").val();

            var data = {integrationId: integrationId, enabled: enabled, formId: formId};

            Craft.postActionRequest('sprout-forms/integrations/enable-integration', data, $.proxy(function(response, textStatus) {
                if (textStatus === 'success' && response.success) {
                    Craft.cp.displayNotice(Craft.t('sprout-forms', "Integration updated."));
                } else {
                    Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to update integration'));
                }
            }, this));

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
            var integrationRows = $(".sproutforms-integration-row");
            var currentIntegration = $("#integrationsOptions").val();
            var formId = $("#formId").val();

            if (currentIntegration === '') {
                return;
            }

            var data = {type: currentIntegration, formId: formId};

            Craft.postActionRequest('sprout-forms/integrations/create-integration', data, $.proxy(function(response, textStatus) {
                if (textStatus === 'success') {
                    var integration = response.integration;

                    integrationRows.last().after('<div class="field sproutforms-integration-row">' +
                        '<div class="heading">' +
                        '<a href="#" id ="sproutform-integration-' + integration.id + '" data-integrationid="' + integration.id + '">' + integration.name + '</a>' +
                        '</div>' +
                        '<div>' +
                        '<div class="lightswitch small" tabindex="0" data-value="1" role="checkbox" aria-checked="false">' +
                        '<div class="lightswitch-container">' +
                        '<div class="label on"></div>' +
                        '<div class="handle"></div>' +
                        '<div class="label off"></div>' +
                        '</div>' +
                        '<input type="hidden" name="" value="">' +
                        '</div>' +
                        '</div>' +
                        '</div>');

                    that.addListener($("#sproutform-integration-" + integration.id), 'activate', 'editIntegration');

                    $('#integrationsOptions').val('');

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