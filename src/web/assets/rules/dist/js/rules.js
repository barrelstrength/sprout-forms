var SproutForms = SproutForms || {};
if (typeof SproutForms.FieldConditionalLogic === typeof undefined) {
    SproutForms.FieldConditionalLogic = {};
}

// Manage field conditional logic
SproutForms.FieldConditionalLogic = {

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
            if (conditional.behaviorAction == 'show'){
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
            if (inputField.tagName === 'INPUT' && inputField.type === 'text'){
                event = "keyup";
            }
            inputField.addEventListener(event, function(event) {
                that.runConditionalRulesForInput(this);
            }, false);
        }
    },

    callAjax: function(data, action = 'sprout-forms/conditionals/validate-condition')
    {
        var xhr = new XMLHttpRequest();
        var postData = {};
        xhr.open('POST', '/');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var that = this;
        xhr.onload = function() {
            var conditionalLogicResults = that.form.querySelectorAll('[name="conditionalLogicResults"]');

            conditionalLogicResults[0].value = this.response;
            var response = JSON.parse(this.response);
            if (this.status === 200 && response.success == true) {
                // apply rules
                for (var targetField in response.result) {
                    var wrapperId = "fields-" + targetField + "-field";
                    var wrapper = document.getElementById(wrapperId);
                    var rule = that.allRules[targetField];
                    if (response.result[targetField] == true){
                        if (rule.action == 'hide'){
                            that.hideField(wrapper);
                        }else{
                            that.showField(wrapper);
                        }
                    }else{
                        if (rule.action == 'hide'){
                            that.showField(wrapper);
                        }else{
                            that.hideField(wrapper);
                        }
                    }
                }
            }else{
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
    hideField: function(element)
    {
        element.className+=' hidden';
    },
    showField: function(element)
    {
        element.classList.remove('hidden');
    },
    /**
     * Run all rules where this input is involved
     * prepare all the info to run the validation in the backend
     **/
    runConditionalRulesForInput: function(input) {
        var inputFieldHandle = input.id.replace('fields-','');
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
                        ruleValue: rule.value
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