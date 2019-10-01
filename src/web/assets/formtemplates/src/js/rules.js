if (typeof SproutFormsRules === typeof undefined) {
  var SproutFormsRules = {};
}

// Manage Form Rules
SproutFormsRules = {

  formId: null,
  form: null,
  allRules: {},
  fieldsToListen: {},
  targetFieldsHtml: {},
  rulesJson: {},

  init: function(formId) {
    this.formId = formId;
    this.form = document.getElementById(this.formId);
    this.rulesJson = JSON.parse(this.form.dataset.rules);
    this.allRules = {};
    this.fieldsToListen = {};

    var self = this;

    for (var i = 0; i < this.rulesJson.length; i++) {
      var rule = this.rulesJson[i];
      var targetHandle = rule.behaviorTarget;
      var fieldWrapper = document.getElementById("fields-" + targetHandle + "-field");
      var rules = {};
      var conditionSets = rule.conditions;

      for (conditionSetId in conditionSets) {
        var conditionSet = conditionSets[conditionSetId];
        for (conditionId in conditionSet) {
          var conditionObject = {};
          var conditionSetIndex = conditionId.split('-').pop();
          var condition = conditionSet[conditionId];

          this.fieldsToListen[condition[0]] = 1;

          conditionObject[conditionSetIndex] = {
            'fieldHandle': condition[0],
            'condition': condition[1],
            'value': condition[2]
          };

          rules[conditionId] = conditionObject;
        }
      }

      var wrapperId = "fields-" + targetHandle + "-field";
      var wrapper = document.getElementById(wrapperId);
      if (rule.behaviorAction === 'show') {
        this.hideAndDisableField(wrapper);
      }

      this.allRules[targetHandle] = {
        "rules": rules,
        "action": rule.behaviorAction
      };
    }

    // Enable events
    for (var fieldToListen in this.fieldsToListen) {
      var fieldId = this.getFieldId(fieldToListen);
      var inputField = document.getElementById(fieldId);
      var event = "change";
      if (
        inputField.tagName === 'TEXTAREA' ||
        (inputField.tagName === 'INPUT' && inputField.type === 'text') ||
        (inputField.tagName === 'INPUT' && inputField.type === 'number')) {
        event = "keyup";
      }
      inputField.addEventListener(event, function(event) {
        self.runConditionsForInput(this);
      }, false);

      // The number field can have change and keyup events
      if (inputField.tagName === 'INPUT' && inputField.type === 'number') {
        event = "change";
        inputField.addEventListener(event, function(event) {
          self.runConditionsForInput(this);
        }, false);
      }
    }
  },

  /**
   * Run all rules where this input is involved
   * prepare all the info to run the validation in the backend
   **/
  runConditionsForInput: function(input) {
    var inputFieldHandle = input.id.replace('fields-', '');
    var conditionsByField = {};

    for (var targetField in this.allRules) {
      var wrapperId = "fields-" + targetField + "-field";
      var wrapper = document.getElementById(wrapperId);

      var conditional = this.allRules[targetField];
      var result = false;
      var andResult = true;
      var i = 0;
      var conditions = {};

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
        conditions[i] = orConditions;
        i++;
      }
      conditionsByField[targetField] = conditions;
    }

    var rules = JSON.stringify({
      data: conditionsByField
    });

    this.validateConditions(rules);
  },

  validateConditions: function(rules) {
    var self = this;
    var postData = {};

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
      var response = JSON.parse(this.response);
      if (this.status === 200 && response.success == true) {
        // apply rules
        for (var targetField in response.result) {
          var wrapperId = "fields-" + targetField + "-field";
          var wrapper = document.getElementById(wrapperId);
          var rule = self.allRules[targetField];
          if (response.result[targetField] == true) {
            if (rule.action == 'hide') {
              self.hideAndDisableField(wrapper);
            } else {
              self.showAndEnableField(wrapper);
            }
          } else {
            if (rule.action == 'hide') {
              self.showAndEnableField(wrapper);
            } else {
              self.hideAndDisableField(wrapper);
            }
          }
        }
      } else {
        console.error("Invalid request while validating conditions");
      }
    };

    postData[window.csrfTokenName] = this.form.querySelector('[name="'+window.csrfTokenName+'"]').value;
    postData['action'] = 'sprout-forms/rules/validate-condition';
    postData['rules'] = rules;

    var body = Object.keys(postData).map(function(key) {
      return encodeURIComponent(key) + '=' + encodeURIComponent(postData[key])
    }).join('&');

    xhr.send(body);
  },

  getFieldId: function(fieldHandle) {
    return "fields-" + fieldHandle;
  },

  hideAndDisableField: function(element) {
    // Disable all form elements within this field
    var inputs = element.querySelectorAll('input, select, option, textarea, button, datalist, output');
    for (var key in inputs) {
      var input = inputs[key];
      input.disabled = true;
    }

    // Hide field
    element.classList.add('hidden');
  },

  showAndEnableField: function(element) {
    // Enabled all form elements within this field
    var inputs = element.querySelectorAll('input, select, option, textarea, button, datalist, output');
    for (var key in inputs) {
      var input = inputs[key];
      input.disabled = false;
    }

    // Show field
    element.classList.remove('hidden');
  },
};