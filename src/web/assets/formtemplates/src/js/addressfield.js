if (typeof SproutFormsAddressField === typeof undefined) {
  var SproutFormsAddressField = {};
}

SproutFormsAddressField = {

  addressFields: null,
  countrySelectFields: null,
  baseInputName: null,

  form: null,

  init: function(settings) {
    this.addressFields = document.querySelectorAll('.sproutfields-address-formfields');
    this.countrySelectFields = document.querySelectorAll('.sprout-address-country-select');

    // No need to continue if we don't have an Address Field
    if (this.addressFields.length === 0 && this.countrySelectFields.length === 0) {
      return false;
    }

    this.getNamespace();
    this.initCountrySelectFields();
  },

  initCountrySelectFields: function() {
    self = this;
    for (var i = 0; i < this.countrySelectFields.length; i++) {
      this.countrySelectFields[i].addEventListener('change', function(event) {
        self.updateFormFields(this);
      });
    }
  },

  updateFormFields: function(countrySelectInput) {
    var self = this;
    this.form = countrySelectInput.closest('form');

    var data = {
      action: 'sprout-base-fields/fields-address/update-address-form-html',
      namespace: this.baseInputName,
      countryCode: countrySelectInput.value,
      overrideTemplatePaths: true
    };

    data[window.csrfTokenName] = window.csrfTokenValue;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
    xhr.setRequestHeader('Accept', 'application/json; charset=utf-8');

    // When the country dropdown changes, update things
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        var response = JSON.parse(xhr.response);
        self.removeCountrySpecificElements();
        var countrySpecificFields = self.form.querySelector('.sprout-address-country-fields');
        countrySpecificFields.innerHTML = response.html;
      }
    };

    var body = Object.keys(data).map(function(key) {
      return encodeURIComponent(key) + '=' + encodeURIComponent(data[key])
    }).join('&');

    xhr.send(body);
  },

  removeCountrySpecificElements: function() {
    var inputs = this.form.querySelectorAll('.sprout-address-onchange-country');

    for (var key in inputs) {
      var elem = inputs[key];

      if (typeof elem.parentNode !== 'undefined') {
        elem.parentNode.removeChild(elem);
      }
    }
  },

  getNamespace: function() {
    // @todo - update to support multiple address fields
    if (this.addressFields.length) {
      this.baseInputName = this.addressFields[0].dataset.namespace;
    }
  },
};