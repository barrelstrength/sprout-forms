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

    this.setDuplicateSubmissionEventListeners();
  }

  setDuplicateSubmissionEventListeners() {
    let self = this;

    // Mark all submit buttons as disabled once a user submits the form
    this.form.addEventListener('beforeSproutFormsSubmit', function(event) {
      self.submitButtons.forEach(function(button) {
        button.setAttribute('disabled', 'disabled');
      });
    }, false);

    // Mark all submit buttons as enabled after the form submission is complete
    this.form.addEventListener('afterSproutFormsSubmit', function(event) {
      self.submitButtons.forEach(function(button) {
        // Add slight delay, for kicks
        setTimeout(() => {
          button.removeAttribute('disabled');
        }, 500)

      });
    }, false);

    // Mark all submit buttons as enabled if the form submission is cancelled
    this.form.addEventListener('onSproutFormsSubmitCancelled', function(event) {
      self.submitButtons.forEach(function(button) {
        // Add slight delay, for kicks
        setTimeout(() => {
          button.removeAttribute('disabled');
        }, 500)

      });
    }, false);
  }
}

window.SproutFormsDisableSubmitButton = SproutFormsDisableSubmitButton;