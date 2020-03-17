/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

// Prevent duplicate submissions on front-end form
class SproutFormsDisableSubmitButton {

  constructor(formId) {
    this.formId = formId;
    this.form = document.getElementById(this.formId);
    this.submitButtons = this.form.querySelectorAll('[type="submit"]');

    this.setDuplicateSubmissionEventListener();
    this.setResetDuplicateSubmissionEventListener();
  }

  // Mark all submit buttons as disabled once a user submits the form
  setDuplicateSubmissionEventListener() {
    let self = this;
    this.form.addEventListener('submit', function() {
      self.submitButtons.forEach(function(button) {
        button.setAttribute('disabled', 'disabled');
      });
    }, false);
  }

  // If any inputs change, make sure all submit buttons are not disabled
  setResetDuplicateSubmissionEventListener() {
    let self = this;
    let inputs = this.form.querySelectorAll('input, select, option, textarea, button, datalist, output');
    inputs.forEach(function(input) {
      input.addEventListener('input', function() {
        self.submitButtons.forEach(function(button) {
          button.removeAttribute('disabled');
        });
      }, false);
    });
  }
}

window.SproutFormsDisableSubmitButton = SproutFormsDisableSubmitButton;