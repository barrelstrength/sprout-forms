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

            var $container = $("#integration-enabled-" + integration.id);
            var currentValue = integration.enabled == 1 ? true : false;
            var settingsValue = $container.attr('aria-checked');
            if (currentValue != settingsValue) {
                $container.attr('aria-checked', "" + currentValue);
                if (currentValue) {
                    $container.addClass("on");
                } else {
                    $container.removeClass("on");
                }
            }
            $integrationDiv.html(integration.name);
        },

        createDefaultIntegration: function(type) {

            var that = this;
            var integrationCreate = $("#sproutforms-integrations-create");
            var currentIntegration = $("#integrationsOptions").val();
            var formId = $("#formId").val();

            if (currentIntegration === '') {
                return;
            }

            var data = {type: currentIntegration, formId: formId};

            Craft.postActionRequest('sprout-forms/integrations/save-integration', data, $.proxy(function(response, textStatus) {
                if (textStatus === 'success') {
                    var integration = response.integration;

                    integrationCreate.before('<div class="field sproutforms-integration-row" id ="sproutforms-integration-row-' + integration.id + '">' +
                        '<div class="heading">' +
                        '<a href="#" id ="sproutform-integration-' + integration.id + '" data-integrationid="' + integration.id + '">' + integration.name + '</a>' +
                        '</div>' +
                        '<div>' +
                        '<div class="lightswitch small" tabindex="0" data-value="1" role="checkbox" aria-checked="false" id ="integration-enabled-' + integration.id + '">' +
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
                    var $container = $("#integration-enabled-" + integration.id);
                    $container.lightswitch();
                    that.addListener($container, 'click', 'onChange');
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