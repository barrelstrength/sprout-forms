// Manage Form Rules
class SproutFormsRules {

  constructor(formId) {
    this.formId = formId;
    this.form = document.getElementById(this.formId);
    this.allRules = {};
    this.fieldsToListen = {};
    this.rulesJson = JSON.parse(this.form.dataset.rules);

    let self = this;

    for (let i = 0; i < this.rulesJson.length; i++) {
      let rule = this.rulesJson[i];
      let targetHandle = rule.behaviorTarget;
      let fieldWrapper = document.getElementById('fields-' + targetHandle + '-field');
      let rules = {};
      let j = 0;
      let conditionSets = rule.conditions;
      for (conditionSetId in conditionSets) {
        let conditionSet = conditionSets[conditionSetId];
        let conditionSetKey = 'condition-set-'+j;

        let orArray = [];
        for (conditionId in conditionSet) {
          let conditionObject = {};
          let condition = conditionSet[conditionId];

          this.fieldsToListen[condition[0]] = 1;

          conditionObject = {
            'fieldHandle': condition[0],
            'condition': condition[1],
            'value': condition[2]
          };
          orArray.push(conditionObject)
        }
        rules[conditionSetKey] = orArray;

        j++;
      }

      let wrapperId = 'fields-' + targetHandle + '-field';
      let wrapper = document.getElementById(wrapperId);
      if (rule.behaviorAction === 'show') {
        this.hideAndDisableField(wrapper);
      }

      this.allRules[targetHandle] = {
        "rules": rules,
        "action": rule.behaviorAction
      };
    }

    // Enable events
    for (let fieldToListen in this.fieldsToListen) {
      let fieldId = this.getFieldId(fieldToListen);
      let inputField = document.getElementById(fieldId);
      let event = "change";
      if (
        inputField.tagName === 'TEXTAREA' ||
        (inputField.tagName === 'INPUT' && inputField.type === 'text') ||
        (inputField.tagName === 'INPUT' && inputField.type === 'number')) {
        event = "keyup";
      }
      inputField.addEventListener(event, function(event) {
        self.runConditionsForInput(this);
      }, false);
      // on first page load
      self.runConditionsForInput(inputField);

      // The number field can have change and keyup events
      if (inputField.tagName === 'INPUT' && inputField.type === 'number') {
        event = "change";
        inputField.addEventListener(event, function(event) {
          self.runConditionsForInput(this);
        }, false);
      }
    }
  }

  /**
   * Run all rules where this input is involved
   * prepare all the info to run the validation in the backend
   **/
  runConditionsForInput(input) {
    let inputFieldHandle = input.id.replace('fields-', '');
    let conditionsByField = {};
    for (let targetField in this.allRules) {
      let wrapperId = 'fields-' + targetField + '-field';
      let wrapper = document.getElementById(wrapperId);

      let conditional = this.allRules[targetField];
      let result = false;
      let andResult = true;
      let i = 0;
      let conditions = {};

      for (let andPos in conditional.rules) {
        let andRule = conditional.rules[andPos];
        let orConditions = [];
        for (let orPos in andRule) {
          let rule = andRule[orPos];
          let fieldId = this.getFieldId(rule.fieldHandle);
          let inputField = document.getElementById(fieldId);
          let inputValue = typeof inputField.value === 'undefined' ? '' : inputField.value;
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
        // apply rules
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