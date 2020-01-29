/*
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

class SproutFormsAddressField {

  constructor(formId) {
    this.formId = formId;
    this.form = document.getElementById(this.formId);

    this.countrySelectFields = this.form.querySelectorAll('.sprout-address-country-select');

    // No need to continue if we don't have any Country Select fields
    if (this.countrySelectFields.length === 0) {
      return;
    }

    this.initCountrySelectFields();
  }

  initCountrySelectFields() {
    let self = this;
    for (let i = 0; i < this.countrySelectFields.length; i++) {
      this.countrySelectFields[i].addEventListener('change', function(event) {
        self.updateFormFields(this);
      });
    }
  }

  updateFormFields(countrySelectInput) {
    let self = this;
    this.form = countrySelectInput.closest('form');
    let baseInputName = countrySelectInput.closest('[data-namespace]').dataset.namespace;

    let data = {
      action: 'sprout-base-fields/fields-address/update-address-form-html',
      namespace: baseInputName,
      countryCode: countrySelectInput.value,
      overrideTemplatePaths: true
    };

    data[window.csrfTokenName] = this.form.querySelector('[name="' + window.csrfTokenName + '"]').value;

    let xhr = new XMLHttpRequest();
    xhr.open('POST', '/', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
    xhr.setRequestHeader('Accept', 'application/json; charset=utf-8');

    // When the country dropdown changes, update things
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        let response = JSON.parse(xhr.response);
        self.removeCountrySpecificElements();
        let countrySpecificFields = self.form.querySelector('.sprout-address-country-fields');
        countrySpecificFields.innerHTML = response.html;
      }
    };

    let body = Object.keys(data).map(function(key) {
      return encodeURIComponent(key) + '=' + encodeURIComponent(data[key])
    }).join('&');

    xhr.send(body);
  }

  removeCountrySpecificElements() {
    let inputs = this.form.querySelectorAll('.sprout-address-onchange-country');
    for (let key in inputs) {
      if (inputs.hasOwnProperty(key)) {
        let elem = inputs[key];
        if (typeof elem.parentNode !== 'undefined') {
          elem.parentNode.removeChild(elem);
        }
      }
    }
  }
}

window.SproutFormsAddressField = SproutFormsAddressField;