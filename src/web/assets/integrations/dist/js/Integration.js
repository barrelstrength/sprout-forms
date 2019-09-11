/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/web/assets/integrations/src/js/Integration.js":
/*!***********************************************************!*\
  !*** ./src/web/assets/integrations/src/js/Integration.js ***!
  \***********************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/* global Craft */
if (_typeof(Craft.SproutForms) === ( true ? "undefined" : undefined)) {
  Craft.SproutForms = {};
}

(function ($) {
  Craft.SproutForms.Integration = Garnish.Base.extend({
    integrationType: null,
    updateTargetFieldsOnChange: [],
    init: function init(settings) {
      var that = this;
      this.integrationType = typeof settings.integrationType !== 'undefined' ? settings.integrationType : ''; // Make the sourceFormField read only

      this.disableOptions(); // Init all empty field selects

      this.updateAllFieldSelects();
      this.updateTargetFieldsOnChange = typeof settings.updateTargetFieldsOnChange !== 'undefined' ? settings.updateTargetFieldsOnChange : [];
      this.updateTargetFieldsOnChange.forEach(function (elementId) {
        // Register an onChange event for all Element IDs identified by the Integration
        $(elementId).change(function () {
          that.updateAllFieldSelects();
        });
      });
    },
    disableOptions: function disableOptions() {
      var that = this;
      var integrationId = $('#integrationId').val();
      data = {
        'integrationId': integrationId
      };
      Craft.postActionRequest('sprout-forms/integrations/get-source-form-fields', data, $.proxy(function (response, textStatus) {
        var statusSuccess = textStatus === 'success';

        if (statusSuccess && response.success) {
          var rows = response.sourceFormFields;
          $('tbody .formField').each(function (index) {
            var td = $(this);
            td.empty();
            var title = rows[index]["label"];
            var handle = rows[index]["value"];
            td.append('<div style="display:none;"><select readonly name="settings[' + that.integrationType + '][fieldMapping][' + index + '][sourceFormField]"><option selected value="' + handle + '">' + title + '</option></select></div><div style="padding: 7px 10px;font-size: 12px;color:#8f98a3;">' + title + ' <span class="code">(' + handle + ')</span></div>');
          });
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the Form fields'));
        }
      }, this));
      return null;
    },
    updateAllFieldSelects: function updateAllFieldSelects() {
      var integrationIdBase = this.integrationType.replace(/\\/g, '-');
      var mappingTableRows = 'table#settings-' + integrationIdBase + '-fieldMapping tr';
      $(mappingTableRows).find('td:eq(2),th:eq(2)').remove();
      $(mappingTableRows).find('td:eq(0),th:eq(0)').css('width', '50%');
      $(mappingTableRows).find('td:eq(1),th:eq(1)').css('width', '50%');
      var $currentRows = this.getCurrentRows('tbody .targetFields'); // Hand off all our current Form data so the Integration can use it if needed

      var data = $("#integrationId").closest('form').serialize();
      var that = this;
      Craft.postActionRequest('sprout-forms/integrations/get-target-integration-fields', data, $.proxy(function (response, textStatus) {
        var statusSuccess = textStatus === 'success';

        if (statusSuccess && response.success) {
          var rows = response.targetIntegrationFields;

          if (rows.length === 0) {
            return false;
          }

          $currentRows.each(function (index) {
            var $select = $(this).find('select');
            var fields = rows[index];
            that.appendFieldsToSelect($select, fields);
          });
        } else {
          Craft.cp.displayError(Craft.t('sprout-forms', 'Unable to get the Form fields'));
        }
      }, this));
    },
    getCurrentRows: function getCurrentRows() {
      var className = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

      if (className === null) {
        className = 'tbody .formField';
      }

      return $(className);
    },
    appendFieldsToSelect: function appendFieldsToSelect($select, fields) {
      $select.empty();
      var dropdown = '';
      var closeOptgroup = false;
      $select.append('<option value="">' + Craft.t('sprout-forms', 'None') + '</option>');

      for (i = 0; i < fields.length; i++) {
        var field = fields[i];
        var selectedCode = '';
        var lastItem = i === fields.length - 1;

        if (!("optgroup" in field)) {
          if ("selected" in field) {
            selectedCode = 'selected';
          }

          dropdown += '<option ' + selectedCode + ' value="' + field['value'] + '">' + field['label'] + '</option>';
        }

        if ("optgroup" in field && closeOptgroup || lastItem && closeOptgroup) {
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

/***/ }),

/***/ 2:
/*!*****************************************************************!*\
  !*** multi ./src/web/assets/integrations/src/js/Integration.js ***!
  \*****************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/integrations/src/js/Integration.js */"./src/web/assets/integrations/src/js/Integration.js");


/***/ })

/******/ });