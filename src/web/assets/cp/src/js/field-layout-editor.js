/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

/* global Craft */
/* global dragula */

// import autoScroll from 'dom-autoscroller/dist/dom-autoscroller.min';
let dragula = require('dragula');
let autoScroll = require('dom-autoscroller');

(function($) {

  /**
   * Handles the buttons for creating new groups and fields inside a the drag and drop UI
   */
  class SproutFormsFieldLayoutEditor {

    constructor(formId) {

      if (window.sproutforms === undefined) {
        window.sproutforms = {};
      }

      this.formId = formId;
      this.$formPage = $( 'body.sprout-forms' );
      this.$formPageManagerBtn = $('#formPageManagerBtn');
      this.$addFormPageBtn = $('#addFormPageBtn');
      this.$saveFormButton = $('#save-form-button');

      this.$revisionSpinner = $('#revision-spinner');
      this.$revisionStatus = $('#revision-status');

      this.selectedTab = Craft.cp.$selectedTab;
      this.selectedTabId = this.selectedTab ? this.selectedTab.parent().data('id') : null;

      this.formPageManagerTabs = [];
      this.fieldModal = Craft.SproutForms.FieldModal.getInstance();

      // Initialize once
      this.initGlobalButtons();
      this.resizeTabContainers();

      // Initialize whenever we rebuild the layout
      this.initFieldLayout();
      this.initDragula();
    }

    initGlobalButtons() {
      const self = this;

      this.$formPageManagerBtn.on('click', function() {
        self.showFormPageManager();
      });

      this.$addFormPageBtn.on('click', function() {
        self.addFormPage();
      });

      this.$formPage.on( 'refreshFieldLayout', function() {
        self.refreshFieldLayout()
      });

      window.addEventListener('resize', function() {
        self.resizeTabContainers()
      });

      this.fieldModal.on('saveField', function(event) {
        $( 'body.sprout-forms').trigger( 'refreshFieldLayout');
      });
    }

    initFieldLayout() {
      // Add listeners to all the items that start with sproutform-field-
      let $formFields = $("a[id^='sproutform-field-']");
      for (let formField of $formFields) {
        let fieldId = $(formField).data('fieldid');
        if (fieldId) {
          let $field = $("#sproutform-field-" + fieldId);
          $field.on('activate', function(event) {
            self.showEditFieldModal(event);
          });
        }
      }
    }

    initDragula() {
      const self = this;

      if (window.sproutforms.formBuilder !== undefined) {
        window.sproutforms.formBuilder.destroy();
      }

      this.fieldsLayout = document.getElementById('right-copy');
      this.tabContainers = document.querySelectorAll('.sprout-tab-container');

      // Create an array of all our target containers
      let dragAndDropContainers = [...[this.fieldsLayout], ...this.tabContainers];

      window.sproutforms.formBuilder = dragula(dragAndDropContainers, {
        copy: function(el, source) {
          return source === self.fieldsLayout;
        },
        accepts: function(el, target) {
          return target !== self.fieldsLayout;
        },
        invalid: function(el, handle) {
          // do not move any item with donotdrag class.
          return el.classList.contains('donotdrag');
        }
      }).on('drag', function(el) {

        $(el).addClass('drag-active');

      }).on('drop', function(el, target, source) {

        $(el).removeClass('drag-active');
        $(target).find('.drag-active').removeClass('drag-active');
        $(source).find('.drag-active').removeClass('drag-active');

        // Reorder fields
        if ($(target).attr('id') === $(source).attr('id')) {
          // just if we need check when the field is reorder
          // not needed because the order is saved from the hidden field
          // when the form is saved
        }

        if (target && source === self.fieldsLayout) {
          // get the tab name by the first div fields
          const tab = $(el).closest(".sproutforms-tab-fields");
          const tabName = tab.data('tabname');
          const tabId = tab.data('tabid');
          const fieldType = $(el).data("type");

          self.createDefaultField(fieldType, tabId, tabName, el);
        }

      }).on('over', function(el, container) {

        $(el).addClass('drag-active');
        $(container).addClass('container-active');

      }).on('out', function(el, container) {

        $(el).removeClass('drag-active');
        $(container).removeClass('container-active');

      });

      // Adds auto-scroll to main container when dragging
      // dom-autoscroller: https://www.npmjs.com/package/dom-autoscroller
      autoScroll([...document.querySelectorAll('.sproutforms-tab-fields')], {
          margin: 20,
          maxSpeed: 10,
          scrollWhenOutside: true,
          autoScroll: function() {
            // Only scroll when the pointer is down, and there is a child being dragged.
            return this.down && window.sproutforms.formBuilder.dragging;
          }
        }
      );
    }

    resizeTabContainers() {
      let globalHeaderHeight = $('#global-header').outerHeight(true);
      let headerContainerHeight = $('#header-container').outerHeight(true);
      let tabHeight = $('#tabs').outerHeight();

      /** 44 = padding at top and bottom of content-pane
       *  48 = footer spacing between content pane footer  and browser */
      let bottomPaddingAdjustment = 44 + 48;
      let headerFooterHeight = globalHeaderHeight + headerContainerHeight + tabHeight + bottomPaddingAdjustment;

      // Height required to allow scrolling while dragging a field
      $('.sproutforms-tab-fields').height($(window).height() - headerFooterHeight);
    }

    createDefaultField(type, tabId, tabName, el) {
      let self = this;

      self.newTabName = tabName;
      self.newFieldElement = el;

      $(el).removeClass('source-field');
      $(el).addClass('target-field');
      $(el).find('.source-field-header').remove();
      $(el).find('.body').removeClass('hidden');
      // try to check position of the field
      let nextDiv = $(el).next('.target-field');
      let nextId = nextDiv.attr('id');
      if (typeof nextId === 'undefined' || nextId === null) {
        nextDiv = null;
      } else {
        // Last field
        const nextDivId = nextId.split('-');
        nextId = nextDivId[1];
      }

      const defaultName = $(el).data('defaultname') ? $(el).data('defaultname') : Craft.t('sprout-forms', 'Untitled Field');

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

      Craft.postActionRequest('sprout-forms/fields/create-field', data, function(response, textStatus) {
        if (textStatus === 'success') {
          self.initFieldOnDrop(response.field, self.newTabName, self.newFieldElement);
        }
      });
    }

    initFieldOnDrop(defaultField, tabName, el) {
      let self = this;

      if (defaultField !== null && defaultField.hasOwnProperty('id')) {
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

        $("#sproutform-field-" + defaultField.id).on('activate', function(event) {
          self.showEditFieldModal(event);
        });
      } else {
        Craft.cp.displayError(Craft.t('sprout-forms', 'Something went wrong when creating the field :('));

        $(el).remove();
      }
    }

    showEditFieldModal(event) {
      const option = event.currentTarget;
      const fieldId = $(option).data('fieldid');
      this.fieldModal.editField(fieldId);
    }

    showFormPageManager() {
      let self = this;

      if (!window.sproutforms.formPageManager) {
        console.log('pagemanager: show new');
        let $tabs = Craft.cp.$tabs ?? [];
        this.buildFormPageManagerElements($tabs);

        window.sproutforms.formPageManager = new Garnish.HUD(this.$formPageManagerBtn, this.$formPageManagerForm, {
          hudClass: 'hud formpagemanagerhud',
          onShow: function() {
            self.$formPageManagerBtn.addClass('active');
          },
          onHide: function() {
            self.$formPageManagerBtn.removeClass('active');
          }
        });

        this.initFormPageManagerAdminTable();

      } else {
        console.log('pagemanager: show existing');
        window.sproutforms.formPageManager.show();
      }
    }

    buildFormPageManagerElements($tabs) {
      let self = this;

      this.$formPageManagerForm = null;
      this.$formPageManagerNoTabs = null;
      this.$formPageManagerTable = null;

      this.$formPageManagerForm = $(
        '<form method="post" accept-charset="UTF-8">' +
        '<input type="hidden" name="action" value="sprout-forms/forms/save-form-page"/>' +
        '</form>'
      );
      this.$formPageManagerForm.appendTo(Garnish.$bod);

      this.$formPageManagerNoTabs = $('<p id="notabs"' + ($tabs.length ? ' class="hidden"' : '') + '>' + Craft.t('sprout-forms', 'You don’t have any tabs yet.') + '</p>');
      this.$formPageManagerNoTabs.appendTo(this.$formPageManagerForm);

      this.$formPageManagerTable = $('<table class="data' + (!$tabs.length ? ' hidden' : '') + '"/>');
      this.$formPageManagerTable.appendTo(this.$formPageManagerForm);

      let $tbody = $('<tbody/>').appendTo(this.$formPageManagerTable);

      for (let formTab of $tabs) {
        let tabId = formTab.getAttribute('data-id');
        let $row = self.getFormManagerTableRow(tabId, formTab);
        let $renameBtn = $row.find('> td.formpagemanagerhud-col-rename');

        $renameBtn.on('click', function(event) {
          self.renameFormPage(event);
        });

        self.formPageManagerTabs[tabId] = $row;
        $row.appendTo($tbody);
      }
    }

    initFormPageManagerAdminTable() {
      let self = this;

      window.sproutforms.formPageManager.adminTable = new Craft.AdminTable({
        tableSelector: self.$formPageManagerTable,
        noObjectsSelector: self.$formPageManagerNoTabs,
        sortable: true,
        reorderAction: 'sprout-forms/forms/reorder-form-tabs',
        reorderSuccessMessage: Craft.t('sprout-forms', 'Items reordered.'),
        reorderFailMessage: Craft.t('sprout-forms', 'Couldn’t reorder items.'),
        deleteAction: 'sprout-forms/forms/delete-form-tab',
        confirmDeleteMessage: Craft.t('sprout-forms', "Are you sure you want to delete this tab, all of it's fields, and all of it's data?"),
        onReorderItems: function(ids) {
          self.refreshFieldLayout('reorder');
        },
        onDeleteItem: function(id) {
          self.refreshFieldLayout('delete');
        }
      });
    }

    addFormPage() {
      let self = this;

      self.newTabName = prompt(Craft.t('sprout-forms', 'Page Name'));

      let data = {
        formId: self.formId,
        name: self.newTabName
      };

      this.$revisionSpinner.removeClass('hidden');

      Craft.postActionRequest('sprout-forms/forms/add-form-tab', data, function(response) {
        self.$revisionSpinner.addClass('hidden');
        self.$revisionStatus.removeClass('invisible');
        self.$revisionStatus.addClass('checkmark-icon');

        if (response.success) {
          self.refreshFieldLayout('add');
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to add page.'));
        }
      });
    }

    renameFormPage(event) {
      let self = this;

      let $row = $(event.currentTarget).parent();
      let oldName = $row.find('.formpagemanagerhud-col-page-name').text();
      this.newTabName = prompt(Craft.t('sprout-forms', 'Test'), oldName);
      let tabId = $row.data('id');

      if (this.newTabName === null) {
        return;
      }

      let data = {
        tabId: tabId,
        newName: this.newTabName
      };

      Craft.postActionRequest('sprout-forms/forms/rename-form-tab', data, function(response) {
        if (response.success) {
          self.refreshFieldLayout('rename');
          Craft.cp.displayNotice(Craft.t('sprout-forms', 'Page renamed.'));
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to rename page.'));
        }
      });

      if (this.newTabName && this.newTabName !== oldName) {
        // Tab in field layout
        $('li[data-id="' + tabId + '"] a').text(this.newTabName);

        // Tab row in Page Manager modal
        $row.find('.formpagemanagerhud-col-page-name').text(this.newTabName);
      }

      Craft.cp.initTabs();
    }

    getFormManagerTableRow(tabId, formTab) {

      let tabName = formTab.querySelector('a').innerText;

      return $(
        '<tr data-id="' + tabId + '" data-name="' + Craft.escapeHtml(tabName) + '">' +
        '<td class="formpagemanagerhud-col-page-name">' + tabName + '</td>' +
        '<td class="formpagemanagerhud-col-rename thin"><a class="edit icon" title="' + Craft.t('sprout-forms', 'Rename') + '" role="button"></a></td>' +
        '<td class="formpagemanagerhud-col-move thin"><a class="move icon" title="' + Craft.t('sprout-forms', 'Reorder') + '" role="button"></a></td>' +
        '<td class="thin"><a class="delete icon" title="' + Craft.t('app', 'Delete') + '" role="button"></a></td>' +
        '</tr>'
      );
    }

    refreshFieldLayout(currentActionType = null) {
      let self = this;
      this.currentActionType = currentActionType;
      this.$newTabs = null;

      let data = {
        formId: this.formId
      };

      Craft.postActionRequest('sprout-forms/forms/get-updated-layout-html', data, function(response) {
        if (response.success) {
          let $tabs = $('#tabs');
          if ($tabs.length) {
            $tabs.replaceWith(response.tabsHtml);
          } else {
            $(response.tabsHtml).insertBefore($('#content'))
          }

          $('#sproutforms-fieldlayout-container').html(response.contentHtml);

          // Grab content again because it may have changed
          Craft.initUiElements($('#content'));
          Craft.appendHeadHtml(response.headHtml);
          Craft.appendFootHtml(response.bodyHtml);

          Craft.cp.initTabs();
          self.initFieldLayout();
          self.initDragula();

          // Update Page Manager Modal to reflect new ID targets
          if (window.sproutforms.formPageManager) {
            console.log('pagemanager: REFRESH yes.');
            self.buildFormPageManagerElements(Craft.cp.$tabs);
            window.sproutforms.formPageManager.updateBody(self.$formPageManagerForm);
            self.initFormPageManagerAdminTable();
          }

          if (self.currentActionType === 'add') {
            let $lastTab = $(Craft.cp.$tabs[Craft.cp.$tabs.length - 1]);
            $lastTab.find('a').trigger('click');
          }

          if (self.currentActionType === 'delete') {
            let $firstTab = $(Craft.cp.$tabs[0]);
            Craft.cp.$selectedTab = null;
            $firstTab.find('a').trigger('click');
          }

          if (self.currentActionType === 'rename') {
            for (let tab of Craft.cp.$tabs) {
              let $tab = $(tab);
              let tabName = $tab.find('a').attr('title');
              if (typeof self.newTabName !== undefined && tabName === self.newTabName) {
                $tab.find('a').trigger('click');
              }
            }
          }

          if (self.currentActionType === 'reorder') {

          }

          return true;
        }

        return false;
      });
    }

    /**
     * Determine if a given HTML element exists within the current viewport
     *
     * @returns {boolean}
     */
    isInViewport($element) {
      let topOfElement = $element.offset().top;
      let bottomOfElement = $element.offset().top + $element.outerHeight();
      let bottomOfScreen = $(window).scrollTop() + $(window).innerHeight();
      let topOfScreen = $(window).scrollTop();

      return (bottomOfScreen > topOfElement) && (topOfScreen < bottomOfElement);
    }
  }

  window.SproutFormsFieldLayoutEditor = SproutFormsFieldLayoutEditor;

})(jQuery);

