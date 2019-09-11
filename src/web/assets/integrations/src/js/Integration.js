/* global Craft */

if (typeof Craft.SproutForms === typeof undefined) {
  Craft.SproutForms = {};
}

(function($) {

  Craft.SproutForms.Integration = Garnish.Base.extend({

    integrationType: null,
    updateTargetFieldsOnChange: [],

    init: function(settings) {
      var that = this;

      this.integrationType = typeof settings.integrationType !== 'undefined'
        ? settings.integrationType
        : '';

      // Make the sourceFormField read only
      this.disableOptions();

      // Init all empty field selects
      this.updateAllFieldSelects();

      this.updateTargetFieldsOnChange = typeof settings.updateTargetFieldsOnChange !== 'undefined'
        ? settings.updateTargetFieldsOnChange
        : [];

      this.updateTargetFieldsOnChange.forEach(function(elementId) {
        // Register an onChange event for all Element IDs identified by the Integration
        $(elementId).change(function() {
          that.updateAllFieldSelects();
        });
      });
    },

    disableOptions: function() {

      var that = this;

      var integrationId = $('#integrationId').val();

      data = {
        'integrationId': integrationId
      };

      Craft.postActionRequest('sprout-forms/integrations/get-source-form-fields', data, $.proxy(function(response, textStatus) {
        var statusSuccess = (textStatus === 'success');
        if (statusSuccess && response.success) {
          var rows = response.sourceFormFields;
          $('tbody .formField').each(function(index) {
            var td = $(this);
            td.empty();
            var title = rows[index]["label"];
            var handle = rows[index]["value"];
            td.append('<div style="display:none;"><select readonly name="settings[' + that.integrationType + '][fieldMapping][' + index + '][sourceFormField]"><option selected value="' + handle + '">' + title + '</option></select></div><div style="padding: 7px 10px;font-size: 12px;color:#8f98a3;">' + title + ' <span class="code">(' + handle + ')</span></div>');
          });
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the Form fields'));
        }
      }, this));

      return null;
    },

    updateAllFieldSelects: function() {
      var integrationIdBase = this.integrationType.replace(/\\/g, '-');
      var mappingTableRows = 'table#settings-' + integrationIdBase + '-fieldMapping tr';
      $(mappingTableRows).find('td:eq(2),th:eq(2)').remove();
      $(mappingTableRows).find('td:eq(0),th:eq(0)').css('width', '50%');
      $(mappingTableRows).find('td:eq(1),th:eq(1)').css('width', '50%');

      var $currentRows = this.getCurrentRows('tbody .targetFields');

      // Hand off all our current Form data so the Integration can use it if needed
      var data = $("#integrationId").closest('form').serialize();

      var that = this;

      Craft.postActionRequest('sprout-forms/integrations/get-target-integration-fields', data, $.proxy(function(response, textStatus) {
        var statusSuccess = (textStatus === 'success');

        if (statusSuccess && response.success) {
          var rows = response.targetIntegrationFields;

          if (rows.length === 0) {
            return false;
          }

          $currentRows.each(function(index) {
            var $select = $(this).find('select');
            var fields = rows[index];

            that.appendFieldsToSelect($select, fields);
          });
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the Form fields'));
        }
      }, this));
    },

    getCurrentRows: function(className = null) {
      if (className === null) {
        className = 'tbody .formField';
      }
      return $(className);
    },

    appendFieldsToSelect: function($select, fields) {
      $select.empty();

      var dropdown = '';
      var closeOptgroup = false;
      $select.append('<option value="">' + Craft.t('sprout-forms', 'None') + '</option>');

      for (i = 0; i < fields.length; i++) {
        var field = fields[i];
        var selectedCode = '';
        var lastItem = i === (fields.length - 1);

        if (!("optgroup" in field)) {
          if ("selected" in field) {
            selectedCode = 'selected';
          }
          dropdown += '<option ' + selectedCode + ' value="' + field['value'] + '">' + field['label'] + '</option>';
        }

        if (("optgroup" in field && closeOptgroup) || (lastItem && closeOptgroup)) {
          dropdown += '</optgroup>';
          closeOptgroup = false;
        }

        if ("optgroup" in field) {
          dropdown += '<optgroup label="' + field['optgroup'] + '">';
          closeOptgroup = true;
        }
      }

      $select.append(dropdown);
    }

  }); // End Craft.SproutForms.Integration

})(jQuery);