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
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/web/assets/base/src/scss/sproutforms.scss":
/*!*******************************************************!*\
  !*** ./src/web/assets/base/src/scss/sproutforms.scss ***!
  \*******************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./src/web/assets/charts/src/scss/charts-explorer.scss":
/*!*************************************************************!*\
  !*** ./src/web/assets/charts/src/scss/charts-explorer.scss ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./src/web/assets/entries/src/js/SproutFormsEntriesIndex.js":
/*!******************************************************************!*\
  !*** ./src/web/assets/entries/src/js/SproutFormsEntriesIndex.js ***!
  \******************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

if (_typeof(Craft.SproutForms) === ( true ? "undefined" : undefined)) {
  Craft.SproutForms = {};
}
/**
 * Class Craft.SproutForms.EntriesIndex
 */


Craft.SproutForms.EntriesIndex = Craft.BaseElementIndex.extend({
  getViewClass: function getViewClass(mode) {
    switch (mode) {
      case 'table':
        return Craft.SproutForms.EntriesTableView;

      default:
        return this.base(mode);
    }
  },
  getDefaultSort: function getDefaultSort() {
    return ['dateCreated', 'desc'];
  }
}); // Register the SproutForms EntriesIndex class

Craft.registerElementIndexClass('barrelstrength\\sproutforms\\elements\\Entry', Craft.SproutForms.EntriesIndex);

/***/ }),

/***/ "./src/web/assets/entries/src/js/SproutFormsEntriesTableView.js":
/*!**********************************************************************!*\
  !*** ./src/web/assets/entries/src/js/SproutFormsEntriesTableView.js ***!
  \**********************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

/*
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */
if (_typeof(Craft.SproutForms) === ( true ? "undefined" : undefined)) {
  Craft.SproutForms = {};
}
/**
 * Class Craft.SproutForms.EntriesTableView
 */


