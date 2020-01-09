// Prevent duplicate submissions on front-end form
class SproutFormsDisableSubmitButton {

  constructor(formId) {
    this.formId = formId;
    this.form = document.getElementById(this.formId);

    this.setDuplicateSubmissionEventListener();
  }

  setDuplicateSubmissionEventListener() {
    this.form.addEventListener('submit', function() {
      let buttons = this.querySelectorAll('[type="submit"]');
      buttons.forEach(function(button) {
        button.setAttribute('disabled', 'disabled');
      });
    }, false);
  }
}

window.SproutFormsDisableSubmitButton = SproutFormsDisableSubmitButton;