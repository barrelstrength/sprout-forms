/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

/* global Craft */
/* global Garnish */

if (typeof Craft.SproutForms === typeof undefined) {
  Craft.SproutForms = {};
}

(function($) {
  /**
   * @param window.WebKitMutationObserver
   */
  let MutationObserver = window.MutationObserver || window.WebKitMutationObserver;

  // If mutation observer is not supported, create a harness for it for graceful degradation.
  // Older browsers could be supported through the DOMNodeInserted event, but that can be saved for another day...
  if (!MutationObserver) {
    MutationObserver = function() {
    };
    MutationObserver.prototype.observe = function() {
    };
    MutationObserver.prototype.disconnect = function() {
    };
  }

  /**
   * SproutForms.FieldModal class
   * Handles the modal window for creating new fields.
   *
   * @param Garnish.Modal
   */
  Craft.SproutForms.FieldModal = Garnish.Modal.extend({

      $body: null,
      $content: null,
      $main: null,
      $footer: null,
      $leftButtons: null,
      $rightButtons: null,
      $saveBtn: null,
      $cancelBtn: null,
      $deleteBtn: null,
      $saveSpinner: null,
      $deleteSpinner: null,
      $loadSpinner: null,
      addedDelete: false,

      $html: null,
      $js: null,
      $css: null,
      $currentHtml: null,
      $currentJs: null,
      $currentCss: null,

      $observed: null,
      observer: null,

      templateLoaded: false,
      executedJs: null,
      loadedCss: null,

      /**
       * The constructor.
       */
      init: function(settings) {
        this.base();
        this.setSettings(settings, {
          resizable: true
        });

        this.$currentHtml = $();
        this.$currentJs = $();
        this.$currentCss = $();
        this.$observed = $();

        this.executedJs = {};
        this.loadedCss = {};

        // Observe the DOM
        this.observer = new MutationObserver($.proxy(function(mutations) {
          for (let i = 0; i < mutations.length; i++) {
            this.$observed = this.$observed.add(mutations[i].addedNodes);
          }
        }, this));

        const $container = $('<form class="modal sprout-field-modal" style="display: none; opacity: 0;">').appendTo(Garnish.$bod);

        this.$body = $('<div class="body">').appendTo($container);
        this.$content = $('<div class="content">').appendTo(this.$body);
        this.$main = $('<div class="main">').appendTo(this.$content);
        this.$footer = $('<div class="footer">').appendTo($container);
        this.$loadSpinner = $('<div class="spinner big">').appendTo($container);

        this.$leftButtons = $('<div class="buttons left">').appendTo(this.$footer);
        this.$rightButtons = $('<div class="buttons right">').appendTo(this.$footer);

        this.$deleteSpinner = $('<div class="spinner hidden">').appendTo(this.$leftButtons);

        this.$deleteBtn = $('<div class="btn delete hidden" role="button">').text(Craft.t('sprout-forms', 'Delete')).appendTo(this.$leftButtons);
        this.$cancelBtn = $('<div class="btn disabled" role="button">').text(Craft.t('sprout-forms', 'Cancel')).appendTo(this.$rightButtons);
        this.$saveBtn = $('<div class="btn submit disabled" role="button">').text(Craft.t('sprout-forms', 'Save')).appendTo(this.$rightButtons);
        this.$saveSpinner = $('<div class="spinner hidden">').appendTo(this.$rightButtons);

        this.setContainer($container);
        const formId = $("#formId").val();
        const postData = {
          formId: formId
        };

        // Loads the field settings template file, as well as all the resources that come with it
        Craft.postActionRequest('sprout-forms/fields/modal-field', postData, $.proxy(function(response, textStatus) {
          if (textStatus === 'success') {
            this.$loadSpinner.addClass('hidden');
            this.initTemplate(response);
          } else {
            this.destroy();
          }
        }, this));
      },

      /**
       * Prepares the field settings template HTML, CSS and Javascript.
       *
       * @param template
       */
      initTemplate: function(template) {
        const callback = $.proxy(function(e) {
          this.$html = e.$html;
          this.$js = e.$js;
          this.$css = e.$css;

          this.templateLoaded = true;
          this.initListeners();
          if (this.visible) {
            this.initSettings();
          }

          this.off('parseTemplate', callback);
        }, this);

        this.on('parseTemplate', callback);
        this.parseTemplate(template);
      },

      /**
       * Takes raw HTML, CSS and Javascript and parses it ready to be used in the DOM.
       * It also loads any external resources if they are needed.
       *
       * @param template
       */
      parseTemplate: function(template) {
        const that = this;
        const $head = Garnish.$doc.find('head');

        const $html = $(template.html);
        const $js = $(template.js).filter('script');
        const $css = $(template.css).filter('style, link');

        // Ensure that external stylesheets are loaded asynchronously
        const $cssFiles = $css.filter('link').prop('async', true);
        const $cssInline = $css.filter('style');

        $cssFiles.each(function() {
          const $this = $(this);
          const src = $this.prop('href');

          if (!that.loadedCss.hasOwnProperty(src)) {
            $head.append($this);
            that.loadedCss[src] = $this;
          }
        });

        // Load external Javascript files asynchronously, and remove them from being executed again.
        // This assumes that external Javascript files are simply library files, that don't directly and
        // instantly execute code that modifies the DOM. Library files can be loaded and executed once and
        // reused later on.
        // The Javascript tags that directly contain code are assumed to be context-dependent, so they are
        // saved to be executed each time the modal is opened.
        const $jsFiles = $js.filter('[src]');
        const $jsInline = $js.filter(':not([src])');

        const jsFiles = [];
        $jsFiles.each(function() {
          const $this = $(this);
          const src = $this.prop('src');
          if (!that.executedJs.hasOwnProperty(src)) {
            jsFiles.push(src);
            that.executedJs[src] = true;
          }
        });

        const callback = function() {
          that.off('runExternalScripts', callback);
          that.trigger('parseTemplate', {
            target: this,
            $html: $html,
            $js: $jsInline,
            $css: $cssInline
          });
        };
        // Fixes bug on Craft3 - Updates way to callback function
        $.when(this.runExternalScripts(jsFiles)).then(callback());
        //this.runExternalScripts(jsFiles);

        this.$deleteBtn.removeClass('hidden');
        this.$saveBtn.removeClass('disabled');
        this.$cancelBtn.removeClass('disabled');
      },

      /**
       * Runs external Javascript files
       *
       * @param files - An array of URL's (as strings) to Javascript files
       */
      runExternalScripts: function(files) {
        let filesCount = files.length;

        if (filesCount > 0) {
          for (let i = 0; i < files.length; i++) {
            const src = files[i];
            // Fixes Double-instantiating bug
            if ((src.indexOf('MatrixConfigurator') >= 0) ||
              (src.indexOf('TableFieldSettings.min.js') >= 0) ||
              (src.indexOf('quill.min.js') >= 0) ||
              (src.indexOf('sproutfields.js') >= 0) ||
              (src.indexOf('EditableTable.js') >= 0) ||
              (src.indexOf('initialize.js') >= 0)
            ) {
              $.getScript(src, $.proxy(function(data, status) {
                if (status === 'success') {
                  filesCount--;

                  if (filesCount === 0) {
                    this.trigger('runExternalScripts', {
                      target: this
                    });
                  }
                } else {
                  Craft.cp.displayError(Craft.t('sprout-forms', 'Could not load all resources.'));
                }
              }, this));
            }
          }
        } else {
          this.trigger('runExternalScripts', {
            target: this
          });
        }
      },

      /**
       * Binds all listeners so the quick field buttons can start working.
       */
      initListeners: function() {
        this.$deleteBtn.addClass('hidden');
        this.$cancelBtn.addClass('disabled');
        this.$saveBtn.addClass('disabled');

        this.addListener(this.$cancelBtn, 'activate', 'closeModal');
        this.addListener(this.$saveBtn, 'activate', 'saveField');
        if (!this.addedDelete) {
          this.addListener(this.$deleteBtn, 'click', 'deleteField');
          this.addedDelete = true;
        }

        this.on('show', this.initSettings);
        this.on('fadeOut', this.destroySettings);

        this.enable();
      },

      /**
       * Unbinds all listeners.
       */
      destroyListeners: function() {
        this.$cancelBtn.addClass('disabled');
        this.$saveBtn.addClass('disabled');

        this.removeListener(this.$cancelBtn, 'activate');
        this.removeListener(this.$saveBtn, 'activate');

        this.off('show', this.initSettings);
        this.off('fadeOut', this.destroySettings);

        this.disable();
      },

      /**
       * Initialises the HTML, CSS and Javascript for the modal window.
       */
      initSettings: function(e) {
        const that = e && e.target ? e.target : this;

        // If the template files are not loaded yet, just cancel initialisation of the settings.
        if (!that.templateLoaded) {
          return;
        }

        that.$currentHtml = e && e.$html ? e.$html : that.$html.clone();
        that.$currentJs = e && e.$js ? e.$js : that.$js.clone();
        that.$currentCss = e && e.$css ? e.$css : that.$css.clone();

        // Save any new nodes that are added to the body during initialisation, so they can be safely removed later.
        that.$observed = $();
        that.observer.observe(Garnish.$bod[0], {childList: true, subtree: false});

        that.$main.append(that.$currentHtml);
        Garnish.$bod.append(that.$currentJs);

        // Only show the delete button if editing a field
        const $fieldId = that.$main.find('input[name="fieldId"]');

        Craft.initUiElements();

        // Rerun the external scripts as some field types may need to make DOM changes in their external files.
        // This means that libraries are being initialized multiple times, but hopefully they're smart enough to
        // deal with that. So far, no issues.
        const callback = function() {
          that.off('runExternalScripts', callback);

          // Stop observing after a healthy timeout to ensure all mutations are captured.
          setTimeout(function() {
            that.observer.disconnect();
          }, 1);
        };
        $.when(that.runExternalScripts(Object.keys(that.executedJs))).then(callback());
        //that.on('runExternalScripts', callback);
        //that.runExternalScripts(Object.keys(that.executedJs));
      },

      /**
       * Event handler for when the modal window finishes fading out after hiding.
       * Clears out all events and elements of the modal.
       */
      destroySettings: function(e) {
        const that = e && e.target ? e.target : this;

        that.$currentHtml.remove();
        that.$currentJs.remove();
        that.$currentCss.remove();
        that.$observed.remove();
      },

      /**
       * Event handler for the Close button.
       * Hides the modal window from view.
       */
      closeModal: function() {
        this.hide();
      },

      /**
       * Event handler for the save button.
       * Saves the new field form to the database.
       *
       * @param e
       */
      saveField: function(e) {
        if (e) {
          e.preventDefault();
        }

        if (this.$saveBtn.hasClass('disabled') || !this.$saveSpinner.hasClass('hidden')) {
          return;
        }

        this.destroyListeners();

        this.$saveSpinner.removeClass('hidden');
        const data = this.$container.serialize();

        const inputId = this.$container.find('input[name="fieldId"]');
        const id = inputId.length ? inputId.val() : false;

        Craft.postActionRequest('sprout-forms/fields/save-field', data, $.proxy(function(response, textStatus) {
          this.$saveSpinner.addClass('hidden');

          const statusSuccess = (textStatus === 'success');

          if (statusSuccess && response.success) {
            this.initListeners();

            if (id === false) {
              this.trigger('newField', {
                target: this,
                field: response.field
              });
            } else {
              this.trigger('saveField', {
                target: this,
                field: response.field
              });

              Craft.cp.displayNotice(Craft.t('sprout-forms', '\'{name}\' field saved.', {name: response.field.name}));
            }

            this.hide();
          } else if (statusSuccess && response.template) {
            if (this.visible) {
              const callback = $.proxy(function(e) {
                this.initListeners();
                this.destroySettings();
                this.initSettings(e);
                this.off('parseTemplate', callback);
              }, this);

              this.on('parseTemplate', callback);
              this.parseTemplate(response.template);

              Garnish.shake(this.$container);
            } else {
              this.initListeners();
            }
          } else {
            this.initListeners();

            Craft.cp.displayError(Craft.t('sprout-forms', 'An unknown error occurred.'));
          }
        }, this));
      },

      /**
       *
       * @param id
       */
      editField: function(id) {
        this.destroyListeners();
        this.show();
        this.initListeners();

        this.$loadSpinner.removeClass('hidden');

        const formId = $("#formId").val();
        const data = {'fieldId': id, 'formId': formId};

        Craft.postActionRequest('sprout-forms/fields/edit-field', data, $.proxy(function(response, textStatus) {
          this.$loadSpinner.addClass('hidden');

          const statusSuccess = (textStatus === 'success');

          if (statusSuccess && response.success) {
            const callback = $.proxy(function(e) {
              this.destroySettings();
              this.initSettings(e);
              this.off('parseTemplate', callback);
            }, this);

            this.on('parseTemplate', callback);
            this.parseTemplate(response.template);
          } else if (statusSuccess && response.error) {
            Craft.cp.displayError(response.error);

            this.hide();
          } else {
            Craft.cp.displayError(Craft.t('sprout-forms', 'An unknown error occurred. '));

            this.hide();
          }
        }, this));
      },

      deleteField: function(e) {
        e.preventDefault();
        const userResponse = this.confirmDeleteField();

        if (userResponse) {
          this.destroyListeners();

          const data = this.$container.serialize();

          const fieldId = $(this.$container).find('input[name="fieldId"]').val();

          Craft.postActionRequest('sprout-forms/fields/delete-field', data, $.proxy(function(response, textStatus) {
            const statusSuccess = (textStatus === 'success');

            if (statusSuccess && response.success) {

              Craft.cp.displayNotice(Craft.t('sprout-forms', 'Field deleted.'));

              $('#sproutfield-' + fieldId).remove();

              this.initListeners();
              this.hide();
            } else {
              Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to delete field.'));

              this.hide();
            }
          }, this));
        }
      },

      confirmDeleteField: function() {
        return confirm("Are you sure you want to delete this field and all of it's data?");
      },

      /**
       * Prevents the modal from closing if it's disabled.
       * This fixes issues if the modal is closed when saving/deleting fields.
       */
      hide: function() {
        if (!this._disabled) {
          this.base();
        }
      },

      /**
       * Removes everything to do with the modal form the DOM.
       */
      destroy: function() {

        this.base.destroy();

        this.destroyListeners();
        this.destroySettings();

        this.$shade.remove();
        this.$container.remove();

        this.trigger('destroy');
      }
    },
    {
      /**
       * (Static) Singleton pattern.
       *
       * @returns FieldModal
       */
      getInstance: function() {
        if (!this._instance) {
          this._instance = new Craft.SproutForms.FieldModal();
        }

        return this._instance;
      }
    });

})(jQuery);
