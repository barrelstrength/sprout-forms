if (typeof Craft.SproutForms === typeof undefined) {
    Craft.SproutForms = {};
}

(function($) {

    Craft.SproutForms.ElementIntegration = Garnish.Base.extend({

        updateTargetFieldsAction: null,

        init: function(settings) {
            var that = this;
            this.updateTargetFieldsAction = settings.updateTargetFieldsAction !== null
                ? settings.updateTargetFieldsAction
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
            $('.formField').each(function() {
                $(this).find('textarea').attr("readonly", true);
            });
        },

        updateAllFieldSelects: function() {
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
            $select.append('<option value="">-Select Field-</option>');

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

    }); // End Craft.SproutForms.ElementIntegration

})(jQuery);