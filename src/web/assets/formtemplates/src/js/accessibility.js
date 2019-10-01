if (typeof SproutFormsCheckableInputs === typeof undefined) {
  var SproutFormsCheckableInputs = {};
}

// Manage aria-checked values on Checkbox and Radio Button inputs
SproutFormsCheckableInputs = {
  formId: null,
  form: null,
  checkableInputs: null,

  init: function(settings) {
    this.formId = settings.formId;
    this.form = document.getElementById(this.formId);
    this.checkableInputs = this.form.querySelectorAll('[type=checkbox], [type=radio]');

    this.setAriaCheckedAttributes();
    this.addCheckableInputsEventListeners();
  },

  setAriaCheckedAttributes: function() {
    for (var i = 0; i < this.checkableInputs.length; i += 1) {
      if (this.checkableInputs[i].checked) {
        this.checkableInputs[i].setAttribute("aria-checked", true);
      }
    }
  },

  addCheckableInputsEventListeners: function() {
    for (var i = 0; i < this.checkableInputs.length; i += 1) {
      this.checkableInputs[i].addEventListener("click", function(event) {
        this.onCheckableInputChange(event);
      }.bind(this), false);
    }
  },

  onCheckableInputChange: function(event) {

    if (event.target.checked) {

      // Resets all buttons in radio group to false
      if (event.target.getAttribute("type") === "radio") {
        this.resetRadioGroup(event.target);
      }

      event.target.setAttribute("aria-checked", true);
    } else {
      event.target.setAttribute("aria-checked", false);
    }
  },

  resetRadioGroup: function(selectedRadioInput) {

    var radioInputName = selectedRadioInput.getAttribute("name");
    var allRadioInputs = document.querySelectorAll('#' + this.formId + ' [name="' + radioInputName + '"] ');

    for (var i = 0; i < allRadioInputs.length; i += 1) {
      allRadioInputs[i].setAttribute("aria-checked", false);
    }
  }
};