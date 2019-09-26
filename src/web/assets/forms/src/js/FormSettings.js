/* global Craft */
/* global Garnish */

if (typeof Craft.SproutForms === typeof undefined) {
  Craft.SproutForms = {};
}

(function ($) {
  Craft.SproutForms.FormSettings = Garnish.Base.extend({

    options: null,
    modal: null,
    conditionalModal: null,
    $lightswitches: null,

    /**
     * The constructor.
     */
    init: function () {
      // init method
      this.initButtons();
    },

    /**
     * Adds edit buttons to existing integrations.
     */
    initButtons: function () {
      const that = this;

      // Add listeners to all the items that start with sproutform-field-
      $("a[id^='sproutform-integration']").each(function (i, el) {
        const integrationId = $(el).data('integrationid');

        if (integrationId) {
          that.addListener($("#sproutform-integration-" + integrationId), 'activate', 'editIntegration');
        }
      });

      // Add listeners to all conditionals
      $("a[id^='sproutform-conditional']").each(function (i, el) {
        const conditionalId = $(el).data('conditionalid');

        if (conditionalId) {
          that.addListener($("#sproutform-conditional-" + conditionalId), 'activate', 'editConditional');
        }
      });

      this.$lightswitches = $('.sproutforms-integration-row .lightswitch');

      this.addListener(this.$lightswitches, 'click', 'onEnableIntegration');

      this.$conditionalLightswitches = $('.sproutforms-conditional-row .lightswitch');

      this.addListener(this.$conditionalLightswitches, 'click', 'onEnableConditional');

      this.modal = Craft.SproutForms.IntegrationModal.getInstance();

      this.modal.on('saveIntegration', $.proxy(function (e) {
        const integration = e.integration;
        // Let's update the name if the integration is updated
        this.resetIntegration(integration);
      }, this));

      this.conditionalModal = Craft.SproutForms.ConditionalModal.getInstance();

      this.conditionalModal.on('saveConditional', $.proxy(function (e) {
        const conditional = e.conditional;
        // Let's update the name if the conditional is updated
        this.resetConditional(conditional);
      }, this));

      this.addListener($("#integrationsOptions"), 'change', 'createDefaultIntegration');
      this.addListener($("#conditionalOptions"), 'change', 'createDefaultConditional');
    },

    onEnableIntegration: function (ev) {
      const lightswitch = ev.currentTarget;
      const integrationId = lightswitch.id;
      let enabled = $(lightswitch).attr('aria-checked');
      enabled = enabled === 'true' ? 1 : 0;
      const formId = $("#formId").val();

      const data = {integrationId: integrationId, enabled: enabled, formId: formId};

      Craft.postActionRequest('sprout-forms/integrations/enable-integration', data, $.proxy(function (response, textStatus) {
        if (textStatus === 'success' && response.success) {
          Craft.cp.displayNotice(Craft.t('sprout-forms', "Integration updated."));
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to update integration'));
        }
      }, this));

    },

    onEnableConditional: function (ev) {
      const lightswitch = ev.currentTarget;
      const conditionalId = lightswitch.id;
      let enabled = $(lightswitch).attr('aria-checked');
      enabled = enabled === 'true' ? 1 : 0;
      const formId = $("#formId").val();

      const data = {conditionalId: conditionalId, enabled: enabled, formId: formId};

      Craft.postActionRequest('sprout-forms/conditionals/enable-conditional', data, $.proxy(function (response, textStatus) {
        if (textStatus === 'success' && response.success) {
          Craft.cp.displayNotice(Craft.t('sprout-forms', "Conditional updated."));
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to update conditional'));
        }
      }, this));

    },

    /**
     * Renames | update icon |
     * of an existing integration after edit it
     *
     * @param integration
     */
    resetIntegration: function (integration) {
      const $integrationDiv = $("#sproutform-integration-" + integration.id);

      const $container = $("#integration-enabled-" + integration.id);

      const currentValue = integration.enabled === '1' ? true : false;
      const settingsValue = $container.attr('aria-checked') === 'true' ? true : false;
      if (currentValue !== settingsValue) {
        $container.attr('aria-checked', "" + currentValue);
        if (currentValue) {
          $container.addClass("on");
        } else {
          $container.removeClass("on");
        }
      }
      $integrationDiv.html(integration.name);
    },

    /**
     * Renames | update icon |
     * of an existing conditional after edit it
     *
     * @param conditional
     */
    resetConditional: function (conditional) {
      const $conditionalDiv = $("#sproutform-conditional-" + conditional.id);

      const $container = $("#conditional-enabled-" + conditional.id);
      const $behaviorContainer = $("#sproutform-conditional-behavior-"+ conditional.id);
      const currentValue = conditional.enabled === '1' ? true : false;
      const settingsValue = $container.attr('aria-checked') === 'true' ? true : false;
      if (currentValue !== settingsValue) {
        $container.attr('aria-checked', "" + currentValue);
        if (currentValue) {
          $container.addClass("on");
        } else {
          $container.removeClass("on");
        }
      }
      $behaviorContainer.html(conditional.behavior);
      $conditionalDiv.html(conditional.name);
    },

    createDefaultIntegration: function (type) {

      const that = this;
      const integrationTableBody = $('#sproutforms-integrations-table tbody');
      const currentIntegration = $("#integrationsOptions").val();
      const formId = $("#formId").val();

      if (currentIntegration === '') {
        return;
      }

      const data = {
        type: currentIntegration,
        formId: formId,
        sendRule: '*'
      };

      Craft.postActionRequest('sprout-forms/integrations/save-integration', data, $.proxy(function (response, textStatus) {
        if (textStatus === 'success') {
          const integration = response.integration;

          integrationTableBody.append('<tr class="field sproutforms-integration-row" id ="sproutforms-integration-row-' + integration.id + '">' +
            '<td class="heading">' +
            '<a href="#" id ="sproutform-integration-' + integration.id + '" data-integrationid="' + integration.id + '">' + integration.name + '</a>' +
            '</td>' +
            '<td>' +
            '<div class="lightswitch small" tabindex="0" data-value="1" role="checkbox" aria-checked="false" id ="integration-enabled-' + integration.id + '">' +
            '<div class="lightswitch-container">' +
            '<div class="label on"></div>' +
            '<div class="handle"></div>' +
            '<div class="label off"></div>' +
            '</div>' +
            '<input type="hidden" name="" value="">' +
            '</div>' +
            '</td>' +
            '</tr>');

          that.addListener($("#sproutform-integration-" + integration.id), 'activate', 'editIntegration');

          $('#integrationsOptions').val('');
          const $container = $("#integration-enabled-" + integration.id);
          $container.lightswitch();
          that.addListener($container, 'click', 'onEnableIntegration');
        } else {
          // something went wrong
        }
      }, this));

    },

    createDefaultConditional: function (type) {
      const that = this;
      const conditionalTableBody = $("#sproutforms-conditionalrules-table tbody");
      const currentConditional = $("#conditionalOptions").val();
      const formId = $("#formId").val();

      if (currentConditional === '') {
        return;
      }

      const data = {
        type: currentConditional,
        formId: formId
      };

      Craft.postActionRequest('sprout-forms/conditionals/save-conditional', data, $.proxy(function (response, textStatus) {
        if (textStatus === 'success') {
          const conditional = response.conditional;
          conditionalTableBody.append('<tr id ="sproutforms-conditional-row-' + conditional.id + '" class="field sproutforms-conditional-row">' +
            '<td>' +
            '<a href="#" id ="sproutform-conditional-' + conditional.id + '" data-conditionalid="' + conditional.id + '">' + conditional.name + '</a>' +
            '</td>' +
            '<td>' +
            '<div id ="sproutform-conditional-behavior-' + conditional.id + '">' + conditional.behavior + '</div>' +
            '</td>' +
            '<td>' +
            '<div class="lightswitch small" tabindex="0" data-value="1" role="checkbox" aria-checked="false" id ="conditional-enabled-' + conditional.id + '">' +
            '<div class="lightswitch-container">' +
            '<div class="label on"></div>' +
            '<div class="handle"></div>' +
            '<div class="label off"></div>' +
            '</div>' +
            '<input type="hidden" name="" value="">' +
            '</div>' +
            '</td>' +
            '</tr>');

          that.addListener($("#sproutform-conditional-" + conditional.id), 'activate', 'editConditional');

          $('#conditionalOptions').val('');
          const $container = $("#conditional-enabled-" + conditional.id);
          $container.lightswitch();
          that.addListener($container, 'click', 'onEnableConditional');
        } else {
          // something went wrong
        }
      }, this));

    },

    editConditional: function (currentOption) {
      const option = currentOption.currentTarget;
      const conditionalId = $(option).data('conditionalid');
      // Make our field available to our parent function
      //this.$field = $(option);
      this.base($(option));

      this.conditionalModal.editConditional(conditionalId);
    },

    editIntegration: function (currentOption) {
      const option = currentOption.currentTarget;

      const integrationId = $(option).data('integrationid');
      // Make our field available to our parent function
      //this.$field = $(option);
      this.base($(option));

      this.modal.editIntegration(integrationId);
    },

  });

})(jQuery);