if (typeof Craft.SproutForms === typeof undefined) {
    Craft.SproutForms = {};
}

(function($) {

    Craft.SproutForms.Integration = Garnish.Base.extend({

        updateTargetFieldsAction: null,
        updateSourceFieldsAction: null,
        integrationType: null,

        init: function(settings) {
            var that = this;
            this.integrationType = typeof settings.integrationType !== 'undefined'
                ? settings.integrationType
                : '';

            this.updateTargetFieldsAction = typeof settings.updateTargetFieldsAction !== 'undefined'
                ? settings.updateTargetFieldsAction
                : null;

            this.updateSourceFieldsAction = typeof settings.updateSourceFieldsAction !== 'undefined'
                ? settings.updateSourceFieldsAction
                : null;
            // Make the sourceFormField read only
            this.disableOptions();

            // Init all empty field selects
            this.updateAllFieldSelects();

            // When the entry type is changed
            $('#settings-barrelstrength-sproutforms-integrationtypes-EntryElementIntegration-entryTypeId').change(function() {
                var changed = $(this).val() != $(this).data('default');
                if (changed){
                    that.updateAllFieldSelects();
                }
            });
        },

        disableOptions: function() {
            if (this.updateSourceFieldsAction === null) {
                $('.formField').each(function(index) {
                    $(this).find('textarea').attr("readonly", true);
                });

                return null;
            }

            var data = this.getEntryFieldsData();
            var that = this;
            Craft.postActionRequest(this.updateSourceFieldsAction, data, $.proxy(function(response, textStatus) {
                var statusSuccess = (textStatus === 'success');
                if (statusSuccess && response.success) {
                    var rows = response.formFields;
                    $('tbody .formField').each(function(index) {
                        var td = $(this);
                        td.empty();
                        var title = rows[index]["label"];
                        var handle = rows[index]["value"];
                        td.append('<div style="display:none;"><select readonly name="settings['+that.integrationType+'][fieldMapping]['+index+'][sourceFormField]"><option selected value="'+handle+'">'+title+'</option></select></div><div style="padding: 7px 10px;font-size: 12px;color:#8f98a3;">'+title+' <span class="code">('+handle+')</span></div>');
                    });
                } else {
                    Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the Form fields'));
                }
            }, this));
        },

        updateAllFieldSelects: function() {
            var integrationIdBase = this.integrationType.replace(/\\/g, '-');
            var mappingTableRows = 'table#settings-'+integrationIdBase+'-fieldMapping tr';
            $(mappingTableRows).find('td:eq(2),th:eq(2)').remove();
            $(mappingTableRows).find('td:eq(0),th:eq(0)').css('width', '50%');
            $(mappingTableRows).find('td:eq(1),th:eq(1)').css('width', '50%');

            if (this.updateTargetFieldsAction === null){
                return false;
            }

            var $currentRows = this.getCurrentRows('tbody .targetFields');
            var data = this.getEntryFieldsData();
            var that = this;

            Craft.postActionRequest(this.updateTargetFieldsAction, data, $.proxy(function(response, textStatus) {
                var statusSuccess = (textStatus === 'success');
                if (statusSuccess && response.success) {
                    var rows = response.fieldOptionsByRow;

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

        getEntryFieldsData: function() {
            var entryTypeId = $('#settings-barrelstrength-sproutforms-integrationtypes-EntryElementIntegration-entryTypeId').val();
            var integrationId = $('#integrationId').val();
            var data = {
                'entryTypeId': entryTypeId,
                'integrationId': integrationId
            };

            return data;
        },

        appendFieldsToSelect: function($select, fields) {
            $select.empty();

            var dropdown = '';
            var closeOptgroup = false;
            $select.append('<option value="">'+Craft.t('sprout-forms', 'None')+'</option>');

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