/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

// Manage aria-checked values on Checkbox and Radio Button inputs
class SproutFormsCheckableInputs {

  constructor(formId) {
    this.formId = formId;
    this.form = document.getElementById(this.formId);

    this.checkableInputs = this.form.querySelectorAll('[type=checkbox], [type=radio]');
    this.setAriaCheckedAttributes();
    this.addCheckableInputsEventListeners();

    this.requiredCheckboxFields = document.querySelectorAll('.checkboxes.required');
    this.setRequiredCheckboxFieldEventListeners();
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

  setRequiredCheckboxFieldEventListeners() {
    let self = this;
    for (const checkboxField of this.requiredCheckboxFields) {
      // Get all checkbox inputs for a given required Checkboxes field
      let checkboxInputs = checkboxField.querySelectorAll('input[type="checkbox"]');
      for (const checkboxInput of checkboxInputs) {
        checkboxInput.addEventListener('change', function() {
          let isSomethingChecked = self.isSomethingChecked(checkboxInputs);
          self.updateRequiredCheckboxInputAttributes(checkboxInputs, isSomethingChecked);
        }.bind(this), false);
      }
    }
  }

  /**
   * If a single checkbox is checked, a checkbox field satisfies the 'required' criteria
   *
   * @param checkboxInputs
   * @returns {boolean}
   */
  isSomethingChecked(checkboxInputs) {
    for (let i = 0; i < checkboxInputs.length; i++) {
      if (checkboxInputs[i].checked) {
        return true;
      }
    }

    return false;
  }

  /**
   * If a single checkbox is selected, remove all required attributes.
   * If no checkboxes are checked, set all checkbox inputs to required
   *
   * @param checkboxInputs
   * @param isSomethingChecked
   */
  updateRequiredCheckboxInputAttributes(checkboxInputs, isSomethingChecked) {
    for (const checkboxInput of checkboxInputs) {
      if (isSomethingChecked) {
        checkboxInput.removeAttribute('required');
        checkboxInput.removeAttribute('aria-required');
      } else {
        checkboxInput.setAttribute('required', 'true');
        checkboxInput.setAttribute('aria-required', 'true');
      }
    }
  }
}

window.SproutFormsCheckableInputs = SproutFormsCheckableInputs;