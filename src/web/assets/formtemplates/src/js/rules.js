if (typeof SproutFormsRules === typeof undefined) {
  var SproutFormsRules = {};
}

// Manage Form Rules
SproutFormsRules = {

  id: null,
  form: null,
  allRules: {},
  fieldsToListen: {},
  targetFieldsHtml: {},
  fieldConditionalRules: {},

  init: function(settings) {
    this.id = settings.id;
    this.fieldConditionalRules = settings.fieldConditionalRules;
    this.form = document.getElementById(this.id);
    this.allRules = {};
    this.fieldsToListen = {};

    var self = this;

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
    }

    // Enable events
    for (var fieldToListen in this.fieldsToListen) {
      var fieldId = this.getFieldId(fieldToListen);
      var inputField = document.getElementById(fieldId);
      var event = "change";
      if ((inputField.tagName === 'INPUT' && inputField.type === 'text') || inputField.tagName === 'TEXTAREA' ||
        (inputField.tagName === 'INPUT' && inputField.type === 'number')) {
        event = "keyup";
      }
      inputField.addEventListener(event, function(event) {
        self.runConditionalRulesForInput(this);
      }, false);

      // The number field can have change and keyup events
      if (inputField.tagName === 'INPUT' && inputField.type === 'number') {
        event = "change";
        inputField.addEventListener(event, function(event) {
          self.runConditionalRulesForInput(this);
        }, false);
      }
    }
  },

  /**
   * Run all rules where this input is involved
   * prepare all the info to run the validation in the backend
   **/
  runConditionalRulesForInput: function(input) {
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
          var inputValue = typeof inputField.value === 'undefined' ? '' : inputField.value;
          if (inputField.type === 'checkbox') {
            inputValue = inputField.checked;
          }
          if (typeof inputField.type === 'undefined') {
            var radios = inputField.querySelectorAll('input[type="radio"]');
            if (radios.length >= 1) {
              for (var i = 0; i < radios.length; i++) {
                var radio = radios[i];
                if (radio.checked) {
                  inputValue = radio.value;
                  break;
                }
              }
            }
          }
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
    }

    this.callAjax({data: postData});
  },

  callAjax: function(data, action = 'sprout-forms/rules/validate-condition') {

    var self = this;
    var postData = {};
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
      var conditionalLogicResults = self.form.querySelectorAll('[name="conditionalLogicResults"]');

      conditionalLogicResults[0].value = this.response;
      var response = JSON.parse(this.response);
      if (this.status === 200 && response.success == true) {
        // apply rules
        for (var targetField in response.result) {
          var wrapperId = "fields-" + targetField + "-field";
          var wrapper = document.getElementById(wrapperId);
          var rule = self.allRules[targetField];
          if (response.result[targetField] == true) {
            if (rule.action == 'hide') {
              self.hideField(wrapper);
            } else {
              self.showField(wrapper);
            }
          } else {
            if (rule.action == 'hide') {
              self.showField(wrapper);
            } else {
              self.hideField(wrapper);
            }
          }
        }
      } else {
        console.error("Something went wrong");
      }
    };

    postData[window.csrfTokenName] = window.csrfTokenValue;
    postData['action'] = action;
    postData['rules'] = JSON.stringify(data);

    var body = Object.keys(postData).map(function(key) {
      return encodeURIComponent(key) + '=' + encodeURIComponent(postData[key])
    }).join('&');

    xhr.send(body);
  },

  getFieldId: function(fieldHandle) {
    return "fields-" + fieldHandle;
  },

  hideField: function(element) {
    element.classList.add('hidden');
  },

  showField: function(element) {
    element.classList.remove('hidden');
  },
};