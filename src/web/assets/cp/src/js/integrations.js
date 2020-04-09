/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

/* global Craft */

class SproutFormsIntegration {

  constructor(settings) {
    const self = this;

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
        self.updateAllFieldSelects();
      });
    });
  }

  disableOptions() {
    const self = this;
    const integrationId = $('#integrationId').val();

    const data = {
      'integrationId': integrationId
    };

    Craft.postActionRequest('sprout-forms/integrations/get-source-form-fields', data, $.proxy(function(response, textStatus) {
      const statusSuccess = (textStatus === 'success');
      if (statusSuccess && response.success) {
        const rows = response.sourceFormFields;
        $('tbody .formField').each(function(index) {
          const td = $(this);
          td.empty();
          const title = rows[index]["label"];
          const handle = rows[index]["value"];
          td.append('<div style="display:none;"><select readonly name="settings[' + self.integrationType + '][fieldMapping][' + index + '][sourceFormField]"><option selected value="' + handle + '">' + title + '</option></select></div><div style="padding: 7px 10px;font-size: 12px;color:#8f98a3;">' + title + ' <span class="code">(' + handle + ')</span></div>');
        });
      } else {
        Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the Form fields'));
      }
    }, this));

    return null;
  }

  updateAllFieldSelects() {
    const integrationIdBase = this.integrationType.replace(/\\/g, '-');
    const mappingTableRows = 'table#settings-' + integrationIdBase + '-fieldMapping tr';

    $(mappingTableRows).find('td:eq(2),th:eq(2)').remove();
    $(mappingTableRows).find('td:eq(0),th:eq(0)').css('width', '50%');
    $(mappingTableRows).find('td:eq(1),th:eq(1)').css('width', '50%');

    const $currentRows = this.getCurrentRows('tbody .targetFields');

    // Hand off all our current Form data so the Integration can use it if needed
    const data = $("#integrationId").closest('form').serialize();

    const self = this;

    Craft.postActionRequest('sprout-forms/integrations/get-target-integration-fields', data, $.proxy(function(response, textStatus) {
      const statusSuccess = (textStatus === 'success');

      if (statusSuccess && response.success) {
        const rows = response.targetIntegrationFields;

        if (rows.length === 0) {
          return false;
        }

        $currentRows.each(function(index) {
          const $select = $(this).find('select');
          const fields = rows[index];

          self.appendFieldsToSelect($select, fields);
        });
      } else {
        Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the Form fields'));
      }
    }, this));
  }

  getCurrentRows(className = null) {
    if (className === null) {
      className = 'tbody .formField';
    }
    return $(className);
  }

  appendFieldsToSelect($select, fields) {
    $select.empty();

    let dropdown = '';
    let closeOptgroup = false;
    $select.append('<option value="">' + Craft.t('sprout-forms', 'None') + '</option>');

    for (let i = 0; i < fields.length; i++) {
      const field = fields[i];
      let selectedCode = '';
      const lastItem = i === (fields.length - 1);

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
}

window.SproutFormsIntegration = SproutFormsIntegration;