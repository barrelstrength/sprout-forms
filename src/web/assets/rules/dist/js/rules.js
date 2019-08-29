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
        this.formId = settings.id;
        this.allRules = {};
        this.fieldsToListen = {};
        this.targetFieldsHtml= {};
        this.fieldConditionalRules = settings.fieldConditionalRules;
        this.form = document.getElementById(this.formId);
        this.xhr = new XMLHttpRequest();
        this.xhr.open('POST', '/');
        this.xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        var that = this;

        this.xhr.onload = function() {
            if (that.xhr.status === 200) {
                console.log(that.xhr);
            }else{
                console.error("Something went wrong");
            }
        };

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
            this.allRules[targetHandle] = {
                "rules": rules,
                "action": conditional.behaviorAction
            };
        }

        for (var targetField in this.allRules) {
            // This is the wrapper id
            // if the user uses template overrides they may need to update this code
            var wrapperId = "fields-" + targetField + "-field";
            var wrapper = document.getElementById(wrapperId);
            this.targetFieldsHtml[targetField] = wrapper.innerHTML;
        }

        // Enable events
        for (var fieldToListen in this.fieldsToListen) {
            var fieldId = this.getFieldId(fieldToListen);
            var inputField = document.getElementById(fieldId);
            inputField.addEventListener("change", function(event) {
                this.runConditionalRules(event);
            }.bind(this), false);
        }
    },

    callAjax: function(data, action = 'sprout-forms/conditionals/validate-condition')
    {
        data['action'] = action;
        var str = [];
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                str.push(encodeURIComponent(key) + "=" + encodeURIComponent(data[key]));
            }
        }

        this.xhr.send(str.join("&"));
    },

    runConditionalRules: function(event) {
        for (var targetField in this.allRules) {
            var wrapperId = "fields-" + targetField + "-field";
            var wrapper = document.getElementById(wrapperId);

            var conditional = this.allRules[targetField];
            var result = false;
            var andResult = true;
            for (var andPos in conditional.rules) {
                var andRule = conditional.rules[andPos];
                var orResult = {};
                for (var orPos in andRule) {
                    var rule = andRule[orPos];
                    var fieldId = this.getFieldId(rule.fieldHandle);
                    var inputField = document.getElementById(fieldId);
                    var inputValue = inputField.value;

                    // @todo - should we ignore empty values?
                    if (inputValue == '') {
                        continue;
                    }
                    var data = {
                        condition: rule.condition,
                        inputValue: inputValue,
                        ruleValue: rule.value
                    };
                    this.callAjax(data);
                }

                if (!(1 in orResult)) {
                    andResult = false;
                }
            }

            if (andResult) {
                if (conditional.action == 'hide') {
                    wrapper.innerHTML = "";
                } else {
                    wrapper.innerHTML = this.targetFieldsHtml[targetField];
                }
            } else {
                wrapper.innerHTML = this.targetFieldsHtml[targetField];
            }
        }
    },

    getFieldId: function(fieldHandle) {
        return "fields-" + fieldHandle;
    }
};