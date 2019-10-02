// Manage aria-checked values on Checkbox and Radio Button inputs
class SproutFormsCheckableInputs {

  constructor(formId) {
    this.formId = formId;
    this.form = document.getElementById(this.formId);
    this.checkableInputs = this.form.querySelectorAll('[type=checkbox], [type=radio]');

    this.setAriaCheckedAttributes();
    this.addCheckableInputsEventListeners();
  }

  setAriaCheckedAttributes() {
    for (let i = 0; i < this.checkableInputs.length; i += 1) {
      if (this.checkableInputs[i].checked) {
        this.checkableInputs[i].setAttribute('aria-checked', 'true');
      }
    }
  }

  addCheckableInputsEventListeners() {
    let self = this;
    for (let i = 0; i < this.checkableInputs.length; i += 1) {
      this.checkableInputs[i].addEventListener('click', function(event) {
        self.onCheckableInputChange(event);
      }.bind(this), false);
    }
  }

  onCheckableInputChange(event) {
    let self = this;
    if (event.target.checked) {
      // Resets all buttons in radio group to false
      if (event.target.getAttribute('type') === 'radio') {
        self.resetRadioGroup(event.target);
      }

      event.target.setAttribute('aria-checked', 'true');
    } else {
      event.target.setAttribute('aria-checked', 'false');
    }
  }

  resetRadioGroup(selectedRadioInput) {
    let radioInputName = selectedRadioInput.getAttribute('name');
    let allRadioInputs = document.querySelectorAll('#' + this.formId + ' [name="' + radioInputName + '"] ');

    for (let i = 0; i < allRadioInputs.length; i += 1) {
      allRadioInputs[i].setAttribute('aria-checked', 'false');
    }
  }
}

window.SproutFormsCheckableInputs = SproutFormsCheckableInputs;