if (typeof SproutFormsRules === typeof undefined) {
  var SproutFormsRules = {};
}

// Manage field conditional logic
SproutFormsRules = {

  formId: null,
  form: null,
  allRules: {},
  fieldsToListen: {},
  targetFieldsHtml: {},
  fieldConditionalRules: {},
  xhr: {},

  init: function(settings) {
    this.formId = settings.formId;
    this.allRules = {};
    this.fieldsToListen = {};
    this.fieldConditionalRules = settings.fieldConditionalRules;
    this.form = document.getElementById(this.formId);

    const that = this;

    for (let i = 0; i < this.fieldConditionalRules.length; i++) {
      const conditional = this.fieldConditionalRules[i];
      const targetHandle = conditional.behaviorTarget;

      const fieldWrapper = document.getElementById("fields-" + targetHandle + "-field");
      const rules = {};
      for (let key in conditional['conditionalRules']) {
        for (let pos in conditional['conditionalRules'][key]) {
          const ruleObject = {};
          for (let posRule in conditional['conditionalRules'][key][pos]) {
            const rule = conditional['conditionalRules'][key][pos][posRule];
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

      const wrapperId = "fields-" + targetHandle + "-field";
      const wrapper = document.getElementById(wrapperId);
      if (conditional.behaviorAction == 'show') {
        this.hideField(wrapper);
      }

      this.allRules[targetHandle] = {
        "rules": rules,
        "action": conditional.behaviorAction
      };
    }

    // Enable events
    for (let fieldToListen in this.fieldsToListen) {
      const fieldId = this.getFieldId(fieldToListen);
      const inputField = document.getElementById(fieldId);
      let event = "change";
      if ((inputField.tagName === 'INPUT' && inputField.type === 'text') || inputField.tagName === 'TEXTAREA' ||
        (inputField.tagName === 'INPUT' && inputField.type === 'number')) {
        event = "keyup";
      }
      inputField.addEventListener(event, function(event) {
        that.runConditionalRulesForInput(this);
      }, false);

      // The number field can have change and keyup events
      if (inputField.tagName === 'INPUT' && inputField.type === 'number') {
        event = "change";
        inputField.addEventListener(event, function(event) {
          that.runConditionalRulesForInput(this);
        }, false);
      }
    }
  },

  callAjax: function(data, action = 'sprout-forms/conditionals/validate-condition') {
    const xhr = new XMLHttpRequest();
    const postData = {};
    xhr.open('POST', '/');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    const that = this;
    xhr.onload = function() {
      const conditionalLogicResults = that.form.querySelectorAll('[name="conditionalLogicResults"]');

      conditionalLogicResults[0].value = this.response;
      const response = JSON.parse(this.response);
      if (this.status === 200 && response.success == true) {
        // apply rules
        for (let targetField in response.result) {
          const wrapperId = "fields-" + targetField + "-field";
          const wrapper = document.getElementById(wrapperId);
          const rule = that.allRules[targetField];
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
    const str = [];
    for (let key in postData) {
      if (postData.hasOwnProperty(key)) {
        str.push(encodeURIComponent(key) + "=" + encodeURIComponent(postData[key]));
      }
    }

    xhr.send(str.join("&"));
  },
  hideField: function(element) {
    element.className += ' hidden';
  },
  showField: function(element) {
    element.classList.remove('hidden');
  },
  /**
   * Run all rules where this input is involved
   * prepare all the info to run the validation in the backend
   **/
  runConditionalRulesForInput: function(input) {
    const inputFieldHandle = input.id.replace('fields-', '');
    const postData = {};
    for (let targetField in this.allRules) {
      const wrapperId = "fields-" + targetField + "-field";
      const wrapper = document.getElementById(wrapperId);

      const conditional = this.allRules[targetField];
      const result = false;
      const andResult = true;
      let i = 0;
      const data = {};
      for (let andPos in conditional.rules) {
        const andRule = conditional.rules[andPos];
        const orConditions = [];
        for (let orPos in andRule) {
          const rule = andRule[orPos];
          const fieldId = this.getFieldId(rule.fieldHandle);
          const inputField = document.getElementById(fieldId);
          let inputValue = typeof inputField.value === 'undefined' ? '' : inputField.value;
          if (inputField.type === 'checkbox'){
            inputValue = inputField.checked;
          }
          if (typeof inputField.type === 'undefined'){
            const radios = inputField.querySelectorAll('input[type="radio"]');
            if (radios.length >= 1){
              for (let i = 0; i < radios.length; i++){
                let radio = radios[i];
                if (radio.checked){
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
    //console.log(postData);
    this.callAjax({data: postData});
  },

  getFieldId: function(fieldHandle) {
    return "fields-" + fieldHandle;
  }
};