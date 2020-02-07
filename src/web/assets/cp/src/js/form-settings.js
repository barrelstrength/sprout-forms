/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

/* global Craft */
/* global Garnish */

if (typeof Craft.SproutForms === typeof undefined) {
  Craft.SproutForms = {};
}

(function($) {
  Craft.SproutForms.FormSettings = Garnish.Base.extend({

    options: null,
    modal: null,
    ruleModal: null,
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
      const that = this;

      // Add listeners to all the items that start with sproutform-field-
      $("a[id^='sproutform-integration']").each(function(i, el) {
        const integrationId = $(el).data('integrationid');

        if (integrationId) {
          that.addListener($("#sproutform-integration-" + integrationId), 'activate', 'editIntegration');
        }
      });

      // Add listeners to all Ms
      $("a[id^='sproutform-rules']").each(function(i, el) {
        const ruleId = $(el).data('ruleId');

        if (ruleId) {
          that.addListener($("#sproutform-rules-" + ruleId), 'activate', 'editRule');
        }
      });

      this.$lightswitches = $('.sproutforms-integration-row .lightswitch');

      this.addListener(this.$lightswitches, 'click', 'onEnableIntegration');

      this.$ruleEnabledLightswitches = $('.sproutforms-rules-row .lightswitch');

      this.addListener(this.$ruleEnabledLightswitches, 'click', 'onEnableRule');

      this.modal = Craft.SproutForms.IntegrationModal.getInstance();

      this.modal.on('saveIntegration', $.proxy(function(e) {
        const integration = e.integration;
        // Let's update the name if the integration is updated
        this.resetIntegration(integration);
      }, this));

      this.ruleModal = Craft.SproutForms.RuleModal.getInstance();

      this.ruleModal.on('saveRule', $.proxy(function(e) {
        const rule = e.rule;
        // Let's update the name if the conditional is updated
        this.resetConditional(rule);
      }, this));

      this.addListener($("#integrationsOptions"), 'change', 'createDefaultIntegration');
      this.addListener($("#ruleOptions"), 'change', 'createDefaultRule');
    },

    onEnableIntegration: function(ev) {
      const lightswitch = ev.currentTarget;
      const integrationId = lightswitch.id;
      let enabled = $(lightswitch).attr('aria-checked');
      enabled = enabled === 'true' ? 1 : 0;
      const formId = $("#formId").val();

      const data = {integrationId: integrationId, enabled: enabled, formId: formId};

      Craft.postActionRequest('sprout-forms/integrations/enable-integration', data, $.proxy(function(response, textStatus) {
        if (textStatus === 'success' && response.success) {
          Craft.cp.displayNotice(Craft.t('sprout-forms', 'Integration updated.'));
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to update integration'));
        }
      }, this));

    },

    onEnableRule: function(ev) {
      const lightswitch = ev.currentTarget;
      const ruleId = lightswitch.id;
      let enabled = $(lightswitch).attr('aria-checked');
      enabled = enabled === 'true' ? 1 : 0;
      const formId = $("#formId").val();

      const data = {ruleId: ruleId, enabled: enabled, formId: formId};

      Craft.postActionRequest('sprout-forms/rules/enable-rule', data, $.proxy(function(response, textStatus) {
        if (textStatus === 'success' && response.success) {
          Craft.cp.displayNotice(Craft.t('sprout-forms', 'Conditional updated.'));
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
    resetIntegration: function(integration) {
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
    resetConditional: function(conditional) {
      const $conditionalDiv = $("#sproutform-rules-" + conditional.id);

      const $container = $("#condition-enabled-" + conditional.id);
      const $behaviorContainer = $("#sproutform-rules-behavior-" + conditional.id);
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

    createDefaultIntegration: function(type) {

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

      Craft.postActionRequest('sprout-forms/integrations/save-integration', data, $.proxy(function(response, textStatus) {
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

    createDefaultRule: function(type) {
      const that = this;
      const ruleTableBody = $("#sproutforms-rules-table tbody");
      const currentRule = $("#ruleOptions").val();
      const formId = $("#formId").val();

      if (currentRule === '') {
        return;
      }

      const data = {
        type: currentRule,
        formId: formId
      };

      Craft.postActionRequest('sprout-forms/rules/save-rule', data, $.proxy(function(response, textStatus) {
        if (textStatus === 'success') {
          const rule = response.rule;
          ruleTableBody.append('<tr id ="sproutforms-rules-row-' + rule.id + '" class="field sproutforms-rules-row">' +
            '<td>' +
            '<a href="#" id ="sproutform-rules-' + rule.id + '" data-rule-id="' + rule.id + '">' + rule.name + '</a>' +
            '</td>' +
            '<td>' +
            '<div id ="sproutform-rules-behavior-' + rule.id + '">' + rule.behavior + '</div>' +
            '</td>' +
            '<td>' +
            '<div class="lightswitch small" tabindex="0" data-value="1" role="checkbox" aria-checked="false" id ="condition-enabled-' + rule.id + '">' +
            '<div class="lightswitch-container">' +
            '<div class="label on"></div>' +
            '<div class="handle"></div>' +
            '<div class="label off"></div>' +
            '</div>' +
            '<input type="hidden" name="" value="">' +
            '</div>' +
            '</td>' +
            '</tr>');

          that.addListener($("#sproutform-rules-" + rule.id), 'activate', 'editRule');

          $('#ruleOptions').val('');
          const $container = $("#condition-enabled-" + rule.id);
          $container.lightswitch();
          that.addListener($container, 'click', 'onEnableRule');
        } else {
          // something went wrong
        }
      }, this));

    },

    editRule: function(currentOption) {

      const option = currentOption.currentTarget;
      const ruleId = $(option).data('ruleId');
      // Make our field available to our parent function
      //this.$field = $(option);
      this.base($(option));

      this.ruleModal.editRule(ruleId);
    },

    editIntegration: function(currentOption) {
      const option = currentOption.currentTarget;

      const integrationId = $(option).data('integrationid');
      // Make our field available to our parent function
      //this.$field = $(option);
      this.base($(option));

      this.modal.editIntegration(integrationId);
    },

  });

})(jQuery);