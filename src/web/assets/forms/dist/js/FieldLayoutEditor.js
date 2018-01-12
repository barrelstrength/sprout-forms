/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

if (typeof Craft.SproutForms === typeof undefined) {
    Craft.SproutForms = {};
}

(function($)
{
  /**
   * Craft.SproutForms.FieldLayoutEditor class
   * Handles the buttons for creating new groups and fields inside a the drag and drop UI
   */
  Craft.SproutForms.FieldLayoutEditor = Garnish.Base.extend({

    $container: null,
    $groupButton: null,
    $fieldButton: null,
    $settings: null,
    $pane: null,

    fld: null,
    modal: null,
    formLayout: null,
    fieldsLayout: null,
    wheelHtml: null,
    continueEditing : null,

    // The Dragula instance
    drake: null,
    // The dragula instance for tabs
    drakeTabs: null,
    tabsLayout: null,

    /**
     * The constructor.
     * @param - All the tabs of the form fieldLayout.
     */
    init: function(currentTabs, continueEditing)
    {
      var that = this;
      this.continueEditing = continueEditing;

      // Capture the already-initialized Craft.Pane to access later
      // @todo - update this for RC1 (news tabs are not working when added)
      this.$pane = $("div#sproutforms-fieldlayout-container.pane").data('pane');

      this.initButtons();

      this.initTabSettings();

      this.wheelHtml = ' <span class="settings icon"></span>';

      this.modal = Craft.SproutForms.FieldModal.getInstance();

      this.modal.on('newField', $.proxy(function(e)
      {
        var field = e.field;
        var group = field.group;
        this.addField(field.id, field.name, group.name);
      }, this));

      this.modal.on('saveField', $.proxy(function(e)
      {
        var field = e.field;
        var group = field.group;
        // Let's update the name and the icon if the field is updated
        this.resetField(field, group);
      }, this));

      // DRAGULA FOR TABS
      this.tabsLayout = this.getId('sprout-forms-tabs');
      this.drakeTabs = dragula([this.tabsLayout], {
        accepts: function (el, target, source, sibling) {
          // let's try to not allows reorder the PLUS icon
          return sibling === null || $(el).is('.drag-tab');
        },
        invalid: function (el, handle) {
            // do not move any item with donotdrag class.
            return el.classList.contains('donotdrag');
        },
      })
      .on('drag', function (el) {
        $(el).addClass('drag-tab-active');
      })
      .on('drop', function (el,target, source) {
        $(el).removeClass('drag-tab-active');
        $(target).find('.drag-tab-active').removeClass('drag-tab-active');
        $(source).find('.drag-tab-active').removeClass('drag-tab-active');
        // Reorder fields
        if ($(target).attr("id") == $(source).attr("id"))
        {
          // lets update the hidden tab field to reorder the tabs
          $("#sprout-forms-tabs li.drag-tab a").each(function(i) {
            var tabId = $(this).attr('id');
            var mainDiv = $("#sproutforms-fieldlayout-container");

            if (tabId)
            {
              var currentTab = $("#sproutforms-"+tabId);
              if (currentTab)
              {
                mainDiv.append(currentTab);
              }
            }
          });
        }
      });

      // DRAGULA
      this.fieldsLayout = this.getId('right-copy');

      // Drag from right to left
      this.drake = dragula([null, this.fieldsLayout], {
        copy: function (el, source) {
          return source === that.fieldsLayout;
        },
        accepts: function (el, target) {
          return target !== that.fieldsLayout
        },
      })
      .on('drag', function (el) {
        $(el).addClass('drag-active');
      })
      .on('drop', function (el,target, source) {
        $(el).removeClass('drag-active');
        $(target).find('.drag-active').removeClass('drag-active');
        $(source).find('.drag-active').removeClass('drag-active');

        // Reorder fields
        if ($(target).attr("id") == $(source).attr("id"))
        {
          // just if we need check when the field is reorder
          // not needed because the order is saved from the hidden field
          // when the form is saved
        }
        if (target && source === that.fieldsLayout)
        {
          // get the tab name by the first div fields
          var tab       = $(el).closest(".sproutforms-tab-fields");
          var tabName   = tab.data('tabname');
          var tabId     = tab.data('tabid');
          var fieldType = $(el).data("type");

          that.createDefaultField(fieldType, tabId, tabName, el);
        }
      })
      .on('over', function (el, container) {
        $(el).addClass('drag-active');
        $(container).addClass('container-active');
      })
      .on('out', function (el, container) {
        $(el).removeClass('drag-active');
        $(container).removeClass('container-active');
      });

      // Adds auto-scroll to main container when dragging
      var scroll = autoScroll(
        [
          document.querySelector('#content-container')
        ],
        {
          margin: 20,
          maxSpeed: 10,
          scrollWhenOutside: true,
          autoScroll: function(){
            //Only scroll when the pointer is down, and there is a child being dragged.
            return this.down && that.drake.dragging;
          }
        }
      );

      // Add the drop containers for each tab
      for (var i = 0; i < currentTabs.length; i++)
      {
        this.drake.containers.push(this.getId('sproutforms-tab-container-'+currentTabs[i].id));
      }
    },

    clickHandler: function (e) {
      var target = e.target;
      if (target === this.tabsLayout) {
        return;
      }
      target.innerHTML += ' [click!]';

      setTimeout(function () {
        target.innerHTML = target.innerHTML.replace(/ \[click!\]/g, '');
      }, 500);
    },

    createDefaultField: function(type, tabId, tabName, el)
    {
      $(el).removeClass('source-field');
      $(el).addClass('target-field');
      $(el).find('.source-field-header').remove();
      $(el).find('.body').removeClass('hidden');

      var defaultName = $(el).data('defaultname') ? $(el).data('defaultname') : 'Untitled'|t;

      // Add the Field Header
      $(el).prepend($([
        '<div class="active-field-header">',
        '<h2>', defaultName, '</h2>',
        '</div>'
      ].join('')));

      var formId = $("#formId").val();
      var data = {
        'type': type,
        'formId': formId,
        'tabId': tabId
      };

      Craft.postActionRequest('sprout-forms/fields/create-field', data, $.proxy(function(response, textStatus)
      {
        if (textStatus === 'success')
        {
          this.initFieldOnDrop(response.field, tabName, el);
        }
      }, this));
    },

    initFieldOnDrop: function(defaultField, tabName, el)
    {
      if(defaultField != null && defaultField.hasOwnProperty("id"))
      {
        $(el).attr('id', 'sproutfield-'+defaultField.id);

        // Add the Settings buttons
        $(el).prepend($([
          '<ul class="settings">',
          '<li><a id="sproutform-required-',defaultField.id,'" data-fieldid="',defaultField.id,'" href="#" tabindex="0" ><i class="fa fa-asterisk fa-2x" title="',Craft.t('sprout-forms', 'Make required'),'"></i></a></li>',
          '<li><a id="sproutform-field-',defaultField.id,'" data-fieldid="',defaultField.id,'" href="#" tabindex="0" ><i class="fa fa-pencil fa-2x" title="',Craft.t('sprout-forms', 'Edit'),'"></i></a></li>',
          '<li><a id="sproutform-remove-',defaultField.id,'" data-fieldid="',defaultField.id,'" href="#"><i class="fa fa-trash fa-2x" title="',Craft.t('sprout-forms', 'Remove'),'"></i></a></li>',
          '</ul>'
        ].join('')));

        // Add fieldLayout input
        $(el).append($([
          '<input type="hidden" name="fieldLayout[',tabName,'][]" value="',defaultField.id,'" class="id-input">'
          ].join('')
        ));

        this.addListener($("#sproutform-required-"+defaultField.id), 'activate', 'toggleRequiredField');
        this.addListener($("#sproutform-field-"+defaultField.id), 'activate', 'editField');
        this.addListener($("#sproutform-remove-"+defaultField.id), 'activate', 'removeField');
      }
      else
      {
        Craft.cp.displayError(Craft.t('sprout-forms','Something went wrong when creating the field :('));

        $(el).remove();
      }
    },

    getId: function(id)
    {
      return document.getElementById(id);
    },

    /**
     * Adds edit buttons to existing fields.
     */
    initTabSettings: function()
    {
        var that = this;
        // get all the links stars with sproutform-field-
        $("#sprout-forms-tabs li").each(function (i, el) {

          // #delete-tab-"+tab.id
            var tabId = $(el).find('a').attr('id');

            var $editBtn = $(el).find('.settings'),
                $menu = $('<div class="menu" data-align="center"/>').insertAfter($editBtn),
                $ul = $('<ul/>').appendTo($menu);

            $('<li><a data-action="rename" data-tab-id="'+tabId+'">' + Craft.t('app', 'Rename') + '</a></li>').appendTo($ul);
            $('<li><a id ="#delete-'+tabId+'" data-action="delete" data-tab-id="'+tabId+'">' + Craft.t('app', 'Delete') + '</a></li>').appendTo($ul);

            new Garnish.MenuBtn($editBtn, {
                onOptionSelect: $.proxy(that, 'onTabOptionSelect')
            });
        });
    },

    onTabOptionSelect: function(option) {
        var $option = $(option),
            tabId= $option.data('tab-id'),
            action = $option.data('action');

        switch (action) {
            case 'rename': {
                this.renameTab(tabId);
                break;
            }
            case 'delete': {
                this.deleteTab(tabId);
                break;
            }
        }
    },

    /**
     * Adds edit buttons to existing fields.
     */
    initButtons: function()
    {
      var that = this;
      // get all the links stars with sproutform-field-
      $("a[id^='sproutform-field-']").each(function (i, el) {
        var fieldId = $(el).data('fieldid');

        if(fieldId)
        {
          that.addListener($("#sproutform-required-"+fieldId), 'activate', 'toggleRequiredField');
          that.addListener($("#sproutform-field-"+fieldId), 'activate', 'editField');
          that.addListener($("#sproutform-remove-"+fieldId), 'activate', 'removeField');
        }
      });

      // get all the delete buttons
      $("a[id^='delete-tab-']").each(function (i, el) {
        var tabId = $(el).data('tabid');

        if(tabId)
        {
          that.addListener($("#delete-tab-"+tabId), 'activate', 'deleteTab');
        }
      });

      // listener to the new tab button
      this.addListener($("#sproutforms-fieldlayout-addtab"), 'activate', 'addNewTab');
    },

    deleteTab: function(tabId)
    {
      var userResponse = this.confirmDeleteTab();

      if (userResponse)
      {
        $("#sproutforms-"+tabId).slideUp(500, function() { $(this).remove(); });
        $("#"+tabId).closest( "li" ).slideUp(500, function() { $(this).remove(); });
      }
    },

    renameTab: function(tabId)
    {
        var $labelSpan = $('#'+tabId),
            oldName = $labelSpan.text().trim(),
            newName = prompt(Craft.t('app', 'Give your tab a name.'), oldName);

        if (newName && newName !== oldName) {
            $labelSpan.html(newName+this.wheelHtml);
            //$tab.find('.id-input').attr('name', this.getFieldInputName(newName));
            //add Continue editing and force save form
            $('#main-form input[name="redirect"]').remove();
            //@todo stay on same page is not working
            $( "#main-form" ).append("<input type='hidden' name='redirect' value ="+this.continueEditing+"/>");

            var $fields = $("input[name^='fieldLayout["+oldName+"][]']");
            $fields.each(function (i, el) {
                $(el).attr('name', 'fieldLayout['+newName+'][]');
            });
            $( "#main-form" ).submit();
        }
    },

    addNewTab: function()
    {
      var newName  = this.promptForGroupName('');
      var response = true;
      var $tabs    = $("[id^='sproutforms-tab-']");
      var formId   = $("#formId").val();
      var that     = this;

      // validate with current names and set the sortOrder
      $tabs.each(function (i, el) {
        var tabname = $(el).data('tabname');

        if(tabname == newName)
        {
          response = false;
          return false;
        }
      });

      if (response && newName && formId)
      {
        var data = {
          name: newName,
          // Minus the add tab button
          sortOrder: $tabs.length,
          formId : formId
        };

        Craft.postActionRequest('sprout-forms/fields/add-tab', data, $.proxy(function(response, textStatus)
        {
          if (response.success)
          {
            var tab = response.tab;

            Craft.cp.displayNotice(Craft.t('sprout-forms','Tab: '+tab.name+' created'));

            // Insert the new tab before the Add Tab button
            var href = '#sproutforms-tab-'+tab.id;
            $('<li><a id="tab-'+tab.id+'" class="tab" href="'+href+'" tabindex="0">'+tab.name+'</a></li>').insertBefore("#sproutforms-fieldlayout-addtab");

            var $newDivTab = $('#tab-'+tab.id);

            // Create the area to Drag/Drop fields on the new tab
            $("#sproutforms-fieldlayout-container").append($([
              '<div id="sproutforms-tab-'+tab.id+'" data-tabname="'+tab.name+'" data-tabid="'+tab.id+'" class="sproutforms-tab-fields hidden">',
              '<div class="parent">',
              '<div id="sproutforms-tab-container-'+tab.id+'" class="sprout-tab-container">',
              '</div>',
              '</div>',
              '</div>'
            ].join('')));

            // Convert our new tab into Dragula vampire :)
            this.drake.containers.push(this.getId('sproutforms-tab-container-'+tab.id));

            that.$pane.tabs[href] = {
              $tab: $newDivTab,
              $target: $(href)
            };

            this.$pane.addListener($newDivTab, 'activate', 'selectTab');
            // @todo remove this and add new delete listner
            //this.addListener($("#delete-tab-"+tab.id), 'activate', 'deleteTab');
          }
          else
          {
            console.log(response.errors);
            Craft.cp.displayError(Craft.t('sprout-forms','Unable to create a new tab'));
          }
        }, this));

      }
      else
      {
        Craft.cp.displayError(Craft.t('sprout-forms','Invalid tab name'));
      }

    },

    promptForGroupName: function(oldName)
    {
      return prompt(Craft.t('sprout-forms','What do you want to name your new tab?'), oldName);
    },

    confirmDeleteTab: function()
    {
      return confirm("Are you sure you want to delete this tab, all of it's fields, and all of it's data?");
    },

    /**
     * Event handler for the New Field button.
     * Creates a modal window that contains new field settings.
     */
    newField: function()
    {
      this.modal.show();
    },

    editField: function(option)
    {
      var option = option.currentTarget;

      var fieldId = $(option).data('fieldid');
      // Make our field available to our parent function
      this.$field = $(option);
      this.base($(option));

      this.modal.editField(fieldId);
    },

    removeField: function(option)
    {
      var option = option.currentTarget;

      var fieldId = $(option).data('fieldid');

      // Remove the div of the field
      $("#sproutfield-"+fieldId).fadeOut(120, function() {
        $(this).css({"visibility":"hidden",display:'block'}).slideUp(300, function() {
          $(this).remove();
        });
      });

      // Added behavior, store an array of deleted field IDs
      // that will be processed by the sprout-Forms/forms/saveForm method
      $deletedFieldsContainer = $('#deletedFieldsContainer');
      $($deletedFieldsContainer).append('<input type="hidden" name="deletedFields[]" value="' + fieldId + '">');
    },

    toggleRequiredField: function(option)
    {
      var option      = option.currentTarget;
      var fieldId     = $(option).data('fieldid');
      var $divField   = $("#sproutfield-"+fieldId);
      var $toggleLink = $("#sproutform-required-"+fieldId);

      if ($divField.find('.sproutforms-required-input').val())
      {
        $divField.find('h2').removeClass('required');
        $divField.find('.sproutforms-required-input').remove();

        setTimeout(function() {
          $toggleLink.html('<i class="fa fa-asterisk fa-2x" title="'+Craft.t('sprout-forms', 'Make required')+'"></i>');
        }, 500);
      }
      else
      {
        $divField.find('h2').addClass('required');

        $($divField).append('<input type="hidden" name="requiredFields[]" value="' + fieldId + '" class="sproutforms-required-input">');

        setTimeout(function() {
          $toggleLink.html('<i class="fa fa-asterisk fa-2x" title="'+Craft.t('sprout-forms', 'Make not required')+'"></i>');
        }, 500);
      }
    },

    /**
     * Renames | update icon | move field to another tab
     * of an existing field after edit it
     *
     * @param field
     * @param group
     */
    resetField: function(field, group)
    {
      var el = $("#sproutfield-"+field.id);
      // Lets update the the name and icon
      $(el).find('.body').html(field.htmlExample);
      $(el).find('.active-field-header h2').html(field.name);
      $(el).find('.active-field-header p').html(field.instructions);
      // Check if we need move the field to another tab
      var tab       = $(el).closest(".sproutforms-tab-fields");
      var tabName   = tab.data('tabname');
      var tabId     = tab.data('tabid');

      if (tabName != group.name)
      {
        // let's remove the hidden field just if the user change the tab
        $(el).find('.id-input').remove();
        // create the new hidden field and add it to the field div
        var $field = $([
          '<input class="id-input" type="hidden" name="fieldLayout[',group.name,'][]" value="', field.id, '">'
        ].join('')).appendTo($(el));
        // move the field to another tab
        var newTab = $("#sproutforms-tab-container-"+group.id);
        // move element to new div - like ctrl+x
        $(el).detach().appendTo($(newTab));
      }
    },

  });

})(jQuery);