Craft.SproutForms.EntriesTableView = Craft.TableElementIndexView.extend({
  startDate: null,
  endDate: null,
  startDatepicker: null,
  endDatepicker: null,
  $chartExplorer: null,
  $totalValue: null,
  $chartContainer: null,
  $spinner: null,
  $error: null,
  $chart: null,
  $startDate: null,
  $endDate: null,
  afterInit: function afterInit() {
    this.$explorerContainer = $('<div class="chart-explorer-container"></div>').prependTo(this.$container);
    this.createChartExplorer();
    this.base();
  },
  getStorage: function getStorage(key) {
    return Craft.SproutForms.EntriesTableView.getStorage(this.elementIndex._namespace, key);
  },
  setStorage: function setStorage(key, value) {
    Craft.SproutForms.EntriesTableView.setStorage(this.elementIndex._namespace, key, value);
  },
  createChartExplorer: function createChartExplorer() {
    // chart explorer
    var $chartExplorer = $('<div class="chart-explorer"></div>').appendTo(this.$explorerContainer),
        $chartHeader = $('<div class="chart-header"></div>').appendTo($chartExplorer),
        $dateRange = $('<div class="date-range" />').appendTo($chartHeader),
        $startDateContainer = $('<div class="datewrapper"></div>').appendTo($dateRange),
        $to = $('<span class="to light">to</span>').appendTo($dateRange),
        $endDateContainer = $('<div class="datewrapper"></div>').appendTo($dateRange),
        $total = $('<div class="total"></div>').appendTo($chartHeader),
        $totalLabel = $('<div class="total-label light">' + Craft.t('sprout-forms', 'Total Submissions') + '</div>').appendTo($total),
        $totalValueWrapper = $('<div class="total-value-wrapper"></div>').appendTo($total);
    var $totalValue = $('<span class="total-value">&nbsp;</span>').appendTo($totalValueWrapper);
    this.$chartExplorer = $chartExplorer;
    this.$totalValue = $totalValue;
    this.$chartContainer = $('<div class="chart-container"></div>').appendTo($chartExplorer);
    this.$spinner = $('<div class="spinner hidden" />').prependTo($chartHeader);
    this.$error = $('<div class="error"></div>').appendTo(this.$chartContainer);
    this.$chart = $('<div class="chart"></div>').appendTo(this.$chartContainer);
    this.$startDate = $('<input type="text" class="text" size="20" autocomplete="off" />').appendTo($startDateContainer);
    this.$endDate = $('<input type="text" class="text" size="20" autocomplete="off" />').appendTo($endDateContainer);
    this.$startDate.datepicker($.extend({
      onSelect: $.proxy(this, 'handleStartDateChange')
    }, Craft.datepickerOptions));
    this.$endDate.datepicker($.extend({
      onSelect: $.proxy(this, 'handleEndDateChange')
    }, Craft.datepickerOptions));
    this.startDatepicker = this.$startDate.data('datepicker');
    this.endDatepicker = this.$endDate.data('datepicker');
    this.addListener(this.$startDate, 'keyup', 'handleStartDateChange');
    this.addListener(this.$endDate, 'keyup', 'handleEndDateChange'); // Set the start/end dates

    var startTime = this.getStorage('startTime') || new Date().getTime() - 60 * 60 * 24 * 30 * 1000,
        endTime = this.getStorage('endTime') || new Date().getTime();
    this.setStartDate(new Date(startTime));
    this.setEndDate(new Date(endTime)); // Load the report

    this.loadReport();
  },
  handleStartDateChange: function handleStartDateChange() {
    if (this.setStartDate(Craft.SproutForms.EntriesTableView.getDateFromDatepickerInstance(this.startDatepicker))) {
      this.loadReport();
    }
  },
  handleEndDateChange: function handleEndDateChange() {
    if (this.setEndDate(Craft.SproutForms.EntriesTableView.getDateFromDatepickerInstance(this.endDatepicker))) {
      this.loadReport();
    }
  },
  setStartDate: function setStartDate(date) {
    // Make sure it has actually changed
    if (this.startDate && date.getTime() === this.startDate.getTime()) {
      return false;
    }

    this.startDate = date;
    this.setStorage('startTime', this.startDate.getTime());
    this.$startDate.val(Craft.formatDate(this.startDate)); // If this is after the current end date, set the end date to match it

    if (this.endDate && this.startDate.getTime() > this.endDate.getTime()) {
      this.setEndDate(new Date(this.startDate.getTime()));
    }

    return true;
  },
  setEndDate: function setEndDate(date) {
    // Make sure it has actually changed
    if (this.endDate && date.getTime() === this.endDate.getTime()) {
      return false;
    }

    this.endDate = date;
    this.setStorage('endTime', this.endDate.getTime());
    this.$endDate.val(Craft.formatDate(this.endDate)); // If this is before the current start date, set the start date to match it

    if (this.startDate && this.endDate.getTime() < this.startDate.getTime()) {
      this.setStartDate(new Date(this.endDate.getTime()));
    }

    return true;
  },
  loadReport: function loadReport() {
    var requestData = this.settings.params;
    requestData.startDate = Craft.SproutForms.EntriesTableView.getDateValue(this.startDate);
    requestData.endDate = Craft.SproutForms.EntriesTableView.getDateValue(this.endDate);
    this.$spinner.removeClass('hidden');
    this.$error.addClass('hidden');
    this.$chart.removeClass('error');
    Craft.postActionRequest('sprout-forms/charts/get-entries-data', requestData, $.proxy(function (response, textStatus) {
      this.$spinner.addClass('hidden');

      if (textStatus === 'success' && typeof response.error === 'undefined') {
        if (!this.chart) {
          this.chart = new Craft.charts.Area(this.$chart);
        }

        var chartDataTable = new Craft.charts.DataTable(response.dataTable);
        var chartSettings = {
          localeDefinition: response.localeDefinition,
          orientation: response.orientation,
          formats: response.formats,
          dataScale: response.scale
        };
        this.chart.draw(chartDataTable, chartSettings);
        this.$totalValue.html(response.totalHtml);
      } else {
        var msg = Craft.t('sprout-forms', 'An unknown error occurred.');

        if (typeof response !== 'undefined' && response && typeof response.error !== 'undefined') {
          msg = response.error;
        }

        this.$error.html(msg);
        this.$error.removeClass('hidden');
        this.$chart.addClass('error');
      }
    }, this));
  }
}, {
  storage: {},
  getStorage: function getStorage(namespace, key) {
    if (Craft.SproutForms.EntriesTableView.storage[namespace] && Craft.SproutForms.EntriesTableView.storage[namespace][key]) {
      return Craft.SproutForms.EntriesTableView.storage[namespace][key];
    }

    return null;
  },
  setStorage: function setStorage(namespace, key, value) {
    if (_typeof(Craft.SproutForms.EntriesTableView.storage[namespace]) === ( true ? "undefined" : undefined)) {
      Craft.SproutForms.EntriesTableView.storage[namespace] = {};
    }

    Craft.SproutForms.EntriesTableView.storage[namespace][key] = value;
  },
  getDateFromDatepickerInstance: function getDateFromDatepickerInstance(inst) {
    return new Date(inst.currentYear, inst.currentMonth, inst.currentDay);
  },
  getDateValue: function getDateValue(date) {
    return date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
  }
});

/***/ }),

/***/ "./src/web/assets/forms/src/scss/forms.scss":
/*!**************************************************!*\
  !*** ./src/web/assets/forms/src/scss/forms.scss ***!
  \**************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./src/web/assets/integrations/src/scss/integrations.scss":
/*!****************************************************************!*\
  !*** ./src/web/assets/integrations/src/scss/integrations.scss ***!
  \****************************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!*************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** multi ./src/web/assets/entries/src/js/SproutFormsEntriesIndex.js ./src/web/assets/entries/src/js/SproutFormsEntriesTableView.js ./src/web/assets/base/src/scss/sproutforms.scss ./src/web/assets/charts/src/scss/charts-explorer.scss ./src/web/assets/forms/src/scss/forms.scss ./src/web/assets/integrations/src/scss/integrations.scss ***!
  \*************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/entries/src/js/SproutFormsEntriesIndex.js */"./src/web/assets/entries/src/js/SproutFormsEntriesIndex.js");
__webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/entries/src/js/SproutFormsEntriesTableView.js */"./src/web/assets/entries/src/js/SproutFormsEntriesTableView.js");
__webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/base/src/scss/sproutforms.scss */"./src/web/assets/base/src/scss/sproutforms.scss");
__webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/charts/src/scss/charts-explorer.scss */"./src/web/assets/charts/src/scss/charts-explorer.scss");
__webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/forms/src/scss/forms.scss */"./src/web/assets/forms/src/scss/forms.scss");
module.exports = __webpack_require__(/*! /Users/benparizek/Projects/Plugins-Craft3/barrelstrength/sprout-forms/src/web/assets/integrations/src/scss/integrations.scss */"./src/web/assets/integrations/src/scss/integrations.scss");


/***/ })

/******/ });