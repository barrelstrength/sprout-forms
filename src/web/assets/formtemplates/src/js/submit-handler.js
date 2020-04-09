/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

/**
 * Manage the form submission process, with events
 */
class SproutFormsSubmitHandler {

  constructor(formId, settings = {}) {
    this.formId = formId;
    this.form = document.getElementById(this.formId);
    this.submitButtons = this.form.querySelectorAll('[type="submit"]');
    this.messageBoxId = this.formId + '-message-box';

    this.successMessageClass = settings.successMessageClass ?? 'sproutforms-message-success';
    this.errorMessageClass = settings.errorMessageClass ?? 'sproutforms-message-errors';
    this.failureMessage = settings.failureMessage ?? 'Submission failed';

    this.messageElement = settings.messageElement ?? 'div';
    this.errorsContainerElement = settings.errorsContainerElement ?? 'ul.errors';
    this.errorsItemElement = settings.errorsItemElement ?? 'li';

    // The id used to identify a specific field
    // Fields are targeted via their dynamic field handle: id="fields-{fieldHandle}-field"
    this.fieldWrapperIdPrefix = settings.fieldWrapperIdPrefix ?? 'fields-';
    this.fieldWrapperIdSuffix = settings.fieldWrapperIdSuffix ?? '-field';

    // The selector used to identify the wrapper for all fields
    this.fieldWrapperQuerySelector = settings.fieldWrapperQuerySelector ?? '.field';

    this.addFormSubmitEventListener();
  }

  addFormSubmitEventListener() {
    let self = this;
    self.form.addEventListener('submit', function(event) {
      let targetForm = event.target;
      if (targetForm) {
        event.preventDefault();
        self.handleBeforeFormSubmit();
      }
    }, false);
  }

  /**
   * Manage the workflow before a form is submitted. This promise chain
   * gives users a chance to do something before the submit behavior is
   * run, and throws a cancel event if the submit behavior is aborted.
   */
  handleBeforeFormSubmit() {
    let self = this;
    this.getBeforeFormSubmitPromise()
      .then(self.onFormSubmitEvent.bind(self))
      .then(() => {
        self.handleFormSubmit();
      })
      .catch(self.onFormSubmitCancelledEvent.bind(self));
  }

  /**
   * Manage the form submission workflow. This promise chain
   * dispatches a `sproutFormsSubmit` Event to allow another
   * script to hijack and replace the form submit process, and if
   * no other script takes over, runs the default form submit behavior.
   *
   * This is separated into a separate method so that when another
   * script takes over the submission process (say, Invisible reCAPTCHA because
   * it needs to wait for a callback that takes place outside the async promise
   * chain) it can then call this method to continue the default submit process.
   */
  handleFormSubmit() {
    let self = this;
    this.getFormSubmitPromise()
      .then(self.onAfterFormSubmitEvent.bind(self))
      .catch(self.onFormSubmitCancelledEvent.bind(self));
  }

  /**
   * Give third-party scripts a chance to take actions before the form is submitted
   *
   * Custom events are dispatched on the form element. Event Listeners can:
   * - Use `event.target` to retrieve a copy of the form element
   * - Use `event.preventDefault()` to cancel the default form submission
   * - Use `event.detail.promises.push(yourPromise)` to add a promise to be evaluated
   *
   * Promises added to the `event.detail.promises` array should:
   * - `resolve(true)` to continue the form submission
   * - `resolve(false)` or `reject('whatever')` to cancel the form submission
   *
   * Example Listener:
   * formElement.addEventListener('beforeSproutFormsSubmit', function(event) { ... }, false);
   *
   * @returns {Promise<boolean>}
   */
  getBeforeFormSubmitPromise() {
    let self = this;

    const beforeSproutFormsSubmitEvent = new CustomEvent('beforeSproutFormsSubmit', {
      detail: {
        promises: []
      },
      bubbles: true,
      cancelable: true
    });

    return new Promise((resolve, reject) => {
      if (!self.form.dispatchEvent(beforeSproutFormsSubmitEvent)) {
        // beforeSproutFormsSubmit Event has been cancelled
        return reject(false);
      }

      let promises = beforeSproutFormsSubmitEvent.detail.promises;

      Promise.all(promises)
        .then(function(values) {
          for (const value of values) {
            if (value === false) {
              // A promise added to the beforeSproutFormsSubmit Event returned false
              return reject(false);
            }
          }
        })
        .catch(function() {
          // A promise added to the beforeSproutFormsSubmit Event was rejected
          return reject(false);
        });

      // All beforeSproutFormsSubmit Event listeners and promises returned true
      return resolve(true);
    });
  }

