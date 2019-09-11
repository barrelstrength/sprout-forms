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
/******/ 	return __webpack_require__(__webpack_require__.s = 3);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/web/assets/rules/src/js/rules.js":
/*!**********************************************!*\
  !*** ./src/web/assets/rules/src/js/rules.js ***!
  \**********************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var SproutForms = SproutForms || {};

if (_typeof(SproutForms.FieldConditionalLogic) === ( true ? "undefined" : undefined)) {
  SproutForms.FieldConditionalLogic = {};
} // Manage field conditional logic


SproutForms.FieldConditionalLogic = {
  formId: null,
  form: null,
  allRules: {},
  fieldsToListen: {},
  targetFieldsHtml: {},
  fieldConditionalRules: {},
  xhr: {},
  init: function init(settings) {
    this.formId = settings.formId;
    this.allRules = {};
    this.fieldsToListen = {};
    this.fieldConditionalRules = settings.fieldConditionalRules;
    this.form = document.getElementById(this.formId);
    var that = this;

    for (var i = 0; i < this.fieldConditionalRules.length; i++) {
      var conditional = this.fieldConditionalRules[i];
      var targetHandle = conditional.behaviorTarget;
      var fieldWrapper = document.getElementById("fields-" + targetHandle + "-field");
      var rules = {};

      for (var key in conditional['conditionalRules']) {
        for (var pos in conditional['conditionalRules'][key]) {
          var ruleObject = {};

          for (var posRule in conditional['conditionalRules'][key][pos]) {
            var rule = conditional['conditionalRules'][key][pos][posRule];
            this.fieldsToListen[rule[0]] = 1;
            ruleObject[posRule] = {
              'fieldHandle': rule[0],
              'condition': rule[1],
              'value': rule[2]
            };
          }

          rules[key] = ruleObject;
        }
      }

      var wrapperId = "fields-" + targetHandle + "-field";
      var wrapper = document.getElementById(wrapperId);

      if (conditional.behaviorAction == 'show') {
        this.hideField(wrapper);
      }

      this.allRules[targetHandle] = {
        "rules": rules,
        "action": conditional.behaviorAction
      };
    } // Enable events


    for (var fieldToListen in this.fieldsToListen) {
      var fieldId = this.getFieldId(fieldToListen);
      var inputField = document.getElementById(fieldId);
      var event = "change";

      if (inputField.tagName === 'INPUT' && inputField.type === 'text' || inputField.tagName === 'TEXTAREA') {
        event = "keyup";
      }

      inputField.addEventListener(event, function (event) {
        that.runConditionalRulesForInput(this);
      }, false);
    }
  },
  callAjax: function callAjax(data) {
    var action = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'sprout-forms/conditionals/validate-condition';
    var xhr = new XMLHttpRequest();
    var postData = {};
    xhr.open('POST', '/');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    var that = this;

    xhr.onload = function () {
      var conditionalLogicResults = that.form.querySelectorAll('[name="conditionalLogicResults"]');
      conditionalLogicResults[0].value = this.response;
      var response = JSON.parse(this.response);

      if (this.status === 200 && response.success == true) {
        // apply rules
        for (var targetField in response.result) {
          var wrapperId = "fields-" + targetField + "-field";
          var wrapper = document.getElementById(wrapperId);
          var rule = that.allRules[targetField];

          if (response.result[targetField] == true) {
            if (rule.action == 'hide') {
              that.hideField(wrapper);
            } else {
              that.showField(wrapper);
            }
          } else {
            if (rule.action == 'hide') {
              that.showField(wrapper);
            } else {
              that.hideField(wrapper);
            }
          }
        }
      } else {
        console.error("Something went wrong");
      }
    };

    postData['action'] = action;
    postData['rules'] = JSON.stringify(data);
    var str = [];

    for (var key in postData) {
      if (postData.hasOwnProperty(key)) {
        str.push(encodeURIComponent(key) + "=" + encodeURIComponent(postData[key]));
      }
    }

    xhr.send(str.join("&"));
  },
  hideField: function hideField(element) {
    element.className += ' hidden';
  },
  showField: function showField(element) {
    element.classList.remove('hidden');
  },

  /**
   * Run all rules where this input is involved
   * prepare all the info to run the validation in the backend
   **/
  runConditionalRulesForInput: function runConditionalRulesForInput(input) {
    var inputFieldHandle = input.id.replace('fields-', '');
    var postData = {};

    for (var targetField in this.allRules) {
      var wrapperId = "fields-" + targetField + "-field";
      var wrapper = document.getElementById(wrapperId);
      var conditional = this.allRules[targetField];
      var result = false;
      var andResult = true;
      var i = 0;
      var data = {};

      for (var andPos in conditional.rules) {
        var andRule = conditional.rules[andPos];
        var orConditions = [];

        for (var orPos in andRule) {
          var rule = andRule[orPos];
          var fieldId = this.getFieldId(rule.fieldHandle);
          var inputField = document.getElementById(fieldId);
          var inputValue = inputField.value;
          orConditions.push({
            condition: rule.condition,
            inputValue: inputValue,
            ruleValue: typeof rule.value === 'undefined' ? '' : rule.value
          });
        }

        data[i] = orConditions;
        i++;
      }

      postData[targetField] = data;
    } //console.log(postData);


    this.callAjax({
      data: postData
    });
  },
  getFieldId: function getFieldId(fieldHandle) {
    return "fields-" + fieldHandle;
  }
};

/***/ }),

/***/ 3:
/*!****************************************************!*\
  !*** multi ./src/web/assets/rules/src/js/rules.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/rules/src/js/rules.js */"./src/web/assets/rules/src/js/rules.js");


/***/ })

/******/ });