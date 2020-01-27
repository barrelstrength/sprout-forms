/* global Craft */

if (typeof Craft.SproutForms === typeof undefined) {
  Craft.SproutForms = {};
}

(function($) {
  /**
   * Craft.SproutForms.FieldLayoutEditor class
   * Handles the buttons for creating new groups and fields inside a the drag and drop UI
   */
  Craft.SproutForms.FieldLayoutEditor = Garnish.Base.extend({

    $container: null,
    $groupButton: null,
    $fieldButton: null,
    $settings: null,

    fld: null,
    modal: null,
    formLayout: null,
    fieldsLayout: null,
    continueEditing: null,

    // The Dragula instance
    drake: null,
    // The dragula instance for tabs
    drakeTabs: null,
    tabsLayout: null,
    $saveFormButton: null,

    /**
     * The constructor.
     * @param currentTabs
     * @param continueEditing
     */
    init: function(currentTabs, continueEditing) {
      const that = this;

      this.$saveFormButton = $("#save-form-button");

      this.continueEditing = continueEditing;

      this.initButtons();

      this.modal = Craft.SproutForms.FieldModal.getInstance();

      this.modal.on('newField', $.proxy(function(e) {
        const field = e.field;
        const group = field.group;
        this.addField(field.id, field.name, group.name);
      }, this));

      this.modal.on('saveField', $.proxy(function(e) {
        const field = e.field;
        const group = field.group;
        // Let's update the name and the icon if the field is updated
        this.resetField(field, group);
      }, this));

      // DRAGULA
      this.fieldsLayout = this.getId('right-copy');

      // Drag from right to left
      this.drake = dragula([null, this.fieldsLayout], {
        copy: function(el, source) {
          return source === that.fieldsLayout;
        },
        accepts: function(el, target) {
          return target !== that.fieldsLayout;
        },
        invalid: function(el, handle) {
          // do not move any item with donotdrag class.
          return el.classList.contains('donotdrag');
        }
      })
        .on('drag', function(el) {
          $(el).addClass('drag-active');
        })
        .on('drop', function(el, target, source) {
          $(el).removeClass('drag-active');
          $(target).find('.drag-active').removeClass('drag-active');
          $(source).find('.drag-active').removeClass('drag-active');

          // Reorder fields
          if ($(target).attr("id") === $(source).attr("id")) {
            // just if we need check when the field is reorder
            // not needed because the order is saved from the hidden field
            // when the form is saved
          }
          if (target && source === that.fieldsLayout) {
            // get the tab name by the first div fields
            const tab = $(el).closest(".sproutforms-tab-fields");
            const tabName = tab.data('tabname');
            const tabId = tab.data('tabid');
            const fieldType = $(el).data("type");

            that.createDefaultField(fieldType, tabId, tabName, el);
          }
        })
        .on('over', function(el, container) {
          $(el).addClass('drag-active');
          $(container).addClass('container-active');
        })
        .on('out', function(el, container) {
          $(el).removeClass('drag-active');
          $(container).removeClass('container-active');
        });

      // Adds auto-scroll to main container when dragging
      const scroll = autoScroll(
        [
          document.querySelector('#content-container')
        ],
        {
          margin: 20,
          maxSpeed: 10,
          scrollWhenOutside: true,
          autoScroll: function() {
            //Only scroll when the pointer is down, and there is a child being dragged.
            return this.down && that.drake.dragging;
          }
        }
      );

      // Add the drop containers for each tab
      for (let i = 0; i < currentTabs.length; i++) {
        this.drake.containers.push(this.getId('sproutforms-tab-container-' + currentTabs[i].id));
      }
      // Prevent save with Ctrl+S when the the field is dropped
      /*$(document).bind('keydown', function(e) {
          if(e.ctrlKey && (e.which == 83)) {
              if (!that.$saveFormButton.removeClass('disabled').siblings('.spinner').hasClass("hidden")){
                  e.preventDefault();
                  e.stopPropagation();
                  // Not working
                  return false;
              }
          }
      });*/
    },

    clickHandler: function(e) {
      const target = e.target;
      if (target === this.tabsLayout) {
        return;
      }
      target.innerHTML += ' [click!]';

      setTimeout(function() {
        target.innerHTML = target.innerHTML.replace(/ \[click!]/g, '');
      }, 500);
    },

    createDefaultField: function(type, tabId, tabName, el) {
      $(el).removeClass('source-field');
      $(el).addClass('target-field');
      $(el).find('.source-field-header').remove();
      $(el).find('.body').removeClass('hidden');
      // try to check position of the field
      let nextDiv = $(el).next(".target-field");
      let nextId = nextDiv.attr('id');
      if (typeof nextId === 'undefined' || nextId === null) {
        nextDiv = null;
      } else {
        // Last field
        const nextDivId = nextId.split('-');
        nextId = nextDivId[1];
      }

      const defaultName = $(el).data('defaultname') ? $(el).data('defaultname') : Craft.t('sprout-forms', 'Untitled');

      // Add the Field Header
      $(el).prepend($([
        '<div class="active-field-header">',
        '<h2>', defaultName, '</h2>',
        '</div>'
      ].join('')));

      const formId = $("#formId").val();
      const data = {
        'type': type,
        'formId': formId,
        'tabId': tabId,
        'nextId': nextId
      };

      Craft.postActionRequest('sprout-forms/fields/create-field', data, $.proxy(function(response, textStatus) {
        if (textStatus === 'success') {
          this.initFieldOnDrop(response.field, tabName, el);
          //that.$saveFormButton.removeClass('disabled').siblings('.spinner').addClass('hidden');
        }
      }, this));
    },

    initFieldOnDrop: function(defaultField, tabName, el) {
      if (defaultField !== null && defaultField.hasOwnProperty("id")) {
        $(el).attr('id', 'sproutfield-' + defaultField.id);

        // Add the Settings buttons
        $(el).prepend($([
          '<ul class="settings">',
          '<li><a id="sproutform-field-', defaultField.id, '" data-fieldid="', defaultField.id, '" href="#" tabindex="0" ><i class="fa fa-pencil fa-2x" title="', Craft.t('sprout-forms', 'Edit'), '"></i></a></li>',
          '</ul>'
        ].join('')));

        // Add fieldLayout input
        $(el).append($([
            '<input type="hidden" name="fieldLayout[', tabName, '][]" value="', defaultField.id, '" class="id-input">'
          ].join('')
        ));

        this.addListener($("#sproutform-field-" + defaultField.id), 'activate', 'editField');
      } else {
        Craft.cp.displayError(Craft.t('sprout-forms', 'Something went wrong when creating the field :('));

        $(el).remove();
      }
    },

    getId: function(id) {
      return document.getElementById(id);
    },

    /**
     * Adds edit buttons to existing fields.
     */
    initButtons: function() {
      const that = this;

      // Add listeners to all the items that start with sproutform-field-
      $("a[id^='sproutform-field-']").each(function(i, el) {
        const fieldId = $(el).data('fieldid');

        if (fieldId) {
          that.addListener($("#sproutform-field-" + fieldId), 'activate', 'editField');
        }
      });
    },

    /**
     * Event handler for the New Field button.
     * Creates a modal window that contains new field settings.
     */
    newField: function() {
      this.modal.show();
    },

    editField: function(currentOption) {
      const option = currentOption.currentTarget;

      const fieldId = $(option).data('fieldid');
      // Make our field available to our parent function
      this.$field = $(option);
      this.base($(option));

      this.modal.editField(fieldId);
    },

    /**
     * Renames | update icon | move field to another tab
     * of an existing field after edit it
     *
     * @param field
     * @param group
     */
    resetField: function(field, group) {

      const $fieldDiv = $("#sproutfield-" + field.id);

      // Lets update the the name and icon - (new) update if required
      $($fieldDiv).find('.body').html(field.htmlExample);
      const $requiredDiv = $($fieldDiv).find("[name='requiredFields[]']");

      if (field.required) {
        $($fieldDiv).find('.active-field-header h2').addClass('required');

        // Update or create our hidden required div
        if (!$requiredDiv.length) {
          $('<input type="hidden" name="requiredFields[]" value="' + field.id + '" class="sproutforms-required-input">').appendTo($fieldDiv);
        } else {
          $($requiredDiv).val(field.id);
        }
      } else {
        $($fieldDiv).find('.active-field-header h2').removeClass('required');

        // Update our hidden required div
        $($requiredDiv).val('');
      }

      $($fieldDiv).find('.active-field-header h2').html(field.name);
      $($fieldDiv).find('.active-field-header p').html(field.instructions);

      // Check if we need move the field to another tab
      const tab = $($fieldDiv).closest(".sproutforms-tab-fields");
      const tabName = tab.data('tabname');
      const tabId = tab.data('tabid');

      if (tabName !== group.name) {
        // let's remove the hidden field just if the user change the tab
        $($fieldDiv).find('.id-input').remove();

        // create the new hidden field and add it to the field div
        const $field = $([
          '<input class="id-input" type="hidden" name="fieldLayout[', group.name, '][]" value="', field.id, '">'
        ].join('')).appendTo($($fieldDiv));

        // move the field to another tab
        const newTab = $("#sproutforms-tab-container-" + group.id);

        // move element to new div - like ctrl+x
        $($fieldDiv).detach().appendTo($(newTab));
      }
    }
  });

})(jQuery);