  /**
   * Give third-party scripts a chance to take over the submit behavior.
   *
   * - Use `event.preventDefault()` to cancel the default form submission
   * - Use `event.detail.submitHandler.handleFormSubmit()` to let Sprout Forms
   *   complete the submit behavior using the existing form settings
   *
   * @returns {Promise<boolean>}
   */
  onFormSubmitEvent() {
    let self = this;

    const sproutFormsSubmitEvent = new CustomEvent('onSproutFormsSubmit', {
      detail: {
        submitHandler: self
      },
      bubbles: true,
      cancelable: true
    });

    return new Promise((resolve, reject) => {
      if (!self.form.dispatchEvent(sproutFormsSubmitEvent)) {
        return reject(false);
      }

      return resolve(true);
    });
  }

  /**
   * Submit the form.
   *
   * @returns {Promise<boolean>}
   */
  getFormSubmitPromise() {
    let self = this;

    let submissionMethod = self.form.dataset.submissionMethod;

    return new Promise((resolve) => {
      if (submissionMethod === 'async') {
        self.submitAsync()
      } else {
        self.form.submit();
      }

      resolve(true);
    });
  }

  onAfterFormSubmitEvent() {
    const afterSproutFormsSubmitEvent = new CustomEvent('afterSproutFormsSubmit', {
      bubbles: true
    });

    this.form.dispatchEvent(afterSproutFormsSubmitEvent);
  }

  onFormSubmitCancelledEvent() {
    const cancelSproutFormsSubmitEvent = new CustomEvent('onSproutFormsSubmitCancelled', {
      bubbles: true
    });

    this.form.dispatchEvent(cancelSproutFormsSubmitEvent);
  }

  /**
   * Handle async form submission
   */
  submitAsync() {
    let self = this;

    let xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function() {
      // Only run if the request is complete
      if (xhr.readyState !== 4) {
        return;
      }

      /**
       * @param {Object} response
       * @param {boolean} response.success
       * @param {string} response.errorDisplayMethod
       * @param {string} response.message
       */
      let response = JSON.parse(xhr.responseText);

      if (xhr.status >= 200 || xhr.status < 300) {

        self.removeInlineErrors();

        let oldMessageBox = document.getElementById(self.messageBoxId);
        if (oldMessageBox !== null) {
          oldMessageBox.parentNode.removeChild(oldMessageBox);
        }

        if (response.success) {

          if (response.message) {
            self.displayMessageBox({
              id: self.messageBoxId,
              message: response.message,
              messageClass: self.successMessageClass
            });
          }

          self.form.reset();

        } else {

          let globalErrorsEnabled = response && response.errorDisplayMethod
            ? ['global', 'both'].indexOf(response.errorDisplayMethod) >= 0
            : false;
          let inlineErrorsEnabled = response && response.errorDisplayMethod
            ? ['inline', 'both'].indexOf(response.errorDisplayMethod) >= 0
            : false;

          let globalErrors = [];

          if (globalErrorsEnabled) {
            for (let errors of Object.entries(response.errors)) {
              if (errors[1] !== undefined) {
                globalErrors = [...globalErrors, ...errors[1]];
              }
            }
          }

          let errorListHtml = self.getErrorList(globalErrors);
          if (response.message || errorListHtml) {
            self.displayMessageBox({
              id: self.messageBoxId,
              message: response.message ?? null,
              messageClass: self.errorMessageClass,
              errors: errorListHtml
            });
          }

          // Add inline errors to fields
          if (inlineErrorsEnabled) {
            for (let [fieldHandle, errors] of Object.entries(response.errors)) {
              let fieldId = self.fieldWrapperIdPrefix + fieldHandle + self.fieldWrapperIdSuffix;
              let fieldWrapper = document.getElementById(fieldId);

              // Make sure we don't display two copies of the inline errors box on subsequent requests
              let errorClasses = '.' + self.getTargetElementClasses(self.errorsContainerElement).join('.');
              let oldErrorList = fieldWrapper.querySelector(errorClasses);
              if (oldErrorList) {
                oldErrorList.parentNode.removeChild(oldErrorList);
              }

              fieldWrapper.append(self.getErrorList(errors));
            }
          }
        }

      } else {
        // Something went wrong, response outside the range 200-299
        let errors = {};

        if (typeof response.error === 'string') {
          errors = self.getErrorList([response.error])
        }

        self.displayMessageBox({
          id: self.messageBoxId,
          message: '<p>' + self.failureMessage + '</p>',
          messageClass: self.errorMessageClass,
          errors: errors
        });
      }
    };

    let formData = new FormData(self.form);

    xhr.open('POST', '/');
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.send(formData);
  }

  displayMessageBox(config) {
    let self = this;
    let id = config.id ?? null;
    let message = config.message ?? null;
    let messageClass = config.messageClass ?? '';
    let errors = config.errors ?? null;

    let messageBox = self.getElementWithClasses(self.messageElement);

    if (id) {
      messageBox.setAttribute('id', id);
    }

    messageBox.classList.add(messageClass);
    messageBox.innerHTML = message;

    if (errors) {
      messageBox.append(errors);
    }

    let oldMessageBox = document.getElementById(self.messageBoxId);

    if (oldMessageBox) {
      // If a message box already exists, replace it
      oldMessageBox.parentNode.replaceChild(messageBox, oldMessageBox);
    } else {
      self.form.prepend(messageBox);
    }
  }

  removeInlineErrors() {
    let self = this;

    let classesArray = self.getTargetElementClasses(self.errorsContainerElement);
    let errorListClasses = classesArray.map(cssClass => {
      return '.' + cssClass
    });
    let fields = document.querySelectorAll(self.fieldWrapperQuerySelector);
    for (const field of fields) {
      let oldErrorList = field.querySelector(errorListClasses);
      if (oldErrorList !== null) {
        oldErrorList.parentNode.removeChild(oldErrorList);
      }
    }
  }

  /**
   * Returns a container with a list of errors
   *
   * @param errors
   * @returns {HTMLElement}
   */
  getErrorList(errors) {
    let self = this;
    let unorderedListElement = self.getElementWithClasses(self.errorsContainerElement);
    for (let error of errors) {
      let listElement = self.getElementWithClasses(self.errorsItemElement);
      listElement.innerHTML = error;
      unorderedListElement.appendChild(listElement);
    }

    return unorderedListElement;
  }

  /**
   * Converts a class definition setting string into an element with classes
   *
   * Example:
   * ul.errors => <ul class="errors">
   * div.message.box => <div class="message box">
   *
   * @param value
   * @returns {HTMLElement|null}
   */
  getElementWithClasses(value) {
    let self = this;

    if (typeof value !== 'string') {
      return null;
    }

    let elementName = self.getTargetElementName(value);
    let cssClasses = self.getTargetElementClasses(value);

    if (!elementName) {
      return null;
    }

    let element = document.createElement(elementName);

    if (cssClasses !== null && cssClasses.length) {
      for (const cssClass of cssClasses) {
        element.classList.add(cssClass);
      }
    }

    return element;
  }

  /**
   * Returns the first segment of a class definition setting to be used as the target element
   *
   * @param value
   * @returns {string|null}
   */
  getTargetElementName(value) {
    let parts = value.split('.');
    return parts.length > 0 ? parts[0] : null;
  }

  /**
   * Removes the first segment of  of a class definition setting and returns any additional segments
   * as an array to be used as the target element classes
   *
   * @param value
   * @returns {array|null}
   */
  getTargetElementClasses(value) {
    let parts = value.split('.');
    return parts.length > 1 ? parts.slice(1, parts.length) : null;
  }
}

window.SproutFormsSubmitHandler = SproutFormsSubmitHandler;