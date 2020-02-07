/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

// Manage Form Rules
class SproutFormsRules {

  constructor(formId) {
    this.formId = formId;
    this.form = document.getElementById(this.formId);
    this.allRules = {};
    this.fieldsToListen = {};

    if (!this.form.dataset.rules) {
      return;
    }

    this.rulesJson = JSON.parse(this.form.dataset.rules);

    let self = this;

    for (let i = 0; i < this.rulesJson.length; i++) {
      let rules = this.rulesJson[i];
      let targetHandle = rules.behaviorTarget;
      let fieldWrapper = document.getElementById('fields-' + targetHandle + '-field');
      let ruleSets = {};
      let j = 0;
      let andConditionSets = rules.conditions;
      for (let andConditionSetKey in andConditionSets) {
        let andConditionSet = andConditionSets[andConditionSetKey];
        let orConditions = [];
        for (let orConditionKey in andConditionSet) {
          let condition = andConditionSet[orConditionKey];

          this.fieldsToListen[condition[0]] = 1;

          orConditions.push({
            'fieldHandle': condition[0],
            'condition': condition[1],
            'value': condition[2]
          });
        }
        ruleSets[andConditionSetKey] = orConditions;

        j++;
      }

      let wrapperId = 'fields-' + targetHandle + '-field';
      let wrapper = document.getElementById(wrapperId);
      if (rules.behaviorAction === 'show') {
        this.hideAndDisableField(wrapper);
      }

      this.allRules[targetHandle] = {
        "rules": ruleSets,
        "action": rules.behaviorAction
      };
    }

    // Enable events
    for (let fieldToListen in this.fieldsToListen) {
      let fieldId = this.getFieldId(fieldToListen);
      let inputField = document.getElementById(fieldId);

      // Watch all fields for the 'change' event
      inputField.addEventListener('change', function(event) {
        self.runConditionsForInput(event);
      }, false);

      if (
        inputField.tagName === 'TEXTAREA' ||
        (inputField.tagName === 'INPUT' && inputField.type === 'text') ||
        (inputField.tagName === 'INPUT' && inputField.type === 'number')) {

        // Add support for 'keyup' and 'paste' event to these fields
        inputField.addEventListener('keyup', function(event) {
          self.runConditionsForInput(event);
        }, false);
      }

      // on first page load
      self.runConditionsForInput(inputField);
    }
  }

  /**
   * Run all rules where this input is involved
   * prepare all the info to run the validation in the backend
   **/
  runConditionsForInput(event) {
    let conditionsByField = {};

    for (let targetField in this.allRules) {
      let rules = this.allRules[targetField];

      let i = 0;
      let conditions = {};

      for (let andConditionSetKey in rules.rules) {
        let andConditionSet = rules.rules[andConditionSetKey];
        let orConditions = [];
        for (let orConditionKey in andConditionSet) {
          let condition = andConditionSet[orConditionKey];
          let fieldId = this.getFieldId(condition.fieldHandle);
          let inputField = document.getElementById(fieldId);
          let inputValue = typeof inputField.value === 'undefined' ? '' : inputField.value;

          if (event.type === 'paste') {
            let clipboardData = event.clipboardData || window.clipboardData;
            let pastedData = clipboardData.getData('Text');
            inputValue = pastedData;
          }

          if (inputField.type === 'checkbox') {
            inputValue = inputField.checked;
          }

          if (typeof inputField.type === 'undefined') {
            let radios = inputField.querySelectorAll('input[type="radio"]');
            if (radios.length >= 1) {
              for (let i = 0; i < radios.length; i++) {
                let radio = radios[i];
                if (radio.checked) {
                  inputValue = radio.value;
                  break;
                }
              }
            }
          }

          orConditions.push({
            condition: condition.condition,
            inputValue: inputValue,
            ruleValue: typeof condition.value === 'undefined' ? '' : condition.value
          });

        }

        conditions[i] = orConditions;
        i++;
      }
      conditionsByField[targetField] = conditions;
    }

    let rules = JSON.stringify({
      data: conditionsByField
    });

    this.validateConditions(rules);
  }

  validateConditions(rules) {
    let self = this;
    let postData = {};

    let xhr = new XMLHttpRequest();
    xhr.open('POST', '/');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
      let response = JSON.parse(this.response);
      if (this.status === 200 && response.success === true) {
        // Apply the conditions
        for (let targetField in response.result) {
          let wrapperId = 'fields-' + targetField + '-field';
          let wrapper = document.getElementById(wrapperId);
          let rule = self.allRules[targetField];
          if (response.result[targetField] === true) {
            if (rule.action === 'hide') {
              self.hideAndDisableField(wrapper);
            } else {
              self.showAndEnableField(wrapper);
            }
          } else {
            if (rule.action === 'hide') {
              self.showAndEnableField(wrapper);
            } else {
              self.hideAndDisableField(wrapper);
            }
          }
        }
      } else {
        console.error('Invalid request while validating conditions');
      }
    };

    postData[window.csrfTokenName] = this.form.querySelector('[name="' + window.csrfTokenName + '"]').value;
    postData['action'] = 'sprout-forms/rules/validate-condition';
    postData['rules'] = rules;

    let body = Object.keys(postData).map(function(key) {
      return encodeURIComponent(key) + '=' + encodeURIComponent(postData[key])
    }).join('&');

    xhr.send(body);
  }

  getFieldId(fieldHandle) {
    return "fields-" + fieldHandle;
  }

  hideAndDisableField(element) {
    // Disable all form elements within this field
    let inputs = element.querySelectorAll('input, select, option, textarea, button, datalist, output');
    for (let key in inputs) {
      let input = inputs[key];
      input.disabled = true;
    }

    // Hide field
    element.classList.add('sprout-hidden');
  }

  showAndEnableField(element) {
    // Enabled all form elements within this field
    let inputs = element.querySelectorAll('input, select, option, textarea, button, datalist, output');
    for (let key in inputs) {
      let input = inputs[key];
      input.disabled = false;
    }

    // Show field
    element.classList.remove('sprout-hidden');
  }
}

window.SproutFormsRules = SproutFormsRules;