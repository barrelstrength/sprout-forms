/* global Craft */
/* global Garnish */
/* global $ */

if (typeof Craft.SproutForms === typeof undefined) {
  Craft.SproutForms = {};
}

/**
 * Editable table class
 */
Craft.SproutForms.EditableTable = Garnish.Base.extend(
  {
    initialized: false,

    id: null,
    baseName: null,
    columns: null,
    conditionalTypes: null,
    sorter: null,
    biggestId: -1,

    $table: null,
    $tbody: null,
    $addRowBtn: null,

    init: function(id, baseName, columns, settings, conditionalTypes) {
      this.id = id;
      this.baseName = baseName;
      this.columns = columns;
      this.conditionalTypes = conditionalTypes;
      this.setSettings(settings, Craft.SproutForms.EditableTable.defaults);

      this.$table = $('#' + id);
      this.$tbody = this.$table.children('tbody');

      this.sorter = new Craft.DataTableSorter(this.$table, {
        helperClass: 'editabletablesorthelper',
        copyDraggeeInputValuesToHelper: true
      });

      if (this.isVisible()) {
        this.initialize(this.conditionalTypes);
      } else {
        this.addListener(Garnish.$win, 'resize', 'initializeIfVisible');
      }
    },

    isVisible: function() {
      return (this.$table.height() > 0);
    },

    initialize: function(conditionalTypes) {
      if (this.initialized) {
        return;
      }

      this.initialized = true;
      this.removeListener(Garnish.$win, 'resize');

      var $rows = this.$tbody.children();

      for (var i = 0; i < $rows.length; i++) {
        new Craft.SproutForms.EditableTable.Row(this, $rows[i], conditionalTypes);
      }

      this.$addRowBtn = this.$table.find('.buttons').children('.add');
      this.addListener(this.$addRowBtn, 'activate', 'addRow');

      if ($rows.length == 0) {
        this.addRow();
      }
    },

    initializeIfVisible: function() {
      if (this.isVisible()) {
        this.initialize();
      }
    },

    addRow: function() {
      var rowId = this.settings.rowIdPrefix + (this.biggestId + 1),
        rowHtml = Craft.SproutForms.EditableTable.getRowHtml(rowId, this.columns, this.baseName, {}, this.conditionalTypes),
        $tr = $(rowHtml).appendTo(this.$tbody);

      new Craft.SproutForms.EditableTable.Row(this, $tr, this.conditionalTypes);

      var $container = $tr.find('.sprout-selectother');

      this.sorter.addItems($tr);

      // Focus the first input in the row
      $tr.find('input,textarea,select').first().focus();

      this.settings.onAddRow($tr);
      this.$addRowBtn = $tr.find('#add-rule');
      this.addListener(this.$addRowBtn, 'activate', 'addRow');
    }
  },
  {
    textualColTypes: ['singleline', 'multiline', 'number'],
    defaults: {
      rowIdPrefix: '',
      onAddRow: $.noop,
      onDeleteRow: $.noop
    },

    getRowHtml: function(rowId, columns, baseName, values, conditionalTypes) {
      var rowHtml = '<tr data-id="' + rowId + '">';
      var formFieldName = "";
      var formFieldValue = "";
      var conditionFieldName = "";
      var conditionFieldValue = "";
      for (var colId in columns) {
        var col = columns[colId],
          name = baseName + '[' + rowId + '][' + colId + ']',
          value = (typeof values[colId] !== 'undefined' ? values[colId] : ''),
          textual = Craft.inArray(col.type, Craft.SproutForms.EditableTable.textualColTypes);

        if (colId == 0) {
          formFieldName = name;
          formFieldValue = value != '' ? value : col.options[0].value;
        }

        rowHtml += '<td class="' + (textual ? 'textual' : '') + ' ' + (typeof col['class'] !== 'undefined' ? col['class'] : '') + '"' +
          (typeof col['width'] !== 'undefined' ? ' width="' + col['width'] + '"' : '') +
          '>';

        switch (col.type) {
          case 'select': {
            rowHtml += '<div class="select"><select name="' + name + '">';

            var hasOptgroups = false;

            var firstRow = 'selected';

            for (var key in col.options) {
              var option = col.options[key];

              if (typeof option.optgroup !== 'undefined') {
                if (hasOptgroups) {
                  rowHtml += '</optgroup>';
                } else {
                  hasOptgroups = true;
                }

                rowHtml += '<optgroup label="' + option.optgroup + '">';
              } else {
                var optionLabel = (typeof option.label !== 'undefined' ? option.label : option),
                  optionValue = (typeof option.value !== 'undefined' ? option.value : key),
                  optionDisabled = (typeof option.disabled !== 'undefined' ? option.disabled : false);

                rowHtml += '<option ' + firstRow + ' value="' + optionValue + '"' + (optionValue == value ? ' selected' : '') + (optionDisabled ? ' disabled' : '') + '>' + optionLabel + '</option>';
              }

              firstRow = '';
            }

            if (hasOptgroups) {
              rowHtml += '</optgroup>';
            }

            rowHtml += '</select></div>';

            break;
          }

          case 'selectCondition': {
            conditionFieldName = name;
            var colVal = typeof col.options[0] !== 'undefined' ? col.options[0].value : '';
            conditionFieldValue = value != '' ? value : colVal;

            rowHtml += '<div class="select"><select data-check-value-html="true" name="' + name + '">';
            col.options = conditionalTypes[formFieldValue]['rulesAsOptions'];
            var hasOptgroups = false;

            var firstRow = 'selected';

            for (var key in col.options) {
              var option = col.options[key];

              if (typeof option.optgroup !== 'undefined') {
                if (hasOptgroups) {
                  rowHtml += '</optgroup>';
                } else {
                  hasOptgroups = true;
                }

                rowHtml += '<optgroup label="' + option.optgroup + '">';
              } else {
                var optionLabel = (typeof option.label !== 'undefined' ? option.label : option),
                  optionValue = (typeof option.value !== 'undefined' ? option.value : key),
                  optionDisabled = (typeof option.disabled !== 'undefined' ? option.disabled : false);

                rowHtml += '<option ' + firstRow + ' value="' + optionValue + '"' + (optionValue == value ? ' selected' : '') + (optionDisabled ? ' disabled' : '') + '>' + optionLabel + '</option>';
              }

              firstRow = '';
            }

            if (hasOptgroups) {
              rowHtml += '</optgroup>';
            }

            rowHtml += '</select></div>';

            break;
          }

          case 'checkbox': {
            rowHtml += '<input type="hidden" name="' + name + '">' +
              '<input type="checkbox" name="' + name + '" value="1"' + (value ? ' checked' : '') + '>';

            break;
          }

          default: {
            rowHtml += '<input class="text fullwidth" type="text" name="' + name + '" value="' + value + '">';

          }
        }

        rowHtml += '</td>';
      }

      rowHtml += '<td class="thin action"><div class="buttons"> <div id="add-rule" class="btn add icon small" tabindex="0">OR</div> </div></td>' + '<td class="thin action"><a class="move icon" title="' + Craft.t('sprout-seo', 'Reorder') + '"></a></td>' +
        '<td class="thin action"><a class="delete icon" title="' + Craft.t('sprout-seo', 'Delete') + '"></a></td>' +
        '</tr>';

      return rowHtml;
    }

  });

/**
 * Editable table row class
 */
Craft.SproutForms.EditableTable.Row = Garnish.Base.extend(
  {
    table: null,
    id: null,
    niceTexts: null,

    $tr: null,
    $tds: null,
    $textareas: null,
    $deleteBtn: null,
    conditionalTypes: null,

    init: function(table, tr, conditionalTypes) {
      this.table = table;
      this.$tr = $(tr);
      this.$tds = this.$tr.children();
      this.conditionalTypes = conditionalTypes;

      // Get the row ID, sans prefix
      var id = parseInt(this.$tr.attr('data-id').substr(this.table.settings.rowIdPrefix.length));

      if (id > this.table.biggestId) {
        this.table.biggestId = id;
      }

      this.$textareas = $();
      this.niceTexts = [];
      var textareasByColId = {};
      var that = this;

      var i = 0;

      for (var colId in this.table.columns) {
        var col = this.table.columns[colId];

        if (Craft.inArray(col.type, Craft.SproutForms.EditableTable.textualColTypes)) {
          var $textarea = $('textarea', this.$tds[i]);
          this.$textareas = this.$textareas.add($textarea);

          this.addListener($textarea, 'focus', 'onTextareaFocus');
          this.addListener($textarea, 'mousedown', 'ignoreNextTextareaFocus');

          this.niceTexts.push(new Garnish.NiceText($textarea, {
            onHeightChange: $.proxy(this, 'onTextareaHeightChange')
          }));

          if (col.type === 'singleline' || col.type === 'number') {
            this.addListener($textarea, 'keypress', {type: col.type}, 'validateKeypress');
            this.addListener($textarea, 'textchange', {type: col.type}, 'validateValue');
          }

          textareasByColId[colId] = $textarea;
        }

        i++;
      }

      this.initSproutFields();

      // Now that all of the text cells have been nice-ified, let's normalize the heights
      this.onTextareaHeightChange();

      // Now look for any autopopulate columns
      for (var colId in this.table.columns) {
        var col = this.table.columns[colId];

        if (col.autopopulate && typeof textareasByColId[col.autopopulate] !== 'undefined' && !textareasByColId[colId].val()) {
          new Craft.HandleGenerator(textareasByColId[colId], textareasByColId[col.autopopulate]);
        }
      }

      /* We already generate the depending dropdowns when load */
      var needCheck = this.$tr.find("td:eq(1)").find("select").data("check-value-html");
      var $formFieldInput = this.$tr.find("td:eq(0)").find("select");
      var $conditionalInput = this.$tr.find("td:eq(1)").find("select");

      $formFieldInput.change({row: this}, function(event) {
        var conditionSelectHtml = '';
        conditionSelectHtml += '<div class="select"><select data-check-value-html="true" name="' + name + '">';
        var col = {};
        col['options'] = that.conditionalTypes[$formFieldInput.val()]['rulesAsOptions'];
        var value = $conditionalInput.val();
        var hasOptgroups = false;

        var firstRow = 'selected';

        for (var key in col.options) {
          var option = col.options[key];

          if (typeof option.optgroup !== 'undefined') {
            if (hasOptgroups) {
              conditionSelectHtml += '</optgroup>';
            } else {
              hasOptgroups = true;
            }

            conditionSelectHtml += '<optgroup label="' + option.optgroup + '">';
          } else {
            var optionLabel = (typeof option.label !== 'undefined' ? option.label : option),
              optionValue = (typeof option.value !== 'undefined' ? option.value : key),
              optionDisabled = (typeof option.disabled !== 'undefined' ? option.disabled : false);

            conditionSelectHtml += '<option ' + firstRow + ' value="' + optionValue + '"' + (optionValue == value ? ' selected' : '') + (optionDisabled ? ' disabled' : '') + '>' + optionLabel + '</option>';
          }

          firstRow = '';
        }

        if (hasOptgroups) {
          conditionSelectHtml += '</optgroup>';
        }

        conditionSelectHtml += '</select></div>';
        $conditionalInput.html(conditionSelectHtml);
        that.addValueInputHtml(that);
      });

      $conditionalInput.change({row: this}, function(event) {
        that.addValueInputHtml(that);
      });

      $conditionalInput.change({row: this}, function(event) {
        console.log(event.data.row.$tr.find("td:eq(0)").find("select").val());
      });

      if (needCheck == true) {
        this.addValueInputHtml();
      }

      var $deleteBtn = this.$tr.children().last().find('.delete');
      this.addListener($deleteBtn, 'click', 'deleteRow');
    },

    addValueInputHtml(self = null) {
      var that = (self == null) ? this : self;
      var data = {
        'formFieldHandle': this.$tr.find("td:eq(0)").find("select").val(),
        'condition': this.$tr.find("td:eq(1)").find("select").val(),
        'inputName': this.$tr.find("td:eq(2)").find("input").attr("name"),
        'inputValue': this.$tr.find("td:eq(2)").find("input").val(),
        'formId': $("#formId").val()
      };

      Craft.postActionRequest("sprout-forms/conditionals/get-condition-input-html", data, $.proxy(function(response, textStatus) {
        var statusSuccess = (textStatus === 'success');
        if (statusSuccess && response.success) {
          that.$tr.find("td:eq(2)").html(response.html);
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the input html'));
        }
      }, this));
    },

    initSproutFields: function() {
      Craft.SproutFields.initFields(this.$tr);
    },

    onTextareaFocus: function(ev) {
      this.onTextareaHeightChange();

      var $textarea = $(ev.currentTarget);

      if ($textarea.data('ignoreNextFocus')) {
        $textarea.data('ignoreNextFocus', false);
        return;
      }

      setTimeout(function() {
        var val = $textarea.val();

        // Does the browser support setSelectionRange()?
        if (typeof $textarea[0].setSelectionRange !== 'undefined') {
          // Select the whole value
          var length = val.length * 2;
          $textarea[0].setSelectionRange(0, length);
        } else {
          // Refresh the value to get the cursor positioned at the end
          $textarea.val(val);
        }
      }, 0);
    },

    ignoreNextTextareaFocus: function(ev) {
      $.data(ev.currentTarget, 'ignoreNextFocus', true);
    },

    validateKeypress: function(ev) {
      var keyCode = ev.keyCode ? ev.keyCode : ev.charCode;

      if (!Garnish.isCtrlKeyPressed(ev) && (
        (keyCode === Garnish.RETURN_KEY) ||
        (ev.data.type === 'number' && !Craft.inArray(keyCode, Craft.SproutForms.EditableTable.Row.numericKeyCodes))
      )) {
        ev.preventDefault();
      }
    },

    validateValue: function(ev) {
      var safeValue;

      if (ev.data.type === 'number') {
        // Only grab the number at the beginning of the value (if any)
        var match = ev.currentTarget.value.match(/^\s*(-?[\d\.]*)/);

        if (match !== null) {
          safeValue = match[1];
        } else {
          safeValue = '';
        }
      } else {
        // Just strip any newlines
        safeValue = ev.currentTarget.value.replace(/[\r\n]/g, '');
      }

      if (safeValue !== ev.currentTarget.value) {
        ev.currentTarget.value = safeValue;
      }
    },

    onTextareaHeightChange: function() {
      // Keep all the textareas' heights in sync
      var tallestTextareaHeight = -1;

      for (var i = 0; i < this.niceTexts.length; i++) {
        if (this.niceTexts[i].height > tallestTextareaHeight) {
          tallestTextareaHeight = this.niceTexts[i].height;
        }
      }

      this.$textareas.css('min-height', tallestTextareaHeight);

      // If the <td> is still taller, go with that insted
      var tdHeight = this.$textareas.first().parent().height();

      if (tdHeight > tallestTextareaHeight) {
        this.$textareas.css('min-height', tdHeight);
      }
    },

    deleteRow: function() {
      this.table.sorter.removeItems(this.$tr);
      this.$tr.remove();

      // onDeleteRow callback
      this.table.settings.onDeleteRow(this.$tr);
    }
  },
  {
    numericKeyCodes: [9 /* (tab) */, 8 /* (delete) */, 37, 38, 39, 40 /* (arrows) */, 45, 91 /* (minus) */, 46, 190 /* period */, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57 /* (0-9) */]
  });
