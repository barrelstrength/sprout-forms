/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

/* global Craft */
/* global Garnish */

// if (typeof Craft.SproutForms === undefined) {
//   Craft.SproutForms = {};
// }
//
(function($) {
//   Craft.SproutForms.FormPageManager = Garnish.Base.extend({

  class SproutFormsPageManager {
    // $formPageManagerBtn: null,
    // $addFormPageBtn: null,
    //
    // formPageManager: null,
    // $formPageManagerTable: null,
    // $formPageManagerNoTabs: null,
    //
    // tabAdminTable: null,
    // formPageManagerTabs: [],
    //
    // totalFormTabs: null,
    // selectedTab: null,
    // selectedTabId: null,
    //
    // newTabName: null,
    // $newTabs: null,
    //
    // $renamedTab: null,
    //
    // currentActionType: null,

    constructor(formId) {

      let self = this;

      this.formId = formId;
      this.$formPageManagerBtn = $('#formPageManagerBtn');
      this.$addFormPageBtn = $('#addFormPageBtn');
      this.$formPage = $( 'body.sprout-forms' );

      this.$revisionSpinner = $('#revision-spinner');
      this.$revisionStatus = $('#revision-status');

      this.selectedTab = Craft.cp.$selectedTab;
      this.selectedTabId = this.selectedTab ? this.selectedTab.parent().data('id') : null;

      this.formPageManagerTabs = [];

      this.$formPageManagerBtn.on('click', function() {
        self.showFormPageManager();
      });
      this.$addFormPageBtn.on('click', function() {
        self.addFormPage();
      });
      this.$formPage.on( 'refreshFieldLayout', function() {
        self.refreshFieldLayout()
      });
    }

    showFormPageManager() {
      let self = this;

      if (!this.formPageManager) {
        let $tabs = Craft.cp.$tabs ?? [];
        this.buildFormPageManagerElements($tabs);

        this.formPageManager = new Garnish.HUD(this.$formPageManagerBtn, this.$formPageManagerForm, {
          hudClass: 'hud formpagemanagerhud',
          onShow: $.proxy(function() {
            self.$formPageManagerBtn.addClass('active');
          }, this),
          onHide: $.proxy(function() {
            self.$formPageManagerBtn.removeClass('active');
          }, this)
        });

        this.initFormPageManagerAdminTable();

      } else {
        this.formPageManager.show();
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

      this.tabAdminTable = new Craft.AdminTable({
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

          // Update Page Manager Modal to reflect new ID targets
          self.buildFormPageManagerElements(Craft.cp.$tabs);
          if (self.formPageManager) {
            self.formPageManager.updateBody(self.$formPageManagerForm);
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
  }

  window.SproutFormsPageManager = SproutFormsPageManager;

})(jQuery);

