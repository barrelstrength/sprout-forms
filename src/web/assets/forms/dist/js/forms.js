/******/
(function(modules) { // webpackBootstrap
  /******/ 	// The module cache
  /******/
  var installedModules = {};
  /******/
  /******/ 	// The require function
  /******/
  function __webpack_require__(moduleId) {
    /******/
    /******/ 		// Check if module is in cache
    /******/
    if (installedModules[moduleId]) {
      /******/
      return installedModules[moduleId].exports;
      /******/
    }
    /******/ 		// Create a new module (and put it into the cache)
    /******/
    var module = installedModules[moduleId] = {
      /******/      i: moduleId,
      /******/      l: false,
      /******/      exports: {}
      /******/
    };
    /******/
    /******/ 		// Execute the module function
    /******/
    modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
    /******/
    /******/ 		// Flag the module as loaded
    /******/
    module.l = true;
    /******/
    /******/ 		// Return the exports of the module
    /******/
    return module.exports;
    /******/
  }

  /******/
  /******/
  /******/ 	// expose the modules object (__webpack_modules__)
  /******/
  __webpack_require__.m = modules;
  /******/
  /******/ 	// expose the module cache
  /******/
  __webpack_require__.c = installedModules;
  /******/
  /******/ 	// define getter function for harmony exports
  /******/
  __webpack_require__.d = function(exports, name, getter) {
    /******/
    if (!__webpack_require__.o(exports, name)) {
      /******/
      Object.defineProperty(exports, name, {enumerable: true, get: getter});
      /******/
    }
    /******/
  };
  /******/
  /******/ 	// define __esModule on exports
  /******/
  __webpack_require__.r = function(exports) {
    /******/
    if (typeof Symbol !== 'undefined' && Symbol.toStringTag) {
      /******/
      Object.defineProperty(exports, Symbol.toStringTag, {value: 'Module'});
      /******/
    }
    /******/
    Object.defineProperty(exports, '__esModule', {value: true});
    /******/
  };
  /******/
  /******/ 	// create a fake namespace object
  /******/ 	// mode & 1: value is a module id, require it
  /******/ 	// mode & 2: merge all properties of value into the ns
  /******/ 	// mode & 4: return value when already ns object
  /******/ 	// mode & 8|1: behave like require
  /******/
  __webpack_require__.t = function(value, mode) {
    /******/
    if (mode & 1) {
      value = __webpack_require__(value);
    }
    /******/
    if (mode & 8) {
      return value;
    }
    /******/
    if ((mode & 4) && typeof value === 'object' && value && value.__esModule) {
      return value;
    }
    /******/
    var ns = Object.create(null);
    /******/
    __webpack_require__.r(ns);
    /******/
    Object.defineProperty(ns, 'default', {enumerable: true, value: value});
    /******/
    if (mode & 2 && typeof value != 'string') {
      for (var key in value) {
        __webpack_require__.d(ns, key, function(key) {
          return value[key];
        }.bind(null, key));
      }
    }
    /******/
    return ns;
    /******/
  };
  /******/
  /******/ 	// getDefaultExport function for compatibility with non-harmony modules
  /******/
  __webpack_require__.n = function(module) {
    /******/
    var getter = module && module.__esModule ?
      /******/      function getDefault() {
        return module['default'];
      } :
      /******/      function getModuleExports() {
        return module;
      };
    /******/
    __webpack_require__.d(getter, 'a', getter);
    /******/
    return getter;
    /******/
  };
  /******/
  /******/ 	// Object.prototype.hasOwnProperty.call
  /******/
  __webpack_require__.o = function(object, property) {
    return Object.prototype.hasOwnProperty.call(object, property);
  };
  /******/
  /******/ 	// __webpack_public_path__
  /******/
  __webpack_require__.p = "/";
  /******/
  /******/
  /******/ 	// Load entry module and return exports
  /******/
  return __webpack_require__(__webpack_require__.s = 1);
  /******/
})
/************************************************************************/
/******/({

  /***/ "./src/web/assets/forms/src/js/ConditionalModal.js":
  /*!*********************************************************!*\
    !*** ./src/web/assets/forms/src/js/ConditionalModal.js ***!
    \*********************************************************/
  /*! no static exports found */
  /***/ (function(module, exports, __webpack_require__) {

    function _typeof(obj) {
      if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
        _typeof = function _typeof(obj) {
          return typeof obj;
        };
      } else {
        _typeof = function _typeof(obj) {
          return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
        };
      }
      return _typeof(obj);
    }

    /*
     * @link      https://sprout.barrelstrengthdesign.com/
     * @copyright Copyright (c) Barrel Strength Design LLC
     * @license   http://sprout.barrelstrengthdesign.com/license
     */
    if (_typeof(Craft.SproutForms) === (true ? "undefined" : undefined)) {
      Craft.SproutForms = {};
    }

    (function($) {
      var MutationObserver = window.MutationObserver || window.WebKitMutationObserver; // If mutation observer is not supported, create a harness for it for graceful degradation.
      // Older browsers could be supported through the DOMNodeInserted event, but that can be saved for another day...

      if (!MutationObserver) {
        MutationObserver = function MutationObserver() {
        };

        MutationObserver.prototype.observe = function() {
        };

        MutationObserver.prototype.disconnect = function() {
        };
      }
      /**
       * SproutForms.ConditionalModal class
       * Handles the modal window for editing conditionals.
       */


      Craft.SproutForms.ConditionalModal = Garnish.Modal.extend({
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
        init: function init(settings) {
          this.base();
          this.setSettings(settings, {
            resizable: true
          });
          this.$currentHtml = $();
          this.$currentJs = $();
          this.$currentCss = $();
          this.$observed = $();
          this.executedJs = {};
          this.loadedCss = {}; // Observe the DOM

          this.observer = new MutationObserver($.proxy(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
              this.$observed = this.$observed.add(mutations[i].addedNodes);
            }
          }, this));
          var $container = $('<form class="modal sprout-field-modal" style="display: none; opacity: 0;">').appendTo(Garnish.$bod);
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
          this.$loadSpinner.addClass('hidden');
          var response = {
            html: '',
            js: '',
            css: ''
          };
          this.initTemplate(response);
        },

        /**
         * Prepares the Conditional settings template HTML, CSS and Javascript.
         *
         * @param template
         */
        initTemplate: function initTemplate(template) {
          var callback = $.proxy(function(e) {
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
        parseTemplate: function parseTemplate(template) {
          var that = this;
          var $head = Garnish.$doc.find('head');
          var $html = $(template.html);
          var $js = $(template.js).filter('script');
          var $css = $(template.css).filter('style, link'); // Ensure that external stylesheets are loaded asynchronously

          var $cssFiles = $css.filter('link').prop('async', true);
          var $cssInline = $css.filter('style');
          $cssFiles.each(function() {
            var $this = $(this);
            var src = $this.prop('href');

            if (!that.loadedCss.hasOwnProperty(src)) {
              $head.append($this);
              that.loadedCss[src] = $this;
            }
          }); // Load external Javascript files asynchronously, and remove them from being executed again.
          // This assumes that external Javascript files are simply library files, that don't directly and
          // instantly execute code that modifies the DOM. Library files can be loaded and executed once and
          // reused later on.
          // The Javascript tags that directly contain code are assumed to be context-dependent, so they are
          // saved to be executed each time the modal is opened.

          var $jsFiles = $js.filter('[src]');
          var $jsInline = $js.filter(':not([src])');
          var jsFiles = [];
          $jsFiles.each(function() {
            var $this = $(this);
            var src = $this.prop('src');

            if (!that.executedJs.hasOwnProperty(src)) {
              jsFiles.push(src);
              that.executedJs[src] = true;
            }
          });

          var callback = function callback() {
            that.off('runExternalScripts', callback);
            that.trigger('parseTemplate', {
              target: this,
              $html: $html,
              $js: $jsInline,
              $css: $cssInline
            });
          }; // Fixes bug on Craft3 - Updates way to callback function


          $.when(this.runExternalScripts(jsFiles)).then(callback()); //this.runExternalScripts(jsFiles);

          this.$deleteBtn.removeClass('hidden');
          this.$saveBtn.removeClass('disabled');
          this.$cancelBtn.removeClass('disabled');
        },

        /**
         * Runs external Javascript files
         *
         * @param files - An array of URL's (as strings) to Javascript files
         */
        runExternalScripts: function runExternalScripts(files) {
          var filesCount = files.length;

          if (filesCount > 0) {
            for (var i = 0; i < files.length; i++) {
              var src = files[i]; // Fixes Double-instantiating bug

              if (src.indexOf('MatrixConfigurator') >= 0 || src.indexOf('TableFieldSettings.min.js') >= 0 || src.indexOf('quill.min.js') >= 0 || src.indexOf('sproutfields.js') >= 0 || src.indexOf('EditableTable.js') >= 0 || src.indexOf('initialize.js') >= 0) {
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
         * Binds all listeners so the quick conditional buttons can start working.
         */
        initListeners: function initListeners() {
          this.$deleteBtn.addClass('hidden');
          this.$cancelBtn.addClass('disabled');
          this.$saveBtn.addClass('disabled');
          this.addListener(this.$cancelBtn, 'activate', 'closeModal');
          this.addListener(this.$saveBtn, 'activate', 'saveConditional');

          if (!this.addedDelete) {
            this.addListener(this.$deleteBtn, 'click', 'deleteConditional');
            this.addedDelete = true;
          }

          this.on('show', this.initSettings);
          this.on('fadeOut', this.destroySettings);
          this.enable();
        },

        /**
         * Unbinds all listeners.
         */
        destroyListeners: function destroyListeners() {
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
        initSettings: function initSettings(e) {
          var that = e && e.target ? e.target : this; // If the template files are not loaded yet, just cancel initialisation of the settings.

          if (!that.templateLoaded) {
            return;
          }

          that.$currentHtml = e && e.$html ? e.$html : that.$html.clone();
          that.$currentJs = e && e.$js ? e.$js : that.$js.clone();
          that.$currentCss = e && e.$css ? e.$css : that.$css.clone(); // Save any new nodes that are added to the body during initialisation, so they can be safely removed later.

          that.$observed = $();
          that.observer.observe(Garnish.$bod[0], {
            childList: true,
            subtree: false
          });
          that.$main.append(that.$currentHtml);
          Garnish.$bod.append(that.$currentJs);
          Craft.initUiElements(); // Rerun the external scripts as some field types may need to make DOM changes in their external files.
          // This means that libraries are being initialized multiple times, but hopefully they're smart enough to
          // deal with that. So far, no issues.

          var callback = function callback() {
            that.off('runExternalScripts', callback); // Stop observing after a healthy timeout to ensure all mutations are captured.

            setTimeout(function() {
              that.observer.disconnect();
            }, 1);
          };

          $.when(that.runExternalScripts(Object.keys(that.executedJs))).then(callback()); //that.on('runExternalScripts', callback);
          //that.runExternalScripts(Object.keys(that.executedJs));
        },

        /**
         * Event handler for when the modal window finishes fading out after hiding.
         * Clears out all events and elements of the modal.
         */
        destroySettings: function destroySettings(e) {
          var that = e && e.target ? e.target : this;
          that.$currentHtml.remove();
          that.$currentJs.remove();
          that.$currentCss.remove();
          that.$observed.remove();
        },

        /**
         * Event handler for the Close button.
         * Hides the modal window from view.
         */
        closeModal: function closeModal() {
          this.hide();
        },

        /**
         * Event handler for the save button.
         * Saves the Conditional settings to the database.
         *
         * @param e
         */
        saveConditional: function saveConditional(e) {
          if (e) {
            e.preventDefault();
          }

          if (this.$saveBtn.hasClass('disabled') || !this.$saveSpinner.hasClass('hidden')) {
            return;
          }

          this.destroyListeners();
          this.$saveSpinner.removeClass('hidden');
          var data = this.$container.serialize();
          var inputId = this.$container.find('input[name="conditionalId"]');
          var id = inputId.length ? inputId.val() : false;
          Craft.postActionRequest('sprout-forms/conditionals/save-conditional', data, $.proxy(function(response, textStatus) {
            this.$saveSpinner.addClass('hidden');
            var statusSuccess = textStatus === 'success';

            if (statusSuccess && response.success) {
              this.initListeners();
              this.trigger('saveConditional', {
                target: this,
                conditional: response.conditional
              });
              Craft.cp.displayNotice(Craft.t('sprout-forms', '\'{name}\' conditional saved.', {
                name: response.conditional.name
              }));
              this.hide();
            } else if (statusSuccess && response.template) {
              if (this.visible) {
                var callback = $.proxy(function(e) {
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
        editConditional: function editConditional(id) {
          this.destroyListeners();
          this.show();
          this.initListeners();
          this.$loadSpinner.removeClass('hidden');
          var formId = $("#formId").val();
          var data = {
            'conditionalId': id,
            'formId': formId
          };
          Craft.postActionRequest('sprout-forms/conditionals/edit-conditional', data, $.proxy(function(response, textStatus) {
            this.$loadSpinner.addClass('hidden');
            var statusSuccess = textStatus === 'success';

            if (statusSuccess && response.success) {
              var callback = $.proxy(function(e) {
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
        deleteConditional: function deleteConditional(e) {
          e.preventDefault();
          var userResponse = this.confirmDeleteConditional();

          if (userResponse) {
            this.destroyListeners();
            var data = this.$container.serialize();
            var conditionalId = $(this.$container).find('input[name="conditionalId"]').val();
            Craft.postActionRequest('sprout-forms/conditionals/delete-conditional', data, $.proxy(function(response, textStatus) {
              var statusSuccess = textStatus === 'success';

              if (statusSuccess && response.success) {
                Craft.cp.displayNotice(Craft.t('sprout-forms', 'Conditional deleted.'));
                $('#sproutforms-conditional-row-' + conditionalId).remove();
                this.initListeners();
                this.hide();
              } else {
                Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to delete conditional.'));
                this.hide();
              }
            }, this));
          }
        },
        confirmDeleteConditional: function confirmDeleteConditional() {
          return confirm("Are you sure you want to delete this conditional and all of it's settings?");
        },

        /**
         * Prevents the modal from closing if it's disabled.
         * This fixes issues if the modal is closed when saving/deleting conditionals.
         */
        hide: function hide() {
          if (!this._disabled) {
            this.base();
          }
        },

        /**
         * Removes everything to do with the modal form the DOM.
         */
        destroy: function destroy() {
          this.base.destroy();
          this.destroyListeners();
          this.destroySettings();
          this.$shade.remove();
          this.$container.remove();
          this.trigger('destroy');
        }
      }, {
        /**
         * (Static) Singleton pattern.
         *
         * @returns ConditionalModal
         */
        getInstance: function getInstance() {
          if (!this._instance) {
            this._instance = new Craft.SproutForms.ConditionalModal();
          }

          return this._instance;
        }
      });
    })(jQuery);

    /***/
  }),

  /***/ "./src/web/assets/forms/src/js/EditableTable.js":
  /*!******************************************************!*\
    !*** ./src/web/assets/forms/src/js/EditableTable.js ***!
    \******************************************************/
  /*! no static exports found */
  /***/ (function(module, exports, __webpack_require__) {

    function _typeof(obj) {
      if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
        _typeof = function _typeof(obj) {
          return typeof obj;
        };
      } else {
        _typeof = function _typeof(obj) {
          return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
        };
      }
      return _typeof(obj);
    }

    /*
     * @link      https://sprout.barrelstrengthdesign.com/
     * @copyright Copyright (c) Barrel Strength Design LLC
     * @license   http://sprout.barrelstrengthdesign.com/license
     */
    if (_typeof(Craft.SproutForms) === (true ? "undefined" : undefined)) {
      Craft.SproutForms = {};
    }
    /**
     * Editable table class
     */


    Craft.SproutForms.EditableTable = Garnish.Base.extend({
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
      init: function init(id, baseName, columns, settings, conditionalTypes) {
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
      isVisible: function isVisible() {
        return this.$table.height() > 0;
      },
      initialize: function initialize(conditionalTypes) {
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
      initializeIfVisible: function initializeIfVisible() {
        if (this.isVisible()) {
          this.initialize();
        }
      },
      addRow: function addRow() {
        var rowId = this.settings.rowIdPrefix + (this.biggestId + 1),
          rowHtml = Craft.SproutForms.EditableTable.getRowHtml(rowId, this.columns, this.baseName, {}, this.conditionalTypes),
          $tr = $(rowHtml).appendTo(this.$tbody);
        new Craft.SproutForms.EditableTable.Row(this, $tr, this.conditionalTypes);
        var $container = $tr.find('.sprout-selectother');
        this.sorter.addItems($tr); // Focus the first input in the row

        $tr.find('input,textarea,select').first().focus();
        this.settings.onAddRow($tr);
        this.$addRowBtn = $tr.find('#add-rule');
        this.addListener(this.$addRowBtn, 'activate', 'addRow');
      }
    }, {
      textualColTypes: ['singleline', 'multiline', 'number'],
      defaults: {
        rowIdPrefix: '',
        onAddRow: $.noop,
        onDeleteRow: $.noop
      },
      getRowHtml: function getRowHtml(rowId, columns, baseName, values, conditionalTypes) {
        var rowHtml = '<tr data-id="' + rowId + '">';
        var formFieldName = "";
        var formFieldValue = "";
        var conditionFieldName = "";
        var conditionFieldValue = "";

        for (var colId in columns) {
          var col = columns[colId],
            name = baseName + '[' + rowId + '][' + colId + ']',
            value = typeof values[colId] !== 'undefined' ? values[colId] : '',
            textual = Craft.inArray(col.type, Craft.SproutForms.EditableTable.textualColTypes);

          if (colId == 0) {
            formFieldName = name;
            formFieldValue = value != '' ? value : col.options[0].value;
          }

          rowHtml += '<td class="' + (textual ? 'textual' : '') + ' ' + (typeof col['class'] !== 'undefined' ? col['class'] : '') + '"' + (typeof col['width'] !== 'undefined' ? ' width="' + col['width'] + '"' : '') + '>';

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
                  var optionLabel = typeof option.label !== 'undefined' ? option.label : option,
                    optionValue = typeof option.value !== 'undefined' ? option.value : key,
                    optionDisabled = typeof option.disabled !== 'undefined' ? option.disabled : false;
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
                  var optionLabel = typeof option.label !== 'undefined' ? option.label : option,
                    optionValue = typeof option.value !== 'undefined' ? option.value : key,
                    optionDisabled = typeof option.disabled !== 'undefined' ? option.disabled : false;
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
              rowHtml += '<input type="hidden" name="' + name + '">' + '<input type="checkbox" name="' + name + '" value="1"' + (value ? ' checked' : '') + '>';
              break;
            }

            default: {
              rowHtml += '<input class="text fullwidth" type="text" name="' + name + '" value="' + value + '">';
            }
          }

          rowHtml += '</td>';
        }

        rowHtml += '<td class="thin action"><div class="buttons"> <div id="add-rule" class="btn add icon small" tabindex="0">OR</div> </div></td>' + '<td class="thin action"><a class="move icon" title="' + Craft.t('sprout-seo', 'Reorder') + '"></a></td>' + '<td class="thin action"><a class="delete icon" title="' + Craft.t('sprout-seo', 'Delete') + '"></a></td>' + '</tr>';
        return rowHtml;
      }
    });
    /**
     * Editable table row class
     */

    Craft.SproutForms.EditableTable.Row = Garnish.Base.extend({
      table: null,
      id: null,
      niceTexts: null,
      $tr: null,
      $tds: null,
      $textareas: null,
      $deleteBtn: null,
      conditionalTypes: null,
      init: function init(table, tr, conditionalTypes) {
        this.table = table;
        this.$tr = $(tr);
        this.$tds = this.$tr.children();
        this.conditionalTypes = conditionalTypes; // Get the row ID, sans prefix

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
              this.addListener($textarea, 'keypress', {
                type: col.type
              }, 'validateKeypress');
              this.addListener($textarea, 'textchange', {
                type: col.type
              }, 'validateValue');
            }

            textareasByColId[colId] = $textarea;
          }

          i++;
        }

        this.initSproutFields(); // Now that all of the text cells have been nice-ified, let's normalize the heights

        this.onTextareaHeightChange(); // Now look for any autopopulate columns

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
        $formFieldInput.change({
          row: this
        }, function(event) {
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
              var optionLabel = typeof option.label !== 'undefined' ? option.label : option,
                optionValue = typeof option.value !== 'undefined' ? option.value : key,
                optionDisabled = typeof option.disabled !== 'undefined' ? option.disabled : false;
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
        $conditionalInput.change({
          row: this
        }, function(event) {
          that.addValueInputHtml(that);
        });
        $conditionalInput.change({
          row: this
        }, function(event) {
          console.log(event.data.row.$tr.find("td:eq(0)").find("select").val());
        });

        if (needCheck == true) {
          this.addValueInputHtml();
        }

        var $deleteBtn = this.$tr.children().last().find('.delete');
        this.addListener($deleteBtn, 'click', 'deleteRow');
      },
      addValueInputHtml: function addValueInputHtml() {
        var self = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
        var that = self == null ? this : self;
        var data = {
          'formFieldHandle': this.$tr.find("td:eq(0)").find("select").val(),
          'condition': this.$tr.find("td:eq(1)").find("select").val(),
          'inputName': this.$tr.find("td:eq(2)").find("input").attr("name"),
          'inputValue': this.$tr.find("td:eq(2)").find("input").val(),
          'formId': $("#formId").val()
        };
        Craft.postActionRequest("sprout-forms/conditionals/get-condition-input-html", data, $.proxy(function(response, textStatus) {
          var statusSuccess = textStatus === 'success';

          if (statusSuccess && response.success) {
            that.$tr.find("td:eq(2)").html(response.html);
          } else {
            Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the input html'));
          }
        }, this));
      },
      initSproutFields: function initSproutFields() {
        Craft.SproutFields.initFields(this.$tr);
      },
      onTextareaFocus: function onTextareaFocus(ev) {
        this.onTextareaHeightChange();
        var $textarea = $(ev.currentTarget);

        if ($textarea.data('ignoreNextFocus')) {
          $textarea.data('ignoreNextFocus', false);
          return;
        }

        setTimeout(function() {
          var val = $textarea.val(); // Does the browser support setSelectionRange()?

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
      ignoreNextTextareaFocus: function ignoreNextTextareaFocus(ev) {
        $.data(ev.currentTarget, 'ignoreNextFocus', true);
      },
      validateKeypress: function validateKeypress(ev) {
        var keyCode = ev.keyCode ? ev.keyCode : ev.charCode;

        if (!Garnish.isCtrlKeyPressed(ev) && (keyCode === Garnish.RETURN_KEY || ev.data.type === 'number' && !Craft.inArray(keyCode, Craft.SproutForms.EditableTable.Row.numericKeyCodes))) {
          ev.preventDefault();
        }
      },
      validateValue: function validateValue(ev) {
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
      onTextareaHeightChange: function onTextareaHeightChange() {
        // Keep all the textareas' heights in sync
        var tallestTextareaHeight = -1;

        for (var i = 0; i < this.niceTexts.length; i++) {
          if (this.niceTexts[i].height > tallestTextareaHeight) {
            tallestTextareaHeight = this.niceTexts[i].height;
          }
        }

        this.$textareas.css('min-height', tallestTextareaHeight); // If the <td> is still taller, go with that insted

        var tdHeight = this.$textareas.first().parent().height();

        if (tdHeight > tallestTextareaHeight) {
          this.$textareas.css('min-height', tdHeight);
        }
      },
      deleteRow: function deleteRow() {
        this.table.sorter.removeItems(this.$tr);
        this.$tr.remove(); // onDeleteRow callback

        this.table.settings.onDeleteRow(this.$tr);
      }
    }, {
      numericKeyCodes: [
        9
        /* (tab) */
        , 8
        /* (delete) */
        , 37, 38, 39, 40
        /* (arrows) */
        , 45, 91
        /* (minus) */
        , 46, 190
        /* period */
        , 48, 49, 50, 51, 52, 53, 54, 55, 56, 57
        /* (0-9) */
      ]
    });

    /***/
  }),

  /***/ "./src/web/assets/forms/src/js/FieldLayoutEditor.js":
  /*!**********************************************************!*\
    !*** ./src/web/assets/forms/src/js/FieldLayoutEditor.js ***!
    \**********************************************************/
  /*! no static exports found */
  /***/ (function(module, exports, __webpack_require__) {

    function _typeof(obj) {
      if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
        _typeof = function _typeof(obj) {
          return typeof obj;
        };
      } else {
        _typeof = function _typeof(obj) {
          return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
        };
      }
      return _typeof(obj);
    }

    /*
     * @link      https://sprout.barrelstrengthdesign.com/
     * @copyright Copyright (c) Barrel Strength Design LLC
     * @license   http://sprout.barrelstrengthdesign.com/license
     */
    if (_typeof(Craft.SproutForms) === (true ? "undefined" : undefined)) {
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
        wheelHtml: null,
        continueEditing: null,
        // The Dragula instance
        drake: null,
        // The dragula instance for tabs
        drakeTabs: null,
        tabsLayout: null,
        $saveFormButton: null,

        /**
         * The constructor.
         * @param - All the tabs of the form fieldLayout.
         */
        init: function init(currentTabs, continueEditing) {
          var that = this;
          this.$saveFormButton = $("#save-form-button");
          this.continueEditing = continueEditing;
          this.initButtons();
          this.initTabSettings();
          this.wheelHtml = ' <span class="settings icon icon-wheel"></span>';
          this.modal = Craft.SproutForms.FieldModal.getInstance();
          this.modal.on('newField', $.proxy(function(e) {
            var field = e.field;
            var group = field.group;
            this.addField(field.id, field.name, group.name);
          }, this));
          this.modal.on('saveField', $.proxy(function(e) {
            var field = e.field;
            var group = field.group; // Let's update the name and the icon if the field is updated

            this.resetField(field, group);
          }, this)); // DRAGULA FOR TABS

          this.tabsLayout = this.getId('sprout-forms-tabs');
          this.drakeTabs = dragula([this.tabsLayout], {
            accepts: function accepts(el, target, source, sibling) {
              // let's try to not allows reorder the PLUS icon
              return sibling === null || $(el).is('.drag-tab');
            },
            invalid: function invalid(el, handle) {
              // do not move any item with donotdrag class.
              return el.classList.contains('donotdrag');
            }
          }).on('drag', function(el) {
            $(el).addClass('drag-tab-active');
          }).on('drop', function(el, target, source) {
            $(el).removeClass('drag-tab-active');
            $(target).find('.drag-tab-active').removeClass('drag-tab-active');
            $(source).find('.drag-tab-active').removeClass('drag-tab-active'); // Reorder fields

            if ($(target).attr("id") === $(source).attr("id")) {
              // lets update the hidden tab field to reorder the tabs
              $("#sprout-forms-tabs li.drag-tab a").each(function(i) {
                var tabId = $(this).attr('id');
                var mainDiv = $("#sproutforms-fieldlayout-container");

                if (tabId) {
                  var currentTab = $("#sproutforms-" + tabId);

                  if (currentTab) {
                    mainDiv.append(currentTab);
                  }
                }
              });
            }
          }); // Show the wheel on hover

          /*$('#sprout-forms-tabs').find('a').hover( function() {
              $(this).find('#icon-wheel').toggle();
          } );
          */
          // DRAGULA

          this.fieldsLayout = this.getId('right-copy'); // Drag from right to left

          this.drake = dragula([null, this.fieldsLayout], {
            copy: function copy(el, source) {
              return source === that.fieldsLayout;
            },
            accepts: function accepts(el, target) {
              return target !== that.fieldsLayout;
            },
            invalid: function invalid(el, handle) {
              // do not move any item with donotdrag class.
              return el.classList.contains('donotdrag');
            }
          }).on('drag', function(el) {
            $(el).addClass('drag-active');
          }).on('drop', function(el, target, source) {
            $(el).removeClass('drag-active');
            $(target).find('.drag-active').removeClass('drag-active');
            $(source).find('.drag-active').removeClass('drag-active'); // Reorder fields

            if ($(target).attr("id") === $(source).attr("id")) {// just if we need check when the field is reorder
              // not needed because the order is saved from the hidden field
              // when the form is saved
            }

            if (target && source === that.fieldsLayout) {
              // get the tab name by the first div fields
              var tab = $(el).closest(".sproutforms-tab-fields");
              var tabName = tab.data('tabname');
              var tabId = tab.data('tabid');
              var fieldType = $(el).data("type");
              that.createDefaultField(fieldType, tabId, tabName, el);
            }
          }).on('over', function(el, container) {
            $(el).addClass('drag-active');
            $(container).addClass('container-active');
          }).on('out', function(el, container) {
            $(el).removeClass('drag-active');
            $(container).removeClass('container-active');
          }); // Adds auto-scroll to main container when dragging

          var scroll = autoScroll([document.querySelector('#content-container')], {
            margin: 20,
            maxSpeed: 10,
            scrollWhenOutside: true,
            autoScroll: function autoScroll() {
              //Only scroll when the pointer is down, and there is a child being dragged.
              return this.down && that.drake.dragging;
            }
          }); // Adds auto-scroll to main container when dragging

          var tabScroll = autoScroll([document.querySelector('#sprout-forms-tabs')], {
            margin: 20,
            maxSpeed: 10,
            scrollWhenOutside: true,
            autoScroll: function autoScroll() {
              //Only scroll when the pointer is down, and there is a child being dragged.
              return this.down && that.drakeTabs.dragging;
            }
          }); // Add the drop containers for each tab

          for (var i = 0; i < currentTabs.length; i++) {
            this.drake.containers.push(this.getId('sproutforms-tab-container-' + currentTabs[i].id));
          } // Prevent save with Ctrl+S when the the field is dropped

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
        clickHandler: function clickHandler(e) {
          var target = e.target;

          if (target === this.tabsLayout) {
            return;
          }

          target.innerHTML += ' [click!]';
          setTimeout(function() {
            target.innerHTML = target.innerHTML.replace(/ \[click!]/g, '');
          }, 500);
        },
        createDefaultField: function createDefaultField(type, tabId, tabName, el) {
          $(el).removeClass('source-field');
          $(el).addClass('target-field');
          $(el).find('.source-field-header').remove();
          $(el).find('.body').removeClass('hidden'); // try to check position of the field

          var nextDiv = $(el).next(".target-field");
          var nextId = nextDiv.attr('id');

          if (typeof nextId === 'undefined' || nextId === null) {
            nextDiv = null;
          } else {
            // Last field
            var nextDivId = nextId.split('-');
            nextId = nextDivId[1];
          }

          var defaultName = $(el).data('defaultname') ? $(el).data('defaultname') : Craft.t('sprout-forms', 'Untitled'); // Add the Field Header

          $(el).prepend($(['<div class="active-field-header">', '<h2>', defaultName, '</h2>', '</div>'].join('')));
          var formId = $("#formId").val();
          var data = {
            'type': type,
            'formId': formId,
            'tabId': tabId,
            'nextId': nextId
          };
          Craft.postActionRequest('sprout-forms/fields/create-field', data, $.proxy(function(response, textStatus) {
            if (textStatus === 'success') {
              this.initFieldOnDrop(response.field, tabName, el); //that.$saveFormButton.removeClass('disabled').siblings('.spinner').addClass('hidden');
            }
          }, this));
        },
        initFieldOnDrop: function initFieldOnDrop(defaultField, tabName, el) {
          if (defaultField !== null && defaultField.hasOwnProperty("id")) {
            $(el).attr('id', 'sproutfield-' + defaultField.id); // Add the Settings buttons

            $(el).prepend($(['<ul class="settings">', '<li><a id="sproutform-field-', defaultField.id, '" data-fieldid="', defaultField.id, '" href="#" tabindex="0" ><i class="fa fa-pencil fa-2x" title="', Craft.t('sprout-forms', 'Edit'), '"></i></a></li>', '</ul>'].join(''))); // Add fieldLayout input

            $(el).append($(['<input type="hidden" name="fieldLayout[', tabName, '][]" value="', defaultField.id, '" class="id-input">'].join('')));
            this.addListener($("#sproutform-field-" + defaultField.id), 'activate', 'editField');
          } else {
            Craft.cp.displayError(Craft.t('sprout-forms', 'Something went wrong when creating the field :('));
            $(el).remove();
          }
        },
        getId: function getId(id) {
          return document.getElementById(id);
        },

        /**
         * Adds edit buttons to existing fields.
         */
        initTabSettings: function initTabSettings() {
          var that = this;
          $("#sprout-forms-tabs li").each(function(i, el) {
            // #delete-tab-"+tab.id
            var tabId = $(el).find('a').attr('id');
            var $editBtn = $(el).find('.settings');
            that.initializeWheel($editBtn, tabId);
          });
        },
        initializeWheel: function initializeWheel($editBtn, tabId) {
          var that = this;
          var $menu = $('<div class="menu" data-align="center"/>').insertAfter($editBtn),
            $ul = $('<ul/>').appendTo($menu);
          $('<li><a data-action="add" data-tab-id="' + tabId + '">' + Craft.t('sprout-forms', 'Add Tab') + '</a></li>').appendTo($ul);
          $('<li><a data-action="rename" data-tab-id="' + tabId + '">' + Craft.t('sprout-forms', 'Rename') + '</a></li>').appendTo($ul);
          $('<li><a id ="#delete-' + tabId + '" data-action="delete" data-tab-id="' + tabId + '">' + Craft.t('sprout-forms', 'Delete') + '</a></li>').appendTo($ul);
          new Garnish.MenuBtn($editBtn, {
            onOptionSelect: $.proxy(that, 'onTabOptionSelect')
          });
        },
        onTabOptionSelect: function onTabOptionSelect(option) {
          var $option = $(option),
            tabId = $option.data('tab-id'),
            action = $option.data('action');

          switch (action) {
            case 'add': {
              this.addNewTab();
              break;
            }

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
        initButtons: function initButtons() {
          var that = this; // Add listeners to all the items that start with sproutform-field-

          $("a[id^='sproutform-field-']").each(function(i, el) {
            var fieldId = $(el).data('fieldid');

            if (fieldId) {
              that.addListener($("#sproutform-field-" + fieldId), 'activate', 'editField');
            }
          }); // get all the delete buttons

          $("a[id^='delete-tab-']").each(function(i, el) {
            var tabId = $(el).data('tabid');

            if (tabId) {
              that.addListener($("#delete-tab-" + tabId), 'activate', 'deleteTab');
            }
          });
        },
        deleteTab: function deleteTab(tabId) {
          var userResponse = this.confirmDeleteTab();

          if (userResponse) {
            var data = {
              tabId: tabId
            };
            Craft.postActionRequest('sprout-forms/fields/delete-tab', data, $.proxy(function(response, textStatus) {
              if (response.success) {
                Craft.cp.displayNotice(Craft.t('sprout-forms', 'Tab Deleted'));
                $("#sproutforms-" + tabId).slideUp(500, function() {
                  $(this).remove();
                });
                $("#" + tabId).closest("li").slideUp(500, function() {
                  $(this).remove();
                });
              } else {
                Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to delete the tab'));
              }
            }, this));
          }
        },
        renameTab: function renameTab(tabId) {
          var $labelSpan = $('#' + tabId + ' .tab-label'),
            oldName = $labelSpan.text().trim(),
            newName = prompt(Craft.t('sprout-forms', 'Give your tab a name.'), oldName);
          var response = true;
          var $tabs = $(".drag-tab");
          var formId = $("#formId").val();
          var that = this;

          if (newName && newName !== oldName) {
            // validate with current names and set the sortOrder
            $tabs.each(function(i, el) {
              var tabname = $(el).find('.tab-label').text();

              if (tabname === newName) {
                response = false;
                return false;
              }
            });

            if (response && newName && formId) {
              var data = {
                name: newName,
                oldName: oldName,
                formId: formId
              };
              Craft.postActionRequest('sprout-forms/fields/rename-tab', data, $.proxy(function(response, textStatus) {
                if (response.success) {
                  Craft.cp.displayNotice(Craft.t('sprout-forms', 'Tab renamed')); // Rename all the field names

                  var $fields = $("[name^='fieldLayout[" + oldName + "]']");
                  $fields.each(function(i, el) {
                    var fieldName = $(el).attr('name');
                    var newFieldName = fieldName.replace(oldName, newName);
                    $(el).attr('name', newFieldName);
                    $labelSpan.text(newName);
                  });
                  $("#sproutforms-" + tabId).attr('data-tabname', newName);
                } else {
                  Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to rename tab'));
                }
              }, this));
            } else {
              Craft.cp.displayError(Craft.t('sprout-forms', 'Invalid tab name'));
            }
          }
        },
        addNewTab: function addNewTab() {
          var newName = this.promptForGroupName('');
          var response = true;
          var $tabs = $(".drag-tab");
          var formId = $("#formId").val();
          var that = this; // validate with current names and set the sortOrder

          $tabs.each(function(i, el) {
            var tabname = $(el).find('.tab-label').text();

            if (tabname) {
              if (tabname === newName) {
                response = false;
                return false;
              }
            }
          });

          if (response && newName && formId) {
            var data = {
              name: newName,
              // Minus the add tab button
              sortOrder: $tabs.length,
              formId: formId
            };
            Craft.postActionRequest('sprout-forms/fields/add-tab', data, $.proxy(function(response, textStatus) {
              if (response.success) {
                var tab = response.tab;
                Craft.cp.displayNotice(Craft.t('sprout-forms', 'Tab: ' + tab.name + ' created')); // Insert the new tab before the Add Tab button

                var href = '#sproutforms-tab-' + tab.id;
                $("#sprout-forms-tabs").append('<li class="drag-tab"><a id="tab-' + tab.id + '" class="tab" href="' + href + '" tabindex="0"><span class="tab-label">' + tab.name + '</span>&nbsp;' + this.wheelHtml + '</a></li>');
                var $editBtn = $("#tab-" + tab.id).find('.settings'); // add listener to the wheel

                that.initializeWheel($editBtn, 'tab-' + tab.id); // Create the area to Drag/Drop fields on the new tab

                $("#sproutforms-fieldlayout-container").append($(['<div id="sproutforms-tab-' + tab.id + '" data-tabname="' + tab.name + '" data-tabid="' + tab.id + '" class="sproutforms-tab-fields hidden">', '<div class="parent">', '<div id="sproutforms-tab-container-' + tab.id + '" class="sprout-tab-container">', '</div>', '</div>', '</div>'].join(''))); // Convert our new tab into Dragula vampire :)

                this.drake.containers.push(this.getId('sproutforms-tab-container-' + tab.id)); // Reinitialize tabs

                Craft.cp.initTabs();
              } else {
                Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to create a new tab'));
              }
            }, this));
          } else {
            Craft.cp.displayError(Craft.t('sprout-forms', 'Invalid tab name'));
          }
        },
        promptForGroupName: function promptForGroupName(oldName) {
          return prompt(Craft.t('sprout-forms', 'What do you want to name your new tab?'), oldName);
        },
        confirmDeleteTab: function confirmDeleteTab() {
          return confirm("Are you sure you want to delete this tab, all of it's fields, and all of it's data?");
        },

        /**
         * Event handler for the New Field button.
         * Creates a modal window that contains new field settings.
         */
        newField: function newField() {
          this.modal.show();
        },
        editField: function editField(option) {
          var option = option.currentTarget;
          var fieldId = $(option).data('fieldid'); // Make our field available to our parent function

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
        resetField: function resetField(field, group) {
          var $fieldDiv = $("#sproutfield-" + field.id); // Lets update the the name and icon - (new) update if required

          $($fieldDiv).find('.body').html(field.htmlExample);
          var $requiredDiv = $($fieldDiv).find("[name='requiredFields[]']");

          if (field.required) {
            $($fieldDiv).find('.active-field-header h2').addClass('required'); // Update or create our hidden required div

            if (!$requiredDiv.length) {
              $('<input type="hidden" name="requiredFields[]" value="' + field.id + '" class="sproutforms-required-input">').appendTo($fieldDiv);
            } else {
              $($requiredDiv).val(field.id);
            }
          } else {
            $($fieldDiv).find('.active-field-header h2').removeClass('required'); // Update our hidden required div

            $($requiredDiv).val('');
          }

          $($fieldDiv).find('.active-field-header h2').html(field.name);
          $($fieldDiv).find('.active-field-header p').html(field.instructions); // Check if we need move the field to another tab

          var tab = $($fieldDiv).closest(".sproutforms-tab-fields");
          var tabName = tab.data('tabname');
          var tabId = tab.data('tabid');

          if (tabName !== group.name) {
            // let's remove the hidden field just if the user change the tab
            $($fieldDiv).find('.id-input').remove(); // create the new hidden field and add it to the field div

            var $field = $(['<input class="id-input" type="hidden" name="fieldLayout[', group.name, '][]" value="', field.id, '">'].join('')).appendTo($($fieldDiv)); // move the field to another tab

            var newTab = $("#sproutforms-tab-container-" + group.id); // move element to new div - like ctrl+x

            $($fieldDiv).detach().appendTo($(newTab));
          }
        }
      });
    })(jQuery);

    /***/
  }),

  /***/ "./src/web/assets/forms/src/js/FieldModal.js":
  /*!***************************************************!*\
    !*** ./src/web/assets/forms/src/js/FieldModal.js ***!
    \***************************************************/
  /*! no static exports found */
  /***/ (function(module, exports, __webpack_require__) {

    function _typeof(obj) {
      if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
        _typeof = function _typeof(obj) {
          return typeof obj;
        };
      } else {
        _typeof = function _typeof(obj) {
          return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
        };
      }
      return _typeof(obj);
    }

    /*
     * @link      https://sprout.barrelstrengthdesign.com/
     * @copyright Copyright (c) Barrel Strength Design LLC
     * @license   http://sprout.barrelstrengthdesign.com/license
     */
    if (_typeof(Craft.SproutForms) === (true ? "undefined" : undefined)) {
      Craft.SproutForms = {};
    }

    (function($) {
      var MutationObserver = window.MutationObserver || window.WebKitMutationObserver; // If mutation observer is not supported, create a harness for it for graceful degradation.
      // Older browsers could be supported through the DOMNodeInserted event, but that can be saved for another day...

      if (!MutationObserver) {
        MutationObserver = function MutationObserver() {
        };

        MutationObserver.prototype.observe = function() {
        };

        MutationObserver.prototype.disconnect = function() {
        };
      }
      /**
       * SproutForms.FieldModal class
       * Handles the modal window for creating new fields.
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
        init: function init(settings) {
          this.base();
          this.setSettings(settings, {
            resizable: true
          });
          this.$currentHtml = $();
          this.$currentJs = $();
          this.$currentCss = $();
          this.$observed = $();
          this.executedJs = {};
          this.loadedCss = {}; // Observe the DOM

          this.observer = new MutationObserver($.proxy(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
              this.$observed = this.$observed.add(mutations[i].addedNodes);
            }
          }, this));
          var $container = $('<form class="modal sprout-field-modal" style="display: none; opacity: 0;">').appendTo(Garnish.$bod);
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
          var formId = $("#formId").val();
          var postData = {
            formId: formId
          }; // Loads the field settings template file, as well as all the resources that come with it

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
        initTemplate: function initTemplate(template) {
          var callback = $.proxy(function(e) {
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
        parseTemplate: function parseTemplate(template) {
          var that = this;
          var $head = Garnish.$doc.find('head');
          var $html = $(template.html);
          var $js = $(template.js).filter('script');
          var $css = $(template.css).filter('style, link'); // Ensure that external stylesheets are loaded asynchronously

          var $cssFiles = $css.filter('link').prop('async', true);
          var $cssInline = $css.filter('style');
          $cssFiles.each(function() {
            var $this = $(this);
            var src = $this.prop('href');

            if (!that.loadedCss.hasOwnProperty(src)) {
              $head.append($this);
              that.loadedCss[src] = $this;
            }
          }); // Load external Javascript files asynchronously, and remove them from being executed again.
          // This assumes that external Javascript files are simply library files, that don't directly and
          // instantly execute code that modifies the DOM. Library files can be loaded and executed once and
          // reused later on.
          // The Javascript tags that directly contain code are assumed to be context-dependent, so they are
          // saved to be executed each time the modal is opened.

          var $jsFiles = $js.filter('[src]');
          var $jsInline = $js.filter(':not([src])');
          var jsFiles = [];
          $jsFiles.each(function() {
            var $this = $(this);
            var src = $this.prop('src');

            if (!that.executedJs.hasOwnProperty(src)) {
              jsFiles.push(src);
              that.executedJs[src] = true;
            }
          });

          var callback = function callback() {
            that.off('runExternalScripts', callback);
            that.trigger('parseTemplate', {
              target: this,
              $html: $html,
              $js: $jsInline,
              $css: $cssInline
            });
          }; // Fixes bug on Craft3 - Updates way to callback function


          $.when(this.runExternalScripts(jsFiles)).then(callback()); //this.runExternalScripts(jsFiles);

          this.$deleteBtn.removeClass('hidden');
          this.$saveBtn.removeClass('disabled');
          this.$cancelBtn.removeClass('disabled');
        },

        /**
         * Runs external Javascript files
         *
         * @param files - An array of URL's (as strings) to Javascript files
         */
        runExternalScripts: function runExternalScripts(files) {
          var filesCount = files.length;

          if (filesCount > 0) {
            for (var i = 0; i < files.length; i++) {
              var src = files[i]; // Fixes Double-instantiating bug

              if (src.indexOf('MatrixConfigurator') >= 0 || src.indexOf('TableFieldSettings.min.js') >= 0 || src.indexOf('quill.min.js') >= 0 || src.indexOf('sproutfields.js') >= 0 || src.indexOf('EditableTable.js') >= 0 || src.indexOf('initialize.js') >= 0) {
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
        initListeners: function initListeners() {
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
        destroyListeners: function destroyListeners() {
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
        initSettings: function initSettings(e) {
          var that = e && e.target ? e.target : this; // If the template files are not loaded yet, just cancel initialisation of the settings.

          if (!that.templateLoaded) {
            return;
          }

          that.$currentHtml = e && e.$html ? e.$html : that.$html.clone();
          that.$currentJs = e && e.$js ? e.$js : that.$js.clone();
          that.$currentCss = e && e.$css ? e.$css : that.$css.clone(); // Save any new nodes that are added to the body during initialisation, so they can be safely removed later.

          that.$observed = $();
          that.observer.observe(Garnish.$bod[0], {
            childList: true,
            subtree: false
          });
          that.$main.append(that.$currentHtml);
          Garnish.$bod.append(that.$currentJs); // Only show the delete button if editing a field

          var $fieldId = that.$main.find('input[name="fieldId"]');
          Craft.initUiElements(); // Rerun the external scripts as some field types may need to make DOM changes in their external files.
          // This means that libraries are being initialized multiple times, but hopefully they're smart enough to
          // deal with that. So far, no issues.

          var callback = function callback() {
            that.off('runExternalScripts', callback); // Stop observing after a healthy timeout to ensure all mutations are captured.

            setTimeout(function() {
              that.observer.disconnect();
            }, 1);
          };

          $.when(that.runExternalScripts(Object.keys(that.executedJs))).then(callback()); //that.on('runExternalScripts', callback);
          //that.runExternalScripts(Object.keys(that.executedJs));
        },

        /**
         * Event handler for when the modal window finishes fading out after hiding.
         * Clears out all events and elements of the modal.
         */
        destroySettings: function destroySettings(e) {
          var that = e && e.target ? e.target : this;
          that.$currentHtml.remove();
          that.$currentJs.remove();
          that.$currentCss.remove();
          that.$observed.remove();
        },

        /**
         * Event handler for the Close button.
         * Hides the modal window from view.
         */
        closeModal: function closeModal() {
          this.hide();
        },

        /**
         * Event handler for the save button.
         * Saves the new field form to the database.
         *
         * @param e
         */
        saveField: function saveField(e) {
          if (e) {
            e.preventDefault();
          }

          if (this.$saveBtn.hasClass('disabled') || !this.$saveSpinner.hasClass('hidden')) {
            return;
          }

          this.destroyListeners();
          this.$saveSpinner.removeClass('hidden');
          var data = this.$container.serialize();
          var inputId = this.$container.find('input[name="fieldId"]');
          var id = inputId.length ? inputId.val() : false;
          Craft.postActionRequest('sprout-forms/fields/save-field', data, $.proxy(function(response, textStatus) {
            this.$saveSpinner.addClass('hidden');
            var statusSuccess = textStatus === 'success';

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
                Craft.cp.displayNotice(Craft.t('sprout-forms', '\'{name}\' field saved.', {
                  name: response.field.name
                }));
              }

              this.hide();
            } else if (statusSuccess && response.template) {
              if (this.visible) {
                var callback = $.proxy(function(e) {
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
        editField: function editField(id) {
          this.destroyListeners();
          this.show();
          this.initListeners();
          this.$loadSpinner.removeClass('hidden');
          var formId = $("#formId").val();
          var data = {
            'fieldId': id,
            'formId': formId
          };
          Craft.postActionRequest('sprout-forms/fields/edit-field', data, $.proxy(function(response, textStatus) {
            this.$loadSpinner.addClass('hidden');
            var statusSuccess = textStatus === 'success';

            if (statusSuccess && response.success) {
              var callback = $.proxy(function(e) {
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
        deleteField: function deleteField(e) {
          e.preventDefault();
          var userResponse = this.confirmDeleteField();

          if (userResponse) {
            this.destroyListeners();
            var data = this.$container.serialize();
            var fieldId = $(this.$container).find('input[name="fieldId"]').val();
            Craft.postActionRequest('sprout-forms/fields/delete-field', data, $.proxy(function(response, textStatus) {
              var statusSuccess = textStatus === 'success';

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
        confirmDeleteField: function confirmDeleteField() {
          return confirm("Are you sure you want to delete this field and all of it's data?");
        },

        /**
         * Prevents the modal from closing if it's disabled.
         * This fixes issues if the modal is closed when saving/deleting fields.
         */
        hide: function hide() {
          if (!this._disabled) {
            this.base();
          }
        },

        /**
         * Removes everything to do with the modal form the DOM.
         */
        destroy: function destroy() {
          this.base.destroy();
          this.destroyListeners();
          this.destroySettings();
          this.$shade.remove();
          this.$container.remove();
          this.trigger('destroy');
        }
      }, {
        /**
         * (Static) Singleton pattern.
         *
         * @returns FieldModal
         */
        getInstance: function getInstance() {
          if (!this._instance) {
            this._instance = new Craft.SproutForms.FieldModal();
          }

          return this._instance;
        }
      });
    })(jQuery);

    /***/
  }),

  /***/ "./src/web/assets/forms/src/js/FormSettings.js":
  /*!*****************************************************!*\
    !*** ./src/web/assets/forms/src/js/FormSettings.js ***!
    \*****************************************************/
  /*! no static exports found */
  /***/ (function(module, exports, __webpack_require__) {

    function _typeof(obj) {
      if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
        _typeof = function _typeof(obj) {
          return typeof obj;
        };
      } else {
        _typeof = function _typeof(obj) {
          return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
        };
      }
      return _typeof(obj);
    }

    /* global Craft */
    if (_typeof(Craft.SproutForms) === (true ? "undefined" : undefined)) {
      Craft.SproutForms = {};
    }

    (function($) {
      Craft.SproutForms.FormSettings = Garnish.Base.extend({
        options: null,
        modal: null,
        conditionalModal: null,
        $lightswitches: null,

        /**
         * The constructor.
         */
        init: function init() {
          // init method
          this.initButtons();
        },

        /**
         * Adds edit buttons to existing integrations.
         */
        initButtons: function initButtons() {
          var that = this; // Add listeners to all the items that start with sproutform-field-

          $("a[id^='sproutform-integration']").each(function(i, el) {
            var integrationId = $(el).data('integrationid');

            if (integrationId) {
              that.addListener($("#sproutform-integration-" + integrationId), 'activate', 'editIntegration');
            }
          }); // Add listeners to all conditionals

          $("a[id^='sproutform-conditional']").each(function(i, el) {
            var conditionalId = $(el).data('conditionalid');

            if (conditionalId) {
              that.addListener($("#sproutform-conditional-" + conditionalId), 'activate', 'editConditional');
            }
          });
          this.$lightswitches = $('.sproutforms-integration-row .lightswitch');
          this.addListener(this.$lightswitches, 'click', 'onChange');
          this.modal = Craft.SproutForms.IntegrationModal.getInstance();
          this.modal.on('saveIntegration', $.proxy(function(e) {
            var integration = e.integration; // Let's update the name if the integration is updated

            this.resetIntegration(integration);
          }, this));
          this.conditionalModal = Craft.SproutForms.ConditionalModal.getInstance();
          this.conditionalModal.on('saveConditional', $.proxy(function(e) {
            var conditional = e.conditional; // Let's update the name if the conditional is updated

            this.resetConditional(conditional);
          }, this));
          this.addListener($("#integrationsOptions"), 'change', 'createDefaultIntegration');
          this.addListener($("#conditionalOptions"), 'change', 'createDefaultConditional');
        },
        onChange: function onChange(ev) {
          var lightswitch = ev.currentTarget;
          var integrationId = lightswitch.id;
          var enabled = $(lightswitch).attr('aria-checked');
          enabled = enabled === 'true' ? 1 : 0;
          var formId = $("#formId").val();
          var data = {
            integrationId: integrationId,
            enabled: enabled,
            formId: formId
          };
          Craft.postActionRequest('sprout-forms/integrations/enable-integration', data, $.proxy(function(response, textStatus) {
            if (textStatus === 'success' && response.success) {
              Craft.cp.displayNotice(Craft.t('sprout-forms', "Integration updated."));
            } else {
              Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to update integration'));
            }
          }, this));
        },

        /**
         * Renames | update icon |
         * of an existing integration after edit it
         *
         * @param integration
         */
        resetIntegration: function resetIntegration(integration) {
          var $integrationDiv = $("#sproutform-integration-" + integration.id);
          var $container = $("#integration-enabled-" + integration.id);
          var currentValue = integration.enabled === '1' ? true : false;
          var settingsValue = $container.attr('aria-checked') === 'true' ? true : false;

          if (currentValue !== settingsValue) {
            $container.attr('aria-checked', "" + currentValue);

            if (currentValue) {
              $container.addClass("on");
            } else {
              $container.removeClass("on");
            }
          }

          $integrationDiv.html(integration.name);
        },

        /**
         * Renames | update icon |
         * of an existing conditional after edit it
         *
         * @param conditional
         */
        resetConditional: function resetConditional(conditional) {
          var $conditionalDiv = $("#sproutform-conditional-" + conditional.id);
          var $container = $("#conditional-enabled-" + conditional.id);
          var currentValue = conditional.enabled === '1' ? true : false;
          var settingsValue = $container.attr('aria-checked') === 'true' ? true : false;

          if (currentValue !== settingsValue) {
            $container.attr('aria-checked', "" + currentValue);

            if (currentValue) {
              $container.addClass("on");
            } else {
              $container.removeClass("on");
            }
          }

          $conditionalDiv.html(conditional.name);
        },
        createDefaultIntegration: function createDefaultIntegration(type) {
          var that = this;
          var integrationTableBody = $('#sproutforms-integrations-table tbody');
          var currentIntegration = $("#integrationsOptions").val();
          var formId = $("#formId").val();

          if (currentIntegration === '') {
            return;
          }

          var data = {
            type: currentIntegration,
            formId: formId,
            sendRule: '*'
          };
          Craft.postActionRequest('sprout-forms/integrations/save-integration', data, $.proxy(function(response, textStatus) {
            if (textStatus === 'success') {
              var integration = response.integration;
              integrationTableBody.append('<tr class="field sproutforms-integration-row" id ="sproutforms-integration-row-' + integration.id + '">' + '<td class="heading">' + '<a href="#" id ="sproutform-integration-' + integration.id + '" data-integrationid="' + integration.id + '">' + integration.name + '</a>' + '</td>' + '<td>' + '<div class="lightswitch small" tabindex="0" data-value="1" role="checkbox" aria-checked="false" id ="integration-enabled-' + integration.id + '">' + '<div class="lightswitch-container">' + '<div class="label on"></div>' + '<div class="handle"></div>' + '<div class="label off"></div>' + '</div>' + '<input type="hidden" name="" value="">' + '</div>' + '</td>' + '</tr>');
              that.addListener($("#sproutform-integration-" + integration.id), 'activate', 'editIntegration');
              $('#integrationsOptions').val('');
              var $container = $("#integration-enabled-" + integration.id);
              $container.lightswitch();
              that.addListener($container, 'click', 'onChange');
            } else {// something went wrong
            }
          }, this));
        },
        createDefaultConditional: function createDefaultConditional(type) {
          var that = this;
          var conditionalTableBody = $("#sproutforms-conditionalrules-table tbody");
          var currentConditional = $("#conditionalOptions").val();
          var formId = $("#formId").val();

          if (currentConditional === '') {
            return;
          }

          var data = {
            type: currentConditional,
            formId: formId
          };
          Craft.postActionRequest('sprout-forms/conditionals/save-conditional', data, $.proxy(function(response, textStatus) {
            if (textStatus === 'success') {
              var conditional = response.conditional;
              conditionalTableBody.append('<tr id ="sproutforms-conditional-row-' + conditional.id + '" class="field sproutforms-conditional-row">' + '<td>' + '<a href="#" id ="sproutform-conditional-' + conditional.id + '" data-conditionalid="' + conditional.id + '">' + conditional.name + '</a>' + '</td>' + '<td>' + '<div class="lightswitch small" tabindex="0" data-value="1" role="checkbox" aria-checked="false" id ="conditional-enabled-' + conditional.id + '">' + '<div class="lightswitch-container">' + '<div class="label on"></div>' + '<div class="handle"></div>' + '<div class="label off"></div>' + '</div>' + '<input type="hidden" name="" value="">' + '</div>' + '</td>' + '</tr>');
              that.addListener($("#sproutform-conditional-" + conditional.id), 'activate', 'editConditional');
              $('#conditionalOptions').val('');
              var $container = $("#conditional-enabled-" + conditional.id);
              $container.lightswitch();
              that.addListener($container, 'click', 'onChange');
            } else {// something went wrong
            }
          }, this));
        },
        editConditional: function editConditional(option) {
          var option = option.currentTarget;
          var conditionalId = $(option).data('conditionalid');
          console.log(conditionalId); // Make our field available to our parent function
          //this.$field = $(option);

          this.base($(option));
          this.conditionalModal.editConditional(conditionalId);
        },
        editIntegration: function editIntegration(option) {
          var option = option.currentTarget;
          var integrationId = $(option).data('integrationid'); // Make our field available to our parent function
          //this.$field = $(option);

          this.base($(option));
          this.modal.editIntegration(integrationId);
        }
      });
    })(jQuery);

    /***/
  }),

  /***/ "./src/web/assets/forms/src/js/IntegrationModal.js":
  /*!*********************************************************!*\
    !*** ./src/web/assets/forms/src/js/IntegrationModal.js ***!
    \*********************************************************/
  /*! no static exports found */
  /***/ (function(module, exports, __webpack_require__) {

    function _typeof(obj) {
      if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
        _typeof = function _typeof(obj) {
          return typeof obj;
        };
      } else {
        _typeof = function _typeof(obj) {
          return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
        };
      }
      return _typeof(obj);
    }

    /*
     * @link      https://sprout.barrelstrengthdesign.com/
     * @copyright Copyright (c) Barrel Strength Design LLC
     * @license   http://sprout.barrelstrengthdesign.com/license
     */
    if (_typeof(Craft.SproutForms) === (true ? "undefined" : undefined)) {
      Craft.SproutForms = {};
    }

    (function($) {
      var MutationObserver = window.MutationObserver || window.WebKitMutationObserver; // If mutation observer is not supported, create a harness for it for graceful degradation.
      // Older browsers could be supported through the DOMNodeInserted event, but that can be saved for another day...

      if (!MutationObserver) {
        MutationObserver = function MutationObserver() {
        };

        MutationObserver.prototype.observe = function() {
        };

        MutationObserver.prototype.disconnect = function() {
        };
      }
      /**
       * SproutForms.IntegrationModal class
       * Handles the modal window for editing integrations.
       */


      Craft.SproutForms.IntegrationModal = Garnish.Modal.extend({
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
        init: function init(settings) {
          this.base();
          this.setSettings(settings, {
            resizable: true
          });
          this.$currentHtml = $();
          this.$currentJs = $();
          this.$currentCss = $();
          this.$observed = $();
          this.executedJs = {};
          this.loadedCss = {}; // Observe the DOM

          this.observer = new MutationObserver($.proxy(function(mutations) {
            for (var i = 0; i < mutations.length; i++) {
              this.$observed = this.$observed.add(mutations[i].addedNodes);
            }
          }, this));
          var $container = $('<form class="modal sprout-field-modal" style="display: none; opacity: 0;">').appendTo(Garnish.$bod);
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
          this.$loadSpinner.addClass('hidden');
          var response = {
            html: '',
            js: '',
            css: ''
          };
          this.initTemplate(response);
        },

        /**
         * Prepares the Integration settings template HTML, CSS and Javascript.
         *
         * @param template
         */
        initTemplate: function initTemplate(template) {
          var callback = $.proxy(function(e) {
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
        parseTemplate: function parseTemplate(template) {
          var that = this;
          var $head = Garnish.$doc.find('head');
          var $html = $(template.html);
          var $js = $(template.js).filter('script');
          var $css = $(template.css).filter('style, link'); // Ensure that external stylesheets are loaded asynchronously

          var $cssFiles = $css.filter('link').prop('async', true);
          var $cssInline = $css.filter('style');
          $cssFiles.each(function() {
            var $this = $(this);
            var src = $this.prop('href');

            if (!that.loadedCss.hasOwnProperty(src)) {
              $head.append($this);
              that.loadedCss[src] = $this;
            }
          }); // Load external Javascript files asynchronously, and remove them from being executed again.
          // This assumes that external Javascript files are simply library files, that don't directly and
          // instantly execute code that modifies the DOM. Library files can be loaded and executed once and
          // reused later on.
          // The Javascript tags that directly contain code are assumed to be context-dependent, so they are
          // saved to be executed each time the modal is opened.

          var $jsFiles = $js.filter('[src]');
          var $jsInline = $js.filter(':not([src])');
          var jsFiles = [];
          $jsFiles.each(function() {
            var $this = $(this);
            var src = $this.prop('src');

            if (!that.executedJs.hasOwnProperty(src)) {
              jsFiles.push(src);
              that.executedJs[src] = true;
            }
          });

          var callback = function callback() {
            that.off('runExternalScripts', callback);
            that.trigger('parseTemplate', {
              target: this,
              $html: $html,
              $js: $jsInline,
              $css: $cssInline
            });
          }; // Fixes bug on Craft3 - Updates way to callback function


          $.when(this.runExternalScripts(jsFiles)).then(callback()); //this.runExternalScripts(jsFiles);

          this.$deleteBtn.removeClass('hidden');
          this.$saveBtn.removeClass('disabled');
          this.$cancelBtn.removeClass('disabled');
        },

        /**
         * Runs external Javascript files
         *
         * @param files - An array of URL's (as strings) to Javascript files
         */
        runExternalScripts: function runExternalScripts(files) {
          var filesCount = files.length;

          if (filesCount > 0) {
            for (var i = 0; i < files.length; i++) {
              var src = files[i]; // Fixes Double-instantiating bug

              if (src.indexOf('MatrixConfigurator') >= 0 || src.indexOf('TableFieldSettings.min.js') >= 0 || src.indexOf('quill.min.js') >= 0 || src.indexOf('sproutfields.js') >= 0 || src.indexOf('EditableTable.js') >= 0 || src.indexOf('initialize.js') >= 0) {
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
         * Binds all listeners so the quick integration buttons can start working.
         */
        initListeners: function initListeners() {
          this.$deleteBtn.addClass('hidden');
          this.$cancelBtn.addClass('disabled');
          this.$saveBtn.addClass('disabled');
          this.addListener(this.$cancelBtn, 'activate', 'closeModal');
          this.addListener(this.$saveBtn, 'activate', 'saveIntegration');

          if (!this.addedDelete) {
            this.addListener(this.$deleteBtn, 'click', 'deleteIntegration');
            this.addedDelete = true;
          }

          this.on('show', this.initSettings);
          this.on('fadeOut', this.destroySettings);
          this.enable();
        },

        /**
         * Unbinds all listeners.
         */
        destroyListeners: function destroyListeners() {
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
        initSettings: function initSettings(e) {
          var that = e && e.target ? e.target : this; // If the template files are not loaded yet, just cancel initialisation of the settings.

          if (!that.templateLoaded) {
            return;
          }

          that.$currentHtml = e && e.$html ? e.$html : that.$html.clone();
          that.$currentJs = e && e.$js ? e.$js : that.$js.clone();
          that.$currentCss = e && e.$css ? e.$css : that.$css.clone(); // Save any new nodes that are added to the body during initialisation, so they can be safely removed later.

          that.$observed = $();
          that.observer.observe(Garnish.$bod[0], {
            childList: true,
            subtree: false
          });
          that.$main.append(that.$currentHtml);
          Garnish.$bod.append(that.$currentJs);
          Craft.initUiElements(); // Rerun the external scripts as some field types may need to make DOM changes in their external files.
          // This means that libraries are being initialized multiple times, but hopefully they're smart enough to
          // deal with that. So far, no issues.

          var callback = function callback() {
            that.off('runExternalScripts', callback); // Stop observing after a healthy timeout to ensure all mutations are captured.

            setTimeout(function() {
              that.observer.disconnect();
            }, 1);
          };

          $.when(that.runExternalScripts(Object.keys(that.executedJs))).then(callback()); //that.on('runExternalScripts', callback);
          //that.runExternalScripts(Object.keys(that.executedJs));
        },

        /**
         * Event handler for when the modal window finishes fading out after hiding.
         * Clears out all events and elements of the modal.
         */
        destroySettings: function destroySettings(e) {
          var that = e && e.target ? e.target : this;
          that.$currentHtml.remove();
          that.$currentJs.remove();
          that.$currentCss.remove();
          that.$observed.remove();
        },

        /**
         * Event handler for the Close button.
         * Hides the modal window from view.
         */
        closeModal: function closeModal() {
          this.hide();
        },

        /**
         * Event handler for the save button.
         * Saves the Integration settings to the database.
         *
         * @param e
         */
        saveIntegration: function saveIntegration(e) {
          if (e) {
            e.preventDefault();
          }

          if (this.$saveBtn.hasClass('disabled') || !this.$saveSpinner.hasClass('hidden')) {
            return;
          }

          this.destroyListeners();
          this.$saveSpinner.removeClass('hidden');
          var data = this.$container.serialize();
          var inputId = this.$container.find('input[name="integrationId"]');
          var id = inputId.length ? inputId.val() : false;
          Craft.postActionRequest('sprout-forms/integrations/save-integration', data, $.proxy(function(response, textStatus) {
            this.$saveSpinner.addClass('hidden');
            var statusSuccess = textStatus === 'success';

            if (statusSuccess && response.success) {
              this.initListeners();
              this.trigger('saveIntegration', {
                target: this,
                integration: response.integration
              });
              Craft.cp.displayNotice(Craft.t('sprout-forms', '\'{name}\' integration saved.', {
                name: response.integration.name
              }));
              this.hide();
            } else if (statusSuccess && response.template) {
              if (this.visible) {
                var callback = $.proxy(function(e) {
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
        editIntegration: function editIntegration(id) {
          this.destroyListeners();
          this.show();
          this.initListeners();
          this.$loadSpinner.removeClass('hidden');
          var formId = $("#formId").val();
          var data = {
            'integrationId': id,
            'formId': formId
          };
          Craft.postActionRequest('sprout-forms/integrations/edit-integration', data, $.proxy(function(response, textStatus) {
            this.$loadSpinner.addClass('hidden');
            var statusSuccess = textStatus === 'success';

            if (statusSuccess && response.success) {
              var callback = $.proxy(function(e) {
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
        deleteIntegration: function deleteIntegration(e) {
          e.preventDefault();
          var userResponse = this.confirmDeleteIntegration();

          if (userResponse) {
            this.destroyListeners();
            var data = this.$container.serialize();
            var integrationId = $(this.$container).find('input[name="integrationId"]').val();
            Craft.postActionRequest('sprout-forms/integrations/delete-integration', data, $.proxy(function(response, textStatus) {
              var statusSuccess = textStatus === 'success';

              if (statusSuccess && response.success) {
                Craft.cp.displayNotice(Craft.t('sprout-forms', 'Integration deleted.'));
                $('#sproutforms-integration-row-' + integrationId).remove();
                this.initListeners();
                this.hide();
              } else {
                Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to delete integration.'));
                this.hide();
              }
            }, this));
          }
        },
        confirmDeleteIntegration: function confirmDeleteIntegration() {
          return confirm("Are you sure you want to delete this integration and all of it's settings?");
        },

        /**
         * Prevents the modal from closing if it's disabled.
         * This fixes issues if the modal is closed when saving/deleting integrations.
         */
        hide: function hide() {
          if (!this._disabled) {
            this.base();
          }
        },

        /**
         * Removes everything to do with the modal form the DOM.
         */
        destroy: function destroy() {
          this.base.destroy();
          this.destroyListeners();
          this.destroySettings();
          this.$shade.remove();
          this.$container.remove();
          this.trigger('destroy');
        }
      }, {
        /**
         * (Static) Singleton pattern.
         *
         * @returns IntegrationModal
         */
        getInstance: function getInstance() {
          if (!this._instance) {
            this._instance = new Craft.SproutForms.IntegrationModal();
          }

          return this._instance;
        }
      });
    })(jQuery);

    /***/
  }),

  /***/ 1:
  /*!*************************************************************************************************************************************************************************************************************************************************************************************************************!*\
    !*** multi ./src/web/assets/forms/src/js/ConditionalModal.js ./src/web/assets/forms/src/js/EditableTable.js ./src/web/assets/forms/src/js/FieldLayoutEditor.js ./src/web/assets/forms/src/js/FieldModal.js ./src/web/assets/forms/src/js/FormSettings.js ./src/web/assets/forms/src/js/IntegrationModal.js ***!
    \*************************************************************************************************************************************************************************************************************************************************************************************************************/
  /*! no static exports found */
  /***/ (function(module, exports, __webpack_require__) {

    __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/forms/src/js/ConditionalModal.js */"./src/web/assets/forms/src/js/ConditionalModal.js");
    __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/forms/src/js/EditableTable.js */"./src/web/assets/forms/src/js/EditableTable.js");
    __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/forms/src/js/FieldLayoutEditor.js */"./src/web/assets/forms/src/js/FieldLayoutEditor.js");
    __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/forms/src/js/FieldModal.js */"./src/web/assets/forms/src/js/FieldModal.js");
    __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/forms/src/js/FormSettings.js */"./src/web/assets/forms/src/js/FormSettings.js");
    module.exports = __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/forms/src/js/IntegrationModal.js */"./src/web/assets/forms/src/js/IntegrationModal.js");


    /***/
  })

  /******/
});