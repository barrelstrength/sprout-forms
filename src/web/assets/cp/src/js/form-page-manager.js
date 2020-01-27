/* global Craft */
/* global Garnish */

if (typeof Craft.SproutForms === typeof undefined) {
  Craft.SproutForms = {};
}

(function($) {
  Craft.SproutForms.FormPageManager = Garnish.Base.extend({

    $formPageManagerBtn: null,
    $addFormPageBtn: null,

    formPageManager: null,
    tabAdminTable: null,
    formPageManagerTabs: [],
    selectedTabId: null,

    init: function(formId) {
      this.formId = formId;
      this.$formPageManagerBtn = $('#formPageManagerBtn');
      this.$addFormPageBtn = $('#addFormPageBtn');

      let selectedTab = Craft.cp.$selectedTab;
      this.selectedTabId = selectedTab ? selectedTab.parent().data('id') : null;

      this.addListener(this.$formPageManagerBtn, 'click', 'showFormPageManager');
      this.addListener(this.$addFormPageBtn, 'click', 'addFormPage');
    },

    showFormPageManager: function() {
      let self = this;

      if (!this.formPageManager) {
        let formTabs = Craft.cp.$tabs;

        let $form = $(
          '<form method="post" accept-charset="UTF-8">' +
          '<input type="hidden" name="action" value="sprout-forms/forms/save-form-page"/>' +
          '</form>'
        ).appendTo(Garnish.$bod);

        let $noTabs = $('<p id="notabs"' + (formTabs.length ? ' class="hidden"' : '') + '>' + Craft.t('sprout-forms', 'You don’t have any tabs yet.') + '</p>').appendTo($form);
        let $table = $('<table class="data' + (!formTabs.length ? ' hidden' : '') + '"/>').appendTo($form);
        let $tbody = $('<tbody/>').appendTo($table);

        for (let formTab of formTabs) {
          let tabId = formTab.getAttribute('data-id');
          let tabName = formTab.querySelector('a').innerText;

          let $row = $(
            '<tr data-id="' + tabId + '" data-name="' + Craft.escapeHtml(tabName) + '">' +
            '<td class="formpagemanagerhud-col-page-name">' + tabName + '</td>' +
            '<td class="formpagemanagerhud-col-rename thin"><a class="edit icon" title="' + Craft.t('app', 'Rename') + '" role="button"></a></td>' +
            '<td class="formpagemanagerhud-col-move thin"><a class="move icon" title="' + Craft.t('app', 'Reorder') + '" role="button"></a></td>' +
            '<td class="thin"><a class="delete icon" title="' + Craft.t('app', 'Delete') + '" role="button"></a></td>' +
            '</tr>'
          );

          $renameBtn = $row.find('> td.formpagemanagerhud-col-rename');

          this.addListener($renameBtn, 'click', function(event) {
            let $row = $(event.currentTarget).parent();
            let oldName = $row.find('.formpagemanagerhud-col-page-name').text();
            let newName = prompt(Craft.t('sprout-forms', 'Test'), oldName);
            let tabId = $row.data('id');

            let data = {
              tabId: tabId,
              newName: newName
            };

            Craft.postActionRequest('sprout-forms/fields/rename-form-tab', data, function(response) {
              if (response.success) {
                self.refreshTabs();
                Craft.cp.displayNotice(Craft.t('sprout-forms', 'Page renamed.'));
              }
              else {
                Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to rename page.'));
              }
            });

            if (newName && newName !== oldName) {
              // Tab in field layout
              $('li[data-id="'+tabId+'"] a').text(newName);

              // Tab row in Page Manager modal
              $row.find('.formpagemanagerhud-col-page-name').text(newName);
            }

            Craft.cp.initTabs();
          });

          this.formPageManagerTabs[tabId] = $row;
          $row.appendTo($tbody);
        }

        this.formPageManager = new Garnish.HUD(this.$formPageManagerBtn, $form, {
          hudClass: 'hud formpagemanagerhud',
          onShow: $.proxy(function() {
            this.$formPageManagerBtn.addClass('active');
          }, this),
          onHide: $.proxy(function() {
            this.$formPageManagerBtn.removeClass('active');
          }, this)
        });

        this.tabAdminTable = new Craft.AdminTable({
          tableSelector: $table,
          noObjectsSelector: $noTabs,
          sortable: true,
          reorderAction: 'sprout-forms/fields/reorder-form-tabs',
          reorderSuccessMessage: Craft.t('sprout-forms', 'Items reordered.') ,
          reorderFailMessage: Craft.t('sprout-forms', 'Couldn’t reorder items.'),
          deleteAction: 'sprout-forms/fields/delete-form-tab',
          confirmDeleteMessage: Craft.t('sprout-forms', "Are you sure you want to delete this tab, all of it's fields, and all of it's data?"),
          onReorderItems: $.proxy(function(ids) {
            let $tabList = self.getTabHtml(ids);
            $('#tabs ul').replaceWith($tabList);
            Craft.cp.initTabs();
          }, this),
          onDeleteItem: $.proxy(function(id) {
            let $pageManagerTab = this.formPageManagerTabs[id];
            $pageManagerTab.remove();
            self.refreshTabs();
          }, this)
        });

      } else {
        this.formPageManager.show();
      }

    },

    addFormPage: function() {
      let self = this;
      let name = prompt(Craft.t('sprout-forms', 'Page Name'));

      let data = {
        formId: this.formId,
        name: name
      };

      Craft.postActionRequest('sprout-forms/fields/add-form-tab', data, function(response) {
        if (response.success) {
          self.refreshTabs();
          Craft.cp.displayNotice(Craft.t('sprout-forms', 'Page added.'));
        }
        else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to add page.'));
        }
      });
    },

    refreshTabs: function() {
      let self = this;

      let data = {
        formId: this.formId
      };

      Craft.postActionRequest('sprout-forms/fields/get-form-tabs', data, function(response) {
        if (response.success) {
          let tabs = response.tabs;
          let $tabList = self.getTabHtml(tabs);
          $('#tabs ul').replaceWith($tabList);

          if ($(this.selectedTabId)) {
            $firstTab = $(Craft.cp.$tabs[0]);
            $firstTab.find('a').addClass('sel');
          }
          Craft.cp.initTabs();

          return true;
        }

        return false;
      });
    },

    getTabHtml: function(tabs) {
      console.log(tabs);
      let $tabList = $('<ul></ul>');

      for (let tab of tabs) {
        console.log(Craft.cp.$selectedTab);
        console.log(tabId === this.selectedTabId);
        console.log(tabId == this.selectedTabId);
        console.log(tab.id);
        console.log(this.selectedTabId);
        let tabId = tab.id;
        let tabName = tab.name;
        let selected = tabId === this.selectedTabId ? ' sel' : '';

        $(`<li data-id="${tabId}"><a id="tab-${tabId}" class="tab${selected}" href="#sproutforms-tab-${tabId}" title="${tabName}">${tabName}</a></li>`).appendTo($tabList);
      }

      return $tabList;
    }

  });
})(jQuery);