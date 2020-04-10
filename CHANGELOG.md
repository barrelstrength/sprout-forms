# Changelog

## 3.9.0.1 - 2020-04-09

### Added
- Added `barrelstrength\sproutbaseemail\base\EmailTemplates::getTemplateMode()`

### Changed
- Updated `barrelstrength/sprout-base-email` requirement to v1.2.8

### Fixed
- Fixed issue where Email Templates may render in the incorrect Template Mode in some scenarios

## 3.9.0 - 2020-04-09 [CRITICAL]

> {note} This is a recommended upgrade and fixes a vulnerability that could occur in some scenarios with custom Email Templates.

> {warning} If your Form Templates use AJAX or any custom javascript please review the [upgrade notes](https://sprout.barrelstrengthdesign.com/docs/forms/installing-and-updating-craft-3.html#upgrading-to-forms-v3-9-0) and see the new [Javascript Events](https://sprout.barrelstrengthdesign.com/docs/forms/javascript-events.html)

### Added
- Added native support for AJAX Form Submissions
- Added 'Submission Method' setting to control form submission behavior
- Added 'Success Message' and 'Error Message' settings to manage global messaging on a per-form basis. Messages support Markdown and rendering values from the submitted form entry. ([#269], [#449])
- Added 'Error Display Method' setting to control whether errors display inline or globally
- Added `beforeSproutFormsSubmit`, `sproutFormsSubmit`, `afterSproutFormsSubmit`, and `onSproutFormSubmitCancelled` javascript events in front-end submit workflow ([#448])
- Added `barrelstrength\sproutbaseemail\base\EmailTemplates::getTemplateRoot()`

### Changed
- Added polyfill for front-end javascript resources to support additional browsers
- Improved front-end JS to make less assumptions about how a user might customize a given form ([#413])
- Updated `disable-submit-button.js` behavior to watch new events and re-enable submit button after submissions
- Updated Email Template rendering to treat Email Template folder as a subfolder of the template folder to allow more flexible use of extending other templates ([#111][#111-sprout-email], [#122][#122-sprout-email])
- Updated `craft.sproutForms.lastEntry()` tag to support more than one form on a page 
- Updated `barrelstrength\sproutbaseemail\base\EmailTemplates::getPath()` to define path within target template folder
- Updated `barrelstrength/sprout-base-email` requirement to v1.2.7
- Updated `barrelstrength/sprout-base-fields` requirement to v1.3.4

### Fixed
- Fixed issue where Default Section segment could accidentally be translated ([#446])
- Fixed issue where for Form-specific 'Enable Captchas' setting was ignored ([#450])
- Fixed issue where accessing Email Dropdown value could throw error if it didn't exist
- Fixed saving Address Field when using Postgres

### Security
- Fixed injection vulnerability

[#269]: https://github.com/barrelstrength/craft-sprout-forms/issues/269
[#413]: https://github.com/barrelstrength/craft-sprout-forms/issues/413
[#446]: https://github.com/barrelstrength/craft-sprout-forms/issues/446
[#448]: https://github.com/barrelstrength/craft-sprout-forms/issues/448
[#449]: https://github.com/barrelstrength/craft-sprout-forms/issues/449
[#450]: https://github.com/barrelstrength/craft-sprout-forms/issues/450
[#111-sprout-email]: https://github.com/barrelstrength/craft-sprout-email/issues/111
[#122-sprout-email]: https://github.com/barrelstrength/craft-sprout-email/issues/122

## 3.8.8 - 2020-03-20

### Added
- Added `barrelstrength\sproutforms\base\FormFieldTrait`
- Added `barrelstrength\sproutforms\fields\formfields\base\BaseConditionalTrait`
- Added `barrelstrength\sproutforms\fields\formfields\base\BaseOptionsConditionalTrait`

### Changed
- Updated FormField base class to use FormFieldTrait
- Updated relations Fields (Categories, Entries, File Upload, Tags, Users) to extend related Craft fields directly and implement `FormFieldTrait`
- Updated options Fields (Checkboxes, Dropdown, Email Dropdown, Multiple Choice, Multi-select) to extend related Craft fields directly and implement `FormFieldTrait`
- Updated `SproutFormsDisableSubmitButton` logic to ensure the submit button is enabled if edits are made to a form ([#411])
- Updated handling of spam in Form Entry Element queries ([#406])
- Updated default for Duplicate and Honeypot Captchas to be disabled on install
- Updated `barrelstrength/sprout-base-email` requirement to v1.2.6

### Fixed
- Fixed bug when loading rule modal if field no longer exists
- Fixed `min` and `max` Form Field input attributes for Number field in Basic Form Templates
- Fixed Notification Email Record properties in Craft 2 to Craft 3 migration
- Fixed Notification Email settings in some Notification Email migration scenarios ([#121][#121-sproutemail], [#125][#125-sproutemail])

### Removed
- Removed `barrelstrength\sproutforms\services\entries\Entries::saveRelations()`

[#406]: https://github.com/barrelstrength/craft-sprout-forms/issues/406
[#411]: https://github.com/barrelstrength/craft-sprout-forms/issues/411
[#121-sproutemail]: https://github.com/barrelstrength/craft-sprout-email/issues/121
[#125-sproutemail]: https://github.com/barrelstrength/craft-sprout-email/issues/125

## 3.8.7 - 2020-03-14

### Added
- Added support for setting default Sort Order and Sort Column on Reports ([#60], [#71])

### Changed
- Updated `barrelstrength/sprout-base-email` requirement to v1.2.5
- Updated `barrelstrength/sprout-base-reports` requirement to v1.4.4

### Fixed
- Fixed bug where `dataSourceBaseUrl` was not defined after editing a report with validation errors
- Fixed display bug when viewing Notification and Report Element Index pages

## 3.8.6 - 2020-03-14

### Changed
- Updated `barrelstrength/sprout-base-email` requirement to v1.2.3

### Fixed
- Fixed bug where spam entries were not respecting `saveSpamToDatabase` setting
- Fixed validation logic in Javascript Captcha 
- Notification Events are no longer initialized on install and migration requests ([#143][#143-sproutemail])
- Notification Events are no longer initialized on console requests

[#143-sproutemail]: https://github.com/barrelstrength/craft-sprout-email/issues/143

## 3.8.5 - 2020-02-27

### Fixed
- Fixed bug where custom fields did not map correctly in Entry Element Integration ([#434])

[#434]: https://github.com/barrelstrength/craft-sprout-forms/issues/434

## 3.8.4 - 2020-02-26

### Fixed
- Fixed bug where File Upload Field would throw `defaultUploadLocation` error when loading an Entry Edit page in the CP ([#432])

[#432]: https://github.com/barrelstrength/craft-sprout-forms/issues/432

## 3.8.3 - 2020-02-20

### Added
- Added support for Report Relations Editor Modal ([#139][#139-sprout-email])

### Changed
- Updated Notification Email logic to only define CC and BCC when sending a Single Email
- Updated instances of `barrelstrength/sproutbasereports/services/getReport()` to default `getElementById()`
- Updated `barrelstrength/sprout-base-email` requirement to v1.2.3
- Updated `barrelstrength/sprout-base-fields` requirement to v1.3.2
- Updated `barrelstrength/sprout-base-reports` requirement to v1.4.2
- Updated `barrelstrength/sprout-base` requirement to v5.1.2

### Fixed
- Fixed display bug with select dropdown field in edit page sidebars ([#139][#139-sprout-email])
- Fixed bug in session logic when defining `viewContext` setting ([#425], [#428])
- Fixed bug where Mailing Lists settings may not display on Notification Email Edit page
- Fixed bug where CC and BCC fields displayed alongside Email List settings even though they don't apply ([#427][#427-sprout-email])

### Removed
- Removed `barrelstrength/sproutbasereports/services/getReport()`

[#139-sprout-email]: https://github.com/barrelstrength/craft-sprout-email/issues/139
[#140-sprout-email]: https://github.com/barrelstrength/craft-sprout-email/issues/140
[#425]: https://github.com/barrelstrength/craft-sprout-forms/issues/425
[#427]: https://github.com/barrelstrength/craft-sprout-forms/issues/427
[#428]: https://github.com/barrelstrength/craft-sprout-forms/issues/428

## 3.8.2 - 2020-02-12

## Added
- Added Date Form Field ([#381])
- Added Users Relations Form Field ([#368], [#369])

### Changed
- Improved validation for Number field ([#414])
- Increased size of initial field drop area
- Updated `barrelstrength/sprout-base-fields` requirement to v1.3.1
- Updated `barrelstrength/sprout-base` requirement to v5.1.1

### Fixed
- Fixed bug where Number field wouldn’t recognize zero as a min or max value
- Fixed bug when generating field handle for custom fields using non-alphanumeric characters

[#368]: https://github.com/barrelstrength/craft-sprout-forms/issues/368
[#369]: https://github.com/barrelstrength/craft-sprout-forms/issues/369
[#381]: https://github.com/barrelstrength/craft-sprout-forms/issues/381
[#414]: https://github.com/barrelstrength/craft-sprout-forms/issues/414 

## 3.8.1 - 2020-02-12

### Changed
- Updated `barrelstrength/sprout-base-email` requirement to v1.2.2

### Fixed
- Fixed incorrect preview link ([#421])

[#421]: https://github.com/barrelstrength/craft-sprout-forms/issues/421

## 3.8.0.2 - 2020-02-06

### Changed
- Updated `barrelstrength/sprout-base-email` requirement to v1.2.1

### Fixed
- Fixed bug where viewContext may not be defined correctly when loading Mailing Lists modal

## 3.8.0.1 - 2020-02-06

### Fixed
- Fixed display bug with Upgrade to Pro button on Entries index page

## 3.8.0 - 2020-02-05

> {note}: This update migrates recipient emails in some conditions from the cc and bcc fields to the standard recipients field. Please review your recipients after updating and ensure they are working as expected.

### Added 
- Improved Form Builder and Field Layout Editor
- Added Page Manager modal to reorder, rename, and delete form pages
- Added status icon and saving spinner to indicate current state of form 
- Added support for Notification Emails to send to Mailing List Reports
- Added settings to show or hide Notifications and Reports tabs
- Added `barrelstrength\sproutforms\elements\Form::pluralDisplayName()`
- Added `barrelstrength\sproutforms\elements\Entry::pluralDisplayName()`

### Changed
- Updated UI to match look and field of Craft 3.4
- Updated tab management to use the Page Manager modal
- Moved several elements of the sidebar navigation to the header 
- Updated models to use `defineRules()` method
- Updated Page deletion behavior to throw error if trying to delete the final Page on a form
- Updated Entry Status settings page to use `Craft.VueAdminTable`
- Updated Notifications integration to redirect to Sprout Email if plugin is installed
- Updated Reports integration to redirect to Sprout Reports if plugin is installed
- Added `dragula` and `dom-autoscroller` assets as dependencies  
- Updated `barrelstrength/sprout-base-email` requirement to v1.2.0
- Updated `barrelstrength/sprout-base-fields` requirement to v1.3.0
- Updated `barrelstrength/sprout-base-reports` requirement to v1.4.0
- Updated `barrelstrength/sprout-base` requirement to v5.1.0

### Fixed
- Fixed several display bugs introduced in Craft 3.4
- Fixed bug where user was unable to save a form with no fields in the field layout
- Fixed bug where saving a form would create a new field layout
- Fixed bug where editing a field too quickly could result in an error
- Fixed bug where editing a field and quickly adding another could result in an error
- Fixed bug where default Entry Status could be deleted

### Removed
- Removed `barrelstrength\sproutforms\validators\FieldLayoutValidator`
- Removed `barrelstrength/sprout-base-lists` requirement (use Mailing List Reports)

## 3.7.1.1 - 2020-01-18

### Fixed 
- Fixed composer release number 

## 3.7.1 - 2020-01-18

### Updated
- Updated `barrelstrength/sprout-base-fields` to v1.2.3

### Fixed 
- Fixed array offset error in PHP 7.4 ([#405][405-sproutbasefields]) 

[405-sproutbasefields]: https://github.com/barrelstrength/craft-sprout-forms/issues/405

## 3.7.0 - 2020-01-17

### Added
- Added `barrelstrength\sproutforms\base\Captcha::$form`

## 3.6.10 - 2020-01-16

### Updated
- Updated `barrelstrength/sprout-base-fields` to v1.2.2

### Fixed 
- Fixed error in address table migration

## 3.6.9 - 2020-01-15

### Added
- Added `barrelstrength\sproutforms\models\EntryStatus::getCpEditUrl()`
- Added `barrelstrength\sproutforms\models\EntryStatus::htmlLabel()`

### Changed
- Updated 'When a form entry is saved' Notification Event to only send notifications if captchas pass validation ([#396])
- Updated `barrelstrength\sproutforms\models\EntryStatus::$isDefault` default value to `false`

### Fixed
- Fixed bug where optional Phone field would not validate with blank value ([#403])
- Fixed bug where 'When a form is saved' Notification Event may not get migrated properly ([#400])
- Fixed bug where custom Entry Status could not be deleted ([#368])

[#368]: https://github.com/barrelstrength/craft-sprout-forms/issues/386
[#396]: https://github.com/barrelstrength/craft-sprout-forms/issues/396
[#400]: https://github.com/barrelstrength/craft-sprout-forms/issues/400
[#403]: https://github.com/barrelstrength/craft-sprout-forms/issues/403

### Removed
- Removed `barrelstrength\sproutforms\records\EntryStatus::getCpEditUrl()`
- Removed `barrelstrength\sproutforms\records\EntryStatus::htmlLabel()`

## 3.6.8 - 2020-01-09

### Updated
- Updated `barrelstrength/sprout-base-fields` to v1.2.1

### Fixed
- Fixed scenario where address table updates may not get triggered in migrations

## 3.6.7 - 2020-01-09

### Added
- Added Disable Submit js for front-end Form Templates
- Added `barrelstrength\sproutbasefields\services\Name`

### Updated
- Updated how Address Fields are saved and retrieved to better handle integrations
- Updated and standardized shared logic, validation, and response for fields Email, Name, Phone, Regular Expression, and Url 
- Updated dynamic email validation to exclude check for unique email setting
- Addresses are now stored only in the `sproutfields_adddresses` table. Updated `barrelstrength\sproutforms\fields\formfields\Address::hasContentColumn` to return false. 
- Added `barrelstrength\sproutbasefields\models\Address::getCountryCode()`
- Updated `barrelstrength\sproutbasefields\services\Address::deleteAddressById()` to require address ID
- Improved fallbacks for Address Field's default country and language
- Moved methods from `barrelstrength\sproutbasefields\helpers\AddressHelper` to `barrelstrength\sproutbasefields\services\Address`
- Moved methods from `barrelstrength\sproutbasefields\helpers\AddressHelper` to `barrelstrength\sproutbasefields\services\AddressFormatter`
- Updated `barrelstrength\sproutbasefields\helpers\AddressHelper` to `barrelstrength\sproutbasefields\services\AddressFormatter`
- Added property `barrelstrength\sproutbasefields\events\OnSaveAddressEvent::$address`
- Deprecated property `barrelstrength\sproutbasefields\events\OnSaveAddressEvent::$model`
- Renamed `barrelstrength\sproutbasefields\services\Address::getAddress()` => `getAddressFromElement()`
- Renamed data attribute `addressid` => `address-id`
- Renamed data attribute `defaultcountrycode` => `default-country-code`
- Renamed data attribute `showcountrydropdown` => `show-country-dropdown`
- Updated `barrelstrength/sprout-base-fields` to v1.2.0
- Updated `commerceguys/addressing` to v1.0.6
- Updated `giggsey/libphonenumber-for-php` to v8.11.1

### Fixed
- Added `site` translation category to Form Templates errors
- Fixed naming conventions in Name field `input` template. Updated input element references to `address` => `name` 
- Fixed js console error when rules are not defined
- Fixed spacing around rule attribute in Form Templates
- Fixed Form Element index settings icon target to open in same page 
- Fixed output of Form Field modal Field Type name setting
- Fixed order of events in uninstall migration
- Fixed display issue with Gibraltar addresses
- Fixed bug where Address input fields did not display in edit modal after Address was cleared

### Removed
- Removed `barrelstrength\sproutforms\fields\formfields\Address::serializeValue()`
- Removed `barrelstrength\sproutbasefields\helpers\AddressHelper`
- Removed `barrelstrength\sproutbasefields\controllers\actionDeleteAddress()`
- Removed `src/templates/_components/fields/formfields/email/settings.twig`
- Removed `src/templates/_components/fields/formfields/name/settings.twig`
- Removed `src/templates/_components/fields/formfields/phone/settings.twig`
- Removed `src/templates/_components/fields/formfields/regularexpression/settings.twig`
- Removed `src/templates/_components/fields/formfields/url/settings.twig`
- Removed `commerceguys/intl`

## 3.6.6 - 2019-12-11

### Fixed 
- Fixed bug where Entries Report did not display correct dates ([#384])

[#384]: https://github.com/barrelstrength/craft-sprout-forms/issues/384

## 3.6.5 - 2019-12-03

### Fixed
- Added ResaveEntries job to ensure all Form Entry Title's get updated when the Title Format setting is changed ([#374])
- Fixed Address Field and Rules javascript behavior in Basic Templates ([#378])
- Updated duplicate field layout cleanup migration ([#377])

[#374]: https://github.com/barrelstrength/craft-sprout-forms/issues/374
[#377]: https://github.com/barrelstrength/craft-sprout-forms/issues/377
[#378]: https://github.com/barrelstrength/craft-sprout-forms/issues/378

## 3.6.4 - 2019-11-22

### Changed
- Improved support for required checkboxes in Accessible Form Templates ([#336])
- Updated Form settings to allow Default Form Templates general setting to be selected
- Improved how default Form Templates are set when creating a new Form
- Updated barrelstrength/sprout-base-reports requirement v1.3.10

### Fixed
- Fixed `allowAdminChanges` requirement when updating Form Groups and Entry Statuses ([#371])
- Fixed migration bug where integrations table could already exist ([#363])
- Fixed issue where Title Format did not get updated after a field handle changed ([#348])
- Fixed bug where Basic Form Template may not get set in Craft 2 to Craft 3 migration
- Fixed bug where deleting a field can throw an error if rule conditions are not set properly
- Fixed bug where Report may not exist when loading Dashboard widget ([#64][64-sprout-reports])
- Fixed bug when running console requests ([#66][66-sprout-reports], [#376])
- Fixed horizontal scroll on some screen sizes ([#67][67-sprout-reports])

[64-sprout-reports]: https://github.com/barrelstrength/craft-sprout-reports/issues/64
[66-sprout-reports]: https://github.com/barrelstrength/craft-sprout-reports/issues/66
[67-sprout-reports]: https://github.com/barrelstrength/craft-sprout-reports/issues/67
[#336]: https://github.com/barrelstrength/craft-sprout-forms/issues/336
[#348]: https://github.com/barrelstrength/craft-sprout-forms/issues/348
[#363]: https://github.com/barrelstrength/craft-sprout-forms/issues/363
[#371]: https://github.com/barrelstrength/craft-sprout-forms/issues/371
[#376]: https://github.com/barrelstrength/craft-sprout-forms/issues/376

## 3.6.2 - 2019-11-20

### Updated
- Improved Form Template comments around use of `modifyForm` hook ([#225])

### Fixed
- Fixed error migrating report ID ([#370])

[#225]: https://github.com/barrelstrength/craft-sprout-forms/issues/225 
[#370]: https://github.com/barrelstrength/craft-sprout-forms/issues/370

## 3.6.1 - 2019-11-19

> {tip} This release adds improved Spam protection workflows, a new Spam Status, and integrated Spam Log reporting. Configure Spam Protection in the settings. Custom Captcha Integrations and Form Templates using conditional field logic may need to updated to address breaking changes. See the upgrade notes: [Upgrading to Sprout Forms v3.6.0](https://sprout.barrelstrengthdesign.com/docs/forms/installing-and-updating-craft-3.html#updating-to-forms-v3-6-0)

### Added
- Added Spam status and additional control over management workflow
- Added Spam Log to Failed Captchas an error messages when a Form Entry is flagged as spam
- Added Failed Captchas to sidebar of Entry Edit page
- Added Spam filter to Entries Element Index page 
- Added 'Mark as Default Status' Element Action
- Added 'Mark as Spam' Element Action
- Added 'Save Spam to the database' setting
- Added 'Spam Redirect Behavior' setting to control where a user gets redirected when an entry is flagged as spam 
- Added 'Spam Limit' setting to control how many Spam Entries to track in the database 
- Added 'Cleanup Probability' setting to set the probability the Spam cleanup task will run
- Added 'Track Remote IP' setting 
- Added 'Default Section' setting to set the initial page to _Forms_ or _Entries_
- Added 'Referrer' tracking to Entries
- Added 'Entry Status' setting to _Entries Data Source_ to limit reports by status
- Added 'Status Name', 'IP Address', 'Referrer', and 'User Agent' to _Entries Data Source_ reports
- Added 'Enable Captchas' setting for individual Forms
- Added `Entry::getCaptchaErrors()` method to retrieve all Failed Captcha error messages 
- Added default translation file

### Changed
- Updated Captcha API (See upgrade notes in docs)
- Updated where Captchas run validation from Event `OnBeforeSaveEntryEvent` to `OnBeforeValidateEntryEvent`
- Updated Entry queries to use native Element `status`
- Updated All Form and Individual Form sources to exclude Spam on Entries Element Index page
- Updated 'Save Data' Form setting to only display when the global Save Data setting is enabled
- Moved all Form settings to Form Settings
- Added new _Templates_ tab in Form Settings
- Updated navigation sidebar to hide _Entries_ tab if 'Save Data' setting is disabled 
- Updated generic class name used in conditional logic: `hidden` => `sprout-hidden` ([#354])
- Renamed Data Source _Submission Log_ to _Integrations Log_
- Updated IP Address tracking to use 'Remote IP' instead of 'User IP' 
- Updated `saveData` and `displaySectionTitles` columns to not allow null values
- Updated barrelstrength/sprout-base requirement to v5.0.8
- Updated barrelstrength/sprout-base-email requirement to v1.1.6

### Fixed
- Fixed "Save as new Form" behavior ([#360])
- Fixed check for schema version in migration ([#355])
- Fixed deprecation warning ([#357])
- Updated Integration settings to get updated when a field handle is changed
- Updated Field Rule settings to get updated when a field handle is changed
- Updated Field Rule settings to be removed when a Field is deleted

### Removed
- Removed `Entry::statusHandle` attribute
- Removed `getSpamStatusHandle` variable 
- Removed the `fakeIt` and isValid properties from the `OnBeforeValidateEntryEvent` in favor of new Spam Redirect Behavior setting
- Removed `fakeIt` from `OnBeforeSaveEntryEvent` in favor of Spam Redirect Behavior setting

[#354]: https://github.com/barrelstrength/craft-sprout-forms/issues/354
[#355]: https://github.com/barrelstrength/craft-sprout-forms/issues/355
[#357]: https://github.com/barrelstrength/craft-sprout-forms/issues/357
[#360]: https://github.com/barrelstrength/craft-sprout-forms/issues/360

## 3.5.1 - 2019-10-17

### Fixed
- Fixed migration bug where Form Template setting was reset in migration
- Fixed migration bug where Save Data setting was reset in migration
- Fixed a bug where saving the Form settings redirected to an incorrect edit URL

## 3.5.0 - 2019-10-10

> {tip} IF: you upgrade, THEN: Conditional Fields! Projects using Custom Form Templates or extending Sprout Forms in other custom ways should read the upgrade notes before upgrading: [Changes in Sprout Forms v3.5.0](https://sprout.barrelstrengthdesign.com/docs/forms/installing-and-updating-craft-3.html#upgrading-to-forms-v3-5-0) 

### Added
- Added Field Rules
- Added `Is` and `IsNot` Conditions
- Added `Contains` and `DoesNotContain` Conditions
- Added `StartsWith` and `DoesNotStartWith` Conditions
- Added `EndsWith` and `DoesNotEndWith` Conditions
- Added `IsProvided` and `IsNotProvided` Conditions
- Added `IsChecked` and `IsNotChecked` Conditions
- Added `IsGreaterThan` and `IsLessThan` Conditions
- Added `IsGreaterThanOrEqualTo` and `IsLessThanOrEqualTo` Conditions
- Added `rules` data attribute to Form Templates
- Added Field Rule support for custom Form Fields

### Changed
- Added Form Edit Settings page to manage several Form Settings with more breathing room
- Updated Integrations to be edited on the Form Edit Settings page
- Updated Basic Email Template to exclude fields hidden by Field Rules
- Updated front-end js resources to use files and only require initialization from main form template
- Updated Javascript Captcha to ensure initialization runs after page load
- Updated script elements in Accessible Form Templates to use `{% js %}` tag 
- Renamed `barrelstrength\sproutforms\elements\Form::templateOverridesFolder` => `barrelstrength\sproutforms\elements\Form::formTemplate`
- Renamed `barrelstrength\sproutforms\models\Settings::saveDataByDefault` => `barrelstrength\sproutforms\models\Settings::enableSaveDataDefaultValue`
- Renamed `barrelstrength\sproutforms\models\Settings::templateFolderDefaultValue` => `barrelstrength\sproutforms\models\Settings::formTemplateDefaultValue`

### Removed
- Removed `barrelstrength\sproutforms\web\twig\variables\getIntegrationById()`
- Removed `barrelstrength\sproutforms\elements\Form::deleteById()`
- Removed `barrelstrength\sproutforms\models\Settings::enablePerFormTemplateFolderOverride`
- Removed `barrelstrength\sproutforms\models\Settings::enableSaveDataPerFormBasis` 
- Removed `barrelstrength\sproutforms\models\Settings::enableIntegrationsPerFormBasis` 

### Fixed
- Update Opt-in field to add required attribute when required ([#336])

[#336]: https://github.com/barrelstrength/craft-sprout-forms/issues/336

## 3.4.3 - 2019-09-26

### Added
- Added support to map Name fields in Integrations
- Added support to map Opt-in fields in Integrations
- Added template hook `cp.sproutForms.forms.edit.content` ([#339][339-pull-request])
- Added template hook `cp.sproutForms.forms.edit.details` ([#339][339-pull-request])
- Added template hook `cp.sproutForms.entries.edit.content` ([#339][339-pull-request])
- Added template hook `cp.sproutForms.entries.edit.details` ([#339][339-pull-request])

### Changed
- Updated craftcms/cms requirement to v3.3.0

### Fixed
- Fixed deprecation error in default Email Template when using Relations fields. ([#90])
- Fixed bug introduced in Craft v3.3.0 where File Upload field doesn't update Asset Source option ([#343])
- Fixed status filter behavior ([#339][339-pull-request])
- Fixed bug where it was not possible to create new Entry Statuses using Postgres

[#90]: https://github.com/barrelstrength/craft-sprout-forms/issues/90
[#343]: https://github.com/barrelstrength/craft-sprout-forms/issues/343
[339-pull-request]: https://github.com/barrelstrength/craft-sprout-forms/pull/339

## 3.4.2 - 2019-09-04

### Changed
- Updated barrelstrength/sprout-base-reports requirement to v1.3.8

### Fixed
- Fixed bug where field classes did not display in Basic Form Templates ([#335])
- Fixed migration bug where `viewContext` column may not be found 

[#335]: https://github.com/barrelstrength/craft-sprout-forms/issues/335

## 3.4.1 - 2019-08-26

> {note} This release updates the Integrations API. Users with Custom Integrations will want to be sure to read the [upgrade notes](https://sprout.barrelstrengthdesign.com/docs/forms/installing-and-updating-craft-3.html#upgrading-to-forms-3-4-1) as some updates may be required to existing Integration classes.

### Changed
- Refactored Integrations API for additional flexibility
- Improved performance of Form element retrieval
- Added base Integration `getIndexedFieldMapping` method
- Updated Craft.SproutForms.Integration to handle more custom integration scenarios
- Updated base Integration `successMessage` to be translatable
- Moved `getFieldsAsOptionsByRow`, `getCompatibleFields`, and `getTargetIntegrationFieldsAsMappingOptions` from `IntegrationsController` to `EntryElementIntegration` class
- Renamed `getFormFieldsAsMappingOptions` => `getSourceFormFieldsAsMappingOptions`
- Renamed `actionGetElementEntryFields` => `actionGetTargetIntegrationFields` and updated it to instantiate and populate an Integration dynamically and trigger the `getTargetIntegrationFieldsAsMappingOptions` on a given Integration
- Renamed `prepareFieldMapping` => `refreshFieldMapping` and moved to base Integration class init method
- Renamed `resolveFieldMapping` => `getTargetIntegrationFieldValues` and moved to base Base Integration class
- Renamed IntegrationTrait `entry` => `formEntry`
- Removed `prepareFieldTypeSelection` and `prepareIntegrationTypeSelection` variables and methods and simplified how Integration Types field is populated in modal templates
- Removed base Integration `updateTargetFieldsAction` and `updateSourceFieldsAction` dependencies in favor of `updateTargetFieldsOnChange` to allow Integrations to dynamically target fields to watch for changes

### Fixed
- Fixed bug loading Javascript Captcha on the front-end

## 3.4.0 - 2019-08-24

### Changed
- Updated form templates and Javascript Captcha to use Craft-supported js tags ([#327])
- Updated OnBeforeValidateEntryEvent to include Form Entry model ([#324])
- Updated barrelstrength/sprout-base requirement 5.0.7
- Updated barrelstrength/sprout-base-email requirement v1.1.5
- Updated barrelstrength/sprout-base-fields requirement v1.1.0
- Updated barrelstrength/sprout-base-reports requirement v1.3.7

### Fixed
- Fixed issue when running migrations via console command ([#321])
- Fixed bug where `pluginHandle` column may not be found in Data Sources migration ([#315], [#318])
- Fixed bug where unique email field setting did not exclude soft deleted entries ([#328])
- Fixed bug where Save Data setting appears when it should not ([#323])
- Fixed bug where 'View Reports' permission did not allow a user to export reports ([#325])

[#315]: https://github.com/barrelstrength/craft-sprout-forms/issues/315
[#318]: https://github.com/barrelstrength/craft-sprout-forms/issues/318
[#321]: https://github.com/barrelstrength/craft-sprout-forms/issues/321
[#323]: https://github.com/barrelstrength/craft-sprout-forms/issues/323
[#324]: https://github.com/barrelstrength/craft-sprout-forms/issues/324
[#325]: https://github.com/barrelstrength/craft-sprout-forms/issues/325
[#327]: https://github.com/barrelstrength/craft-sprout-forms/issues/327
[#328]: https://github.com/barrelstrength/craft-sprout-forms/issues/328

## 3.3.9 - 2019-07-26

### Changed
- Updated barrelstrength/sprout-base-reports requirement v1.3.5

## 3.3.8 - 2019-07-26

### Fixed
- Fixed bug in C2 to C3 Notification Email Element migration ([#318])

[#318]: https://github.com/barrelstrength/craft-sprout-forms/issues/318

## 3.3.7 - 2019-07-17

### Fixed
- Fixed bug in C2 to C3 Notification Email Element migration ([#318])

[#318]: https://github.com/barrelstrength/craft-sprout-forms/issues/318

## 3.3.6 - 2019-07-17

### Changed
- Updated barrelstrength/sprout-base-email requirement v1.1.3

### Fixed
- Fixed bug where `pluginHandle` not found in C2 to C3 Notification Email Element migration ([#318])
- Fixed bug where Save as New Form threw an error ([#313])

[#313]: https://github.com/barrelstrength/craft-sprout-forms/issues/313
[#318]: https://github.com/barrelstrength/craft-sprout-forms/issues/318

## 3.3.5 - 2019-07-16

### Added
- Added Integration Send Rule setting for fine-grained control over Notification Email logic

### Changed
- Improves Notification Email integration support
- Improves Data Source integration support
- Updated barrelstrength/sprout-base-email requirement v1.1.2
- Updated barrelstrength/sprout-base-reports requirement to 1.3.4

## 3.3.4 - 2019-07-14

### Added
- Added Integration Send Rule setting for fine-grained control over opt-in logic

### Changed
- Updated barrelstrength/sprout-base-fields requirement v1.0.9
- Updated barrelstrength/sprout-base-import requirement v1.0.5
- Updated barrelstrength/sprout-base-reports requirement to 1.3.2

## 3.3.3 - 2019-07-09

### Fixed
- Fixed display bug where large numbers of Tabs on Entries Edit page did not scroll ([#309])
- Fixed issue where users could not edit fields with Edit Forms permission ([#310])

[#309]: https://github.com/barrelstrength/craft-sprout-forms/issues/309
[#310]: https://github.com/barrelstrength/craft-sprout-forms/issues/310

## 3.3.2 - 2019-07-09

### Changed
- Updated Report Name to be dynamic
- Updated barrelstrength/sprout-base-reports requirement to 1.3.1

### Fixed
- Fixed display bug where Report column headers could be incorrect width
- Fixed display bug where Report column header order arrow would repeat in Safari

## 3.3.1 - 2019-07-09

### Added
- Added support for Craft 3.2 allowAnonymous updates

### Changed
- Updated craftcms/cms requirement to v3.2.0
- Updated barrelstrength/sprout-base-fields requirement to 1.0.8

## 3.3.0 - 2019-07-03

> {tip} This release adds a new, interactive results page for your reports including search, ordering columns, and pagination. Enjoy!

### Added
- Added support for search, ordering columns, and pagination on results pages

### Changed
- Updated barrelstrength/sprout-base-reports requirement to 1.3.0

### Fixed
- Fixed bug when retrieving values from the database for the Invisible field. ([#304])
- Fixed bug where `Entry::getForm()` could return null ([#306])
- Fixed bug where Form ID was not being set on Custom Endpoint Integration migration

[#304]: https://github.com/barrelstrength/craft-sprout-forms/issues/304
[#306]: https://github.com/barrelstrength/craft-sprout-forms/issues/306

## 3.2.4 - 2019-07-01

### Fixed
- Fixed Editions migration ([#307])

## 3.2.3 - 2019-07-01

### Changed
- Updated Editions migration ([#307])

[#307]: https://github.com/barrelstrength/craft-sprout-forms/pull/307

## 3.2.2 - 2019-06-28

### Changed
- Updated barrelstrength/sprout-base-email requirement to v1.1.1
- Updated barrelstrength/sprout-base-reports requirement to 1.2.1

### Fixed
- Fixed bug where Edition setting was incorrect after updating to Sprout Forms 3.2 ([#286], [301])
- Fixed bug where Data Source grouping could cause reports to disappear from the UI ([#297], [#286])
- Fixed bug when previewing a Notification Email ([#119][119-sprout-email])
- Fixed bug where deleting notification redirected to incorrect URL ([#294])

[119-sprout-email]: https://github.com/barrelstrength/craft-sprout-email/issues/119
[#286]: https://github.com/barrelstrength/craft-sprout-forms/issues/286
[#294]: https://github.com/barrelstrength/craft-sprout-forms/issues/294
[#297]: https://github.com/barrelstrength/craft-sprout-forms/issues/297
[#301]: https://github.com/barrelstrength/craft-sprout-forms/issues/301

## 3.2.1 - 2019-06-25

### Changed
- Added fieldtype class to base integration
- Updated upgrade messaging and buttons

### Changed
- Updated barrelstrength/sprout-base requirement to v5.0.4

## 3.2.0 - 2019-06-24

### Added
- Added support for full-featured, single form, Lite Edition

### Changed
- Updated Captcha checks to take place before other event handles for `barrelstrength\sproutforms\elements\Entry::EVENT_BEFORE_SAVE` Event ([#295], [#298])
- Updated barrelstrength/sprout-base requirement to v5.0.3

[#295]: https://github.com/barrelstrength/craft-sprout-forms/pull/295
[#298]: https://github.com/barrelstrength/craft-sprout-forms/issues/298

## 3.1.0 - 2019-06-17

> {tip} New Form Integrations feature provides extensible, user-friendly interface to send Form data to custom endpoints (CRM, Mailing List, etc.) or create Elements within Craft. Add multiple Integrations to a single form, log success and failure messages, and monitor your form submissions with Reports or Notifications.  

### Added
- Added Integration API
- Added Custom Endpoint Integration
- Added Entry Element Integration

### Changed
- Improved Data Source management and registration
- Updated barrelstrength/sprout-base-reports requirement v1.2.0
- Updated barrelstrength/sprout-base-fields requirement v1.0.7
- Updated barrelstrength/sprout-base requirement to v5.0.1
- Removed Payload Forwarding in favor of Custom Endpoint Integration

### Fixed
- Fixed bug where deleting a form does not delete entries

## 3.0.2 - 2019-06-11

### Fixed
- Fixed bug where DB prefix was not properly supported in Entry Reports ([#288])

[#288]: https://github.com/barrelstrength/craft-sprout-forms/issues/288

## 3.0.1 - 2019-06-11

### Changed
- Updated barrelstrength/sprout-base-email requirement to v1.1.0

### Fixed
- Fixed issue where some Notification Emails would not get triggered ([#238])

[#238]: https://github.com/barrelstrength/craft-sprout-forms/issues/283

## 3.0.0 - 2019-06-10

### Added
- Added Date Range Report export setting
- Added Markdown support for Notification Email Default Body field

### Changed
- Updated barrelstrength/sprout-base-email requirement to v1.0.9
- Updated barrelstrength/sprout-base-reports requirement to v1.0.7

### Fixed
- Fixed bug where new Notifications could throw error if Notification Event was not set ([#285],[#283])

[#283]: https://github.com/barrelstrength/craft-sprout-forms/issues/283
[#285]: https://github.com/barrelstrength/craft-sprout-forms/issues/285

## 3.0.0-beta.57 - 2019-05-16

### Fixed
- Fixed issue where Form Entries results were based on incorrect Element ID match 

## 3.0.0-beta.56 - 2019-05-15

### Fixed
- Fixed issue where deleted relations would throw an error in Entries Report

## 3.0.0-beta.55 - 2019-05-15

### Fixed
- Fixed issue where deleted entries would throw an error in Entries Report

## 3.0.0-beta.54 - 2019-05-06

### Fixed
- Fixed options support in Form Entries Data Source

## 3.0.0-beta.53 - 2019-05-06

### Added
- Added support for Relations fields in Reports ([#253])

### Changed
- Updated Relations fields to order related items alphabetically by default ([#270])

### Fixed
- Fixed bug in Relations field queries and Postgres support
- Fixed label display bug

[#253]: https://github.com/barrelstrength/craft-sprout-forms/issues/253
[#270]: https://github.com/barrelstrength/craft-sprout-forms/issues/270

## 3.0.0-beta.52 - 2019-04-21

### Fixed
- Fixed missing migration for updated Opt-in Field settings ([#268])
- Fixed display issue with required labels ([#271])

[#271]: https://github.com/barrelstrength/craft-sprout-forms/issues/271
[#268]: https://github.com/barrelstrength/craft-sprout-forms/issues/268


## 3.0.0-beta.51 - 2019-04-20

### Changed
- Updated barrelstrength/sprout-base-email requirement to v1.0.6
- Updated barrelstrength/sprout-base-fields requirement v1.0.4
- Updated barrelstrength/sprout-base-reports requirement to v1.0.4
- Updated barrelstrength/sprout-base requirement v5.0.0

### Fixed
- Improved Postgres support
- Fixed javascript error on Internet Explorer

## 3.0.0-beta.50 - 2019-04-10

### Changed
- Improved permission handling for Reports
- Improved support for Postgres
- Added check for errors on the OnBeforeSaveEntryEvent ([#263])
- Updated barrelstrength/sprout-base-email requirement to v1.0.5
- Updated barrelstrength/sprout-base-reports requirement to v1.0.3
- Updated barrelstrength/sprout-base requirement v4.0.8

### Fixed
- Fixed template output for Address Field ([#266])
- Improved support for default Project Config settings when installing the plugin ([#254])
- Fixed behavior of permissions around Notifications and Report tabs
- Fixed bug where could not set 'Reply To' value dynamically ([#247])
- Fixed bug where Sprout Lists integration was not being recognized for Notification Emails ([#106][106-sprout-email])

[#247]: https://github.com/barrelstrength/craft-sprout-forms/issues/247
[#254]: https://github.com/barrelstrength/craft-sprout-forms/issues/254
[#263]: https://github.com/barrelstrength/craft-sprout-forms/issues/263
[#266]: https://github.com/barrelstrength/craft-sprout-forms/issues/266
[106-sprout-email]: https://github.com/barrelstrength/craft-sprout-email/issues/106

## 3.0.0-beta.49 - 2019-03-19

### Fixed
- Fixed issue when dragging and dropping a field to a form ([#255], [#260])
- Fixed TypeError in migration ([#259], [#258])

[#255]: https://github.com/barrelstrength/craft-sprout-forms/issues/255
[#258]: https://github.com/barrelstrength/craft-sprout-forms/pull/258
[#259]: https://github.com/barrelstrength/craft-sprout-forms/issues/259
[#260]: https://github.com/barrelstrength/craft-sprout-forms/pull/260

## 3.0.0-beta.48 - 2019-03-19

### Changed
- Improved performance of several Element queries
- Updated barrelstrength/sprout-base-email requirement to v1.0.4
- Updated barrelstrength/sprout-base-reports requirement to v1.0.2

### Fixed
- Fixed bug where Settings model was not available for Email integration ([#261])
- Fixed bug where Settings model was not available for Report integration

[#261]: https://github.com/barrelstrength/craft-sprout-forms/issues/261

## 3.0.0-beta.47 - 2019-03-18

> {warning} If your site uses custom Form Fields, Form Templates, or Captchas be sure to confirm those custom integrations work with the latest version of Sprout Forms before updating on a live site. Return Type hints have been added to several base classes and require commensurate changes in custom integrations.

### Added
- Added additional permissions support including permissions for Notifications and Reports

### Changed
- Updated settings to require Admin permission to edit
- Updated Report export naming to use toString method ([#9][9-sprout-base-reports])
- Updated barrelstrength/sprout-base-email requirement to v1.0.3
- Updated barrelstrength/sprout-base-reports requirement to v1.0.1
- Updated barrelstrength/sprout-base requirement v4.0.7

### Fixed
- Added Report Element migration ([#44][44-sprout-reports])
- Fixed TypeError in migration ([#259])

[9-sprout-base-reports]: https://github.com/barrelstrength/craft-sprout-base/pull/9
[44-sprout-reports]: https://github.com/barrelstrength/craft-sprout-reports/issues/44
[#259]: https://github.com/barrelstrength/craft-sprout-forms/issues/259

## 3.0.0-beta.46 - 2019-03-13
> {warning} This is a critical release. Please update to the latest to ensure your Address Field Administrative Area code data is being saved correctly.

### Changed
- Updated barrelstrength/sprout-base-fields requirement v1.0.3

### Fixed
- Fixed bug where Administrative Area Input was not populated correctly ([#85][85-sprout-fields])

[85-sprout-fields]: https://github.com/barrelstrength/craft-sprout-fields/issues/85

## 3.0.0-beta.45 - 2019-02-26

### Changed
- Updated craftcms/cms requirement to v3.1.15
- Updated barrelstrength/sprout-base-fields requirement v1.0.1

### Fixed 
- Fixed Address Field settings that blocked field from being saved in Postgres and Project Config ([#77][77-sprout-fields], [#81][81-sprout-fields])
- Fixed bug where Address Table was not created on new installation

[77-sprout-fields]: https://github.com/barrelstrength/craft-sprout-fields/issues/77
[81-sprout-fields]: https://github.com/barrelstrength/craft-sprout-fields/issues/81

## 3.0.0-beta.44 - 2019-02-18

> {warning} This release includes updates to the default Notification Email Templates and updates to what variables are defined by default for the Hidden and Invisible Fields. Please be sure to review your custom Form implementations if you use these features and ensure everything is working as you'd like.

### Added
- Added support for Markdown and custom true/false values for Opt-in Field ([#216])
- Added support for Opt-in Field in Basic Form Template

### Changed
- Improved support for Address, Name, Phone, and Opt-in Fields in Notification Email Template ([#239])
- Added `striptags` filter to field values being dynamically output in Notification Email ([#227])
- Updated default `addFieldVariables` in base Form Templates to only include the most common variables from the Craft `_context`: `craft`, `now`, `currentSite`, `currentUser`, `siteName`, `siteUrl`, `systemName`
- Updated Email Template to hide plainInput fields in Notification Emails ([#240])
- Updated _When a Form Entry is saved_ event to return null and improved logic in Email Template to handle test emails where no mock Entry could be found
- Updated barrelstrength/sprout-base-email requirement v1.0.1

### Fixed
- Fixed scenario where `addFieldVariables` was called in Form Templates more than once
- Fixed labels and wrapper div in Opt-in Field template ([#235])
- Fixed issue where tab navigation did not scroll with a large number of tabs ([#238])
- Fixed label ids in Name Field template ([#235])
- Fixed false positives that could occur with Notification Email validation ([#100][100email])

[#216]: https://github.com/barrelstrength/craft-sprout-forms/issues/216
[#227]: https://github.com/barrelstrength/craft-sprout-forms/issues/227
[#235]: https://github.com/barrelstrength/craft-sprout-forms/issues/235
[#238]: https://github.com/barrelstrength/craft-sprout-forms/issues/238
[#239]: https://github.com/barrelstrength/craft-sprout-forms/issues/239
[#240]: https://github.com/barrelstrength/craft-sprout-forms/issues/240
[100email]: https://github.com/barrelstrength/craft-sprout-email/issues/100

## 3.0.0-beta.43 - 2019-02-15

### Fixed
- Fixed a Foreign Key issue when migrating from Craft 2 to Craft 3 (drop index needed in a foreign key constraint) ([#234])

## 3.0.0-beta.42 - 2019-02-15

### Fixed
- Fixed a Foreign Key issue when migrating from Craft 2 to Craft 3 ([#234])

[#234]: https://github.com/barrelstrength/craft-sprout-forms/issues/234

## 3.0.0-beta.41 - 2019-02-13

### Fixed
- Added barrelstrength/sprout-base-import requirement v1.0.0

## 3.0.0-beta.40 - 2019-02-13

### Changed
- Added resources previously managed in Sprout Base
- Updated settings to implement SproutSettingsInterface
- Updated barrelstrength/sprout-base requirement to v4.0.6
- Added barrelstrength/sprout-base-email requirement v1.0.0
- Added barrelstrength/sprout-base-fields requirement v1.0.0
- Added barrelstrength/sprout-base-reports requirement v1.0.0

### Fixed
- Fixed bug where it does not display form entries when one of the form is deleted

## 3.0.0-beta.39 - 2019-02-06

### Fixed
- Fixed bug in Craft 3.1 migration ([#226])

[#226]: https://github.com/barrelstrength/craft-sprout-forms/issues/226

## 3.0.0-beta.38 - 2019-01-28

### Fixed
- Fixed error when using Number Fields in Craft 3.1 migration

### Improved
- Improved PostgreSQL compatibility in migrations

## 3.0.0-beta.37 - 2019-01-25

### Added
- Added initial support for Craft 3.1

### Changed
- Updated Craft CMS requirement to v3.1.0
- Updated Sprout Base requirement to v4.0.5

## 3.0.0-beta.36 - 2019-01-23

### Added
- Added International Address Form Field
- Added autocomplete support to base Address Form Field templates

### Changed
- Updated translation filter to use the category 'site' as templates output front-end content and should be translatable on the front-end [#214]
- Improved error message when a Form Field is missing from a Field Layout [#209]
- Updated Entries Relations field from 'Entries (Sprout)' => 'Entries (Sprout Forms)'
- Updated Entries Relations field from 'Forms (Sprout)' => 'Forms (Sprout Forms)'
- Added several assets back to repo that were previously stored in Sprout Base
- Updated barrelstrength/sprout-base to require v4.0.4

### Fixed
- Added placeholder output to regex field [#212]
- Fixed issue where instructions would not output HTML [#208]

[#208]: https://github.com/barrelstrength/craft-sprout-forms/issues/208
[#209]: https://github.com/barrelstrength/craft-sprout-forms/issues/209
[#212]: https://github.com/barrelstrength/craft-sprout-forms/issues/212
[#214]: https://github.com/barrelstrength/craft-sprout-forms/issues/214

## 3.0.0-beta.35 - 2018-12-17

### Fixed
- Fixed issue where Invisible Field was not processing dynamic values on front-end requests ([#205])
- Improved support for migrating from Craft 2 to Craft 3  ([#199], [#204])
- Fixed consistency of Phone field error message ([#201])

[#199]: https://github.com/barrelstrength/craft-sprout-forms/issues/199
[#201]: https://github.com/barrelstrength/craft-sprout-forms/issues/201
[#204]: https://github.com/barrelstrength/craft-sprout-forms/issues/204
[#205]: https://github.com/barrelstrength/craft-sprout-forms/issues/205

## 3.0.0-beta.34 - 2018-11-28

### Changed
- Updated Per-form Form Templates to default to global setting ([#193])
- Updated Sprout Base requirement to v4.0.3

### Fixed
- Fixed email notification logic for Craft 2 to Craft 3 migration ([#198])
- Fixed namespace naming conflict in PHP 7 ([#195])

[#193]: https://github.com/barrelstrength/craft-sprout-forms/issues/193
[#195]: https://github.com/barrelstrength/craft-sprout-forms/issues/195
[#198]: https://github.com/barrelstrength/craft-sprout-forms/issues/198

## 3.0.0-beta.33 - 2018-11-14

### Added
- Field handles now display when hovering over a field

### Changed
- Updated Sprout Base requirement to v4.0.2

## 3.0.0-beta.32 - 2018-11-05

### Added
- Added customize sources support for Form Entries Element Index

### Changed
- Improved migration for Notification Emails ([#189])

### Fixed
- Fixed various Deprecation Warnings ([#184])

[#184]: https://github.com/barrelstrength/craft-sprout-forms/issues/184
[#189]: https://github.com/barrelstrength/craft-sprout-forms/issues/189

## 3.0.0-beta.31 - 2018-10-30

### Changed
- Fixed bug in Email Notifications migration

## 3.0.0-beta.30 - 2018-10-30

### Changed
- Updated Sprout Base requirement to v4.0.1

## 3.0.0-beta.29 - 2018-10-29

### Changed
- Updated Sprout Base requirement to v4.0.0

## 3.0.0-beta.28 - 2018-10-26

### Changed
- Removed `&nbsp;` from Checkboxes and Opt-in field Form Templates [#95]
- Updated Form Field getTemplatesPath to be dynamic [#98]
- Updated Sprout Forms Save Entry Notification Event to return the latest entry as a mock value
- Updated Sprout Base requirement to v3.0.10

### Fixed
- Fixed bug where user was unable to update Opt-in Field message
- Fixed issue where payload was sending empty POST params. [#145] 
- Fixed issue where Email Dropdown field would not render the correct value in Notification Email templates ([#171])
- Fixed bug where Name field would not throw error if it was required and submitted blank [#172]
- Fixed various issues in Notification Email migrations from Craft 2

[#95]: https://github.com/barrelstrength/craft-sprout-forms/issues/95
[#98]: https://github.com/barrelstrength/craft-sprout-forms/issues/98
[#145]: https://github.com/barrelstrength/craft-sprout-forms/issues/145
[#171]: https://github.com/barrelstrength/craft-sprout-forms/issues/171
[#172]: https://github.com/barrelstrength/craft-sprout-forms/issues/172

## 3.0.0-beta.27 - 2018-10-22

### Added
- Added Opt-In Form Field
- Added support for Sprout Google ReCaptcha Spam Protection
- Added `setEntry` variable

### Improved
- Improved front-end form editing the `entry` variable is managed in Form Templates when using the displayForm tag

## 3.0.0-beta.26 - 2018-09-10

### Fixed
- Fixed Changelog link format

## 3.0.0-beta.25 - 2018-09-10

### Changed
- Improved support in PHP 7.2 ([#144])
- Improved Postgres support ([#137], [#158])
- Improved error messaging if Title Format setting includes an incorrect field handle ([#96])
- Updated Sprout Base requirement to v3.0.4

### Fixed
- Fixed Email Dropdown field bug where front-end submissions would save incorrect value in database ([#63])

[#96]: https://github.com/barrelstrength/craft-sprout-forms/issues/63
[#96]: https://github.com/barrelstrength/craft-sprout-forms/issues/96
[#137]: https://github.com/barrelstrength/craft-sprout-forms/issues/137
[#144]: https://github.com/barrelstrength/craft-sprout-forms/issues/147
[#158]: https://github.com/barrelstrength/craft-sprout-forms/issues/158

## 3.0.0-beta.23 - 2018-08-31

> {warning} This is a recommended upgrade.

### Fixed
- Fixed issue where deleting a Form Field could also delete a field with a matching handle in the global context

## 3.0.0-beta.22 - 2018-07-23

### Added
- Added support for lastEntry tag ([#146])
- Added EntriesController::EVENT_BEFORE_VALIDATE ([#136])

### Changed
- Refactored Save Entry workflow ([#135], [#139])
- Improved error message when a Form's Title Format value causes a render error
- Updated Sprout Base requirement to v3.0.2

### Fixed
- Fixed bug where validation would not trigger if data was not being saved in the database ([#135])
- Fixed a bug where creating a field after deleting a new tab resulted in inaccessible fields in the db ([#149])
- Fixed deprecated `includeJs` tags ([#148])
- Fixed javascript parse error on Entry Statuses settings page ([#140])

[#135]: https://github.com/barrelstrength/craft-sprout-forms/issues/135
[#136]: https://github.com/barrelstrength/craft-sprout-forms/issues/136
[#139]: https://github.com/barrelstrength/craft-sprout-forms/issues/139
[#140]: https://github.com/barrelstrength/craft-sprout-forms/issues/140
[#146]: https://github.com/barrelstrength/craft-sprout-forms/issues/146
[#148]: https://github.com/barrelstrength/craft-sprout-forms/issues/148
[#149]: https://github.com/barrelstrength/craft-sprout-forms/issues/149

## 3.0.0-beta.21 - 2018-07-26

### Changed
- Updated Sprout Base requirement to v3.0.0

## 3.0.0-beta.20 - 2018-07-26

### Added
- Added support for `defaultBody` field in Basic Email Template
- Improvements in Notification Emails from Sprout Email v4.0.0-beta.1
- Improvements in Reports from Sprout Reports v1.0.0-beta.11

### Changed
- Updates Form Edit sidebar to use a single column ([#122], [#118])
- Updated Basic Notification Email Template styles
- Updated Sprout Base requirement to v2.0.10

### Fixed
- Added Form and Entry Element Type migration
- Fixed potential syntax error with Form Notification Email template and SaveEntry Event
- Fixed broken styles introduced in recent Craft update ([#122])
- Fixed bug where Sender fields would not validate if using dynamic values ([#124])

[#118]: https://github.com/barrelstrength/craft-sprout-forms/issues/118
[#122]: https://github.com/barrelstrength/craft-sprout-forms/issues/122
[#124]: https://github.com/barrelstrength/craft-sprout-forms/issues/124

## 3.0.0-beta.18 - 2018-07-12

### Fixed
- Fixed bug where checkboxes field would throw error when displaying on Entry page ([#125])

[#125]: https://github.com/barrelstrength/craft-sprout-forms/issues/125#issuecomment-404460226

## 3.0.0-beta.17 - 2018-06-12

### Added
- Added field-specific class name to field wrappers in Form Templates ([#112])
- Added status handle filter to Entry Query

### Fixed
- Fixed behavior of Rendering Options in field override templates ([#103])
- Fixed bug where Paragraph Field Column Type setting did not validate over 255 characters ([#110])
- Fixed bug where the Checkboxes field only saved the value of the final checkbox ([#108])
- Fixed issue where custom fields behaved incorrectly on Form Entry Elements ([#89])
- Fixed error when moving a field between two tabs ([#106])

[#89]: https://github.com/barrelstrength/craft-sprout-forms/issues/89
[#106]: https://github.com/barrelstrength/craft-sprout-forms/issues/106
[#108]: https://github.com/barrelstrength/craft-sprout-forms/issues/108
[#112]: https://github.com/barrelstrength/craft-sprout-forms/issues/112
[#103]: https://github.com/barrelstrength/craft-sprout-forms/issues/103
[#110]: https://github.com/barrelstrength/craft-sprout-forms/issues/110

## 3.0.0-beta.16 - 2018-05-23

### Fixed
- Fixed bug when using custom email template overrides [#102]

[#102]: https://github.com/barrelstrength/craft-sprout-forms/issues/102

## 3.0.0-beta.15 - 2018-05-22

### Added
- Added support for Email Attachments ([#85])

### Fixed
- Added support for tab scrolling when Forms have a large number of tabs ([#97])
- Fixed bug where File Upload field did not upload files if the filename already existed ([#101])

[#85]: https://github.com/barrelstrength/craft-sprout-forms/issues/85
[#97]: https://github.com/barrelstrength/craft-sprout-forms/issues/97
[#101]: https://github.com/barrelstrength/craft-sprout-forms/issues/101

## 3.0.0-beta.14 - 2018-05-18

### Changed
- Updates Sprout Base to v2.0.3

### Fixed
- Fixed reference to Email Dropdown Field Service

## 3.0.0-beta.13 - 2018-05-17

### Fixed
- Fixes release notes warning syntax

## 3.0.0-beta.12 - 2018-05-15

> {warning} If you have more than one Sprout Plugin installed, to avoid errors use the 'Update All' option.

### Added
- Added minVersionRequired as Sprout Forms v2.5.1 ([#92](https://github.com/barrelstrength/craft-sprout-forms/issues/92))

### Changed
- Updated Sprout Email Notification Events to extend new BaseNotificationEvent class
- Updated pattern of Report Edit URL
- Updated folder structure
- Moved schema and component definitions to Plugin class
- Moved templates to Sprout Base
- Moved asset bundles to Sprout Base 

### Fixed
- Fixed several deprecation errors
- Fixed bug when using CLI ([#91](https://github.com/barrelstrength/craft-sprout-forms/issues/91), [#5](https://github.com/barrelstrength/craft-sprout-base/issues/5))
- Fixed bug where form handle casing could cause error during migration ([#84](https://github.com/barrelstrength/craft-sprout-forms/issues/84))
- Fixed Notification exception where parameter did not work in PHP 7.2 ([#86](https://github.com/barrelstrength/craft-sprout-forms/issues/86))   

## 3.0.0-beta.10 - 2018-04-17

### Fixed
- Fixed migration bug where form content table names could be created with improper casing

## 3.0.0-beta.9 - 2018-04-17

### Fixed
- Fixed bug where report data source could return null

## 3.0.0-beta.8 - 2018-04-17

### Fixed
- Fixed bug where report migration was not run for existing Sprout Report installations

## 3.0.0-beta.7 - 2018-04-17

### Added
- Added Notifications powered by Sprout Email
- Added Basic Notification Email Templates integration 
- Added Custom Save Entry Event for Notification Emails  
- Added Reports powered by Sprout Reports
- Added Sprout Forms Entries Data Source

### Fixed
- Fixed bug when updating from Craft 2
- Fixed bug where a Form Entry could not be deleted
- Fixes bug where user was unable to submit a form if not logged in

## 3.0.0-beta.6 - 2018-04-12

### Added
- Added support for `craft.sproutForms.entries` tag
- Added Recent Form Entries Widget
- Added user permissions to Manage, View, and Edit forms

### Fixed
- Fixed various migration bugs  
- Fixed bug where "Save as New Form" did not copy all data
- Fixed bug where Tags field did not allow selecting multiple tags

## 3.0.0-beta.5 - 2018-04-05

### Fixed
- Fixed icon mask display issue

## 3.0.0-beta.4 - 2018-04-04

### Fixed
- Fixed bug where a Form Element would not save properly when using PHP 7.2

## 3.0.0-beta.3 - 2018-04-04

### Fixed
- Fixed Form submission error where Sprout Email was being referenced when not present
- Fixed javascript bug that could occur when Sprout Fields was disabled 

## 3.0.0-beta.2 - 2018-04-01

### Changed
- Improved translation support
- Updated Accessible Form Templates submit button to use <button> tag
- Removed default CSS options from Accessible Templates
- Disabled required option on PrivateNotes, Invisible, and Hidden fields
- Removed references to legacy notification email fields and logic
- Removed legacy example files

### Fixed
- Updated Basic Templates to support new displayTab and displayField methods
- Fixed bug where cssClasses was always true
- Fixed bug where duplicate captcha could allow duplicates
- Fixed bug where missing resources could disable drag and drop if per-form settings were disabled
- Fixed reference to Sprout Base Importers service
- Fixed Name field label identification

## 3.0.0-beta.1 - 2018-03-26

### Added
- Added new Drag & Drop Form Builder user interface
- Added support for Reordering Tabs and moving fields between tabs
- Added Front-end Field API
- Added support for fields from Sprout Forms and Sprout Fields in Craft 2
- Added Section Heading, Private Notes, and Custom HTML Fields
- Added International Name Field with single input and multi-input options and autocomplete support
- Added International Phone Field
- Added autocomplete support for Name, Email, and Phone fields
- Added Form Templates API
- Added Accessible Templates Form Templates which include support for several WCAG 2.0 guidelines 
- Added Captcha API 
- Added support for several Invisible Captcha integrations

### Removed
- Removed Notes Field in favor of Section Heading, Private Notes, and Custom HTML Fields

### Changed
- Updated Plain Text to Single Line Field
- Updated Plain Text multiline to Paragraph Field
- Updated Radio Buttons to Multiple Choice Field
- Updated Assets to File Upload Field
- Removed Phone Field with Pattern Mask

## 2.5.1 - 2017-10-18

### Added
- Added support for the Assets filename in Sprout Forms Entries Report integration
- Added support for $criteria-&gt;formHandle

### Changed
- Improved validation for field handles to include reserved words from Entry Model
- Improved translation support

### Fixed
- Fixed bug where getEntryById could return last entry if entryId was null
- Fixed bug where Amazon S3 files could not be attached to a notification email

## 2.5.0 - 2017-08-30

### Added
- Added `Manage Forms` permission
- Added `View Form Entries` and `Edit Form Entries` permissions

### Fixed
- Fixed bug where Forms could be saved with a duplicate slug

## 2.4.9 - 2017-08-23

### Added
- Added support to retrieve Form Entries by `statusHandle`
- Added translate filter to placeholder text

### Changed
- Updated Sprout Forms Entry Elements to use SproutForms_EntryElement::getFieldsForElementsQuery

## 2.4.2 - 2017-05-25

### Fixed
- Fixed bug where `craft.sproutForms.lastEntry()` would return null

## 2.4.1 - 2017-05-06

### Added
- Added option to disable saving form submission data to the database globally or on a per-form basis
- Added Support for Email Notifications when using Payload Forwarding
- Added hidden config override `sproutForms-&gt;enableEditFormEntryViaFrontEnd` to make forms editable on the front-end

### Changed
- Improved support for static translations on tabs and fields
- Improved default settings on install

### Fixed
- Fixed bug in Sprout Import integration settings

## 2.3.5 - 2017-01-11

### Changed
- The SproutForms_FormModel variable is now available within tab and field templates
- Updated post variable to be passed as a parameter to the SproutForms_FormsService service
- Updated renderObjectTemplate methods to set safe mode to true

### Fixed
- Fixed javascript error that could occur when some fields were marked as required
- Fixed Sprout Import integration bug with how dateCreated was imported to Form Entries Elements

## 2.3.4 - 2016-09-26

### Changed
- Added support for line breaks in default notification email template
- Improved support for Sprout Import

### Fixed
- Fixed errors that could occur on servers running PHP 5.3

## 2.3.2 - 2016-06-07

### Fixed
- Fixed issue on Sprout Reports integration when the form does not exist.
- Fixed issue on Sprout Reports integration when dates are empty.
- Fixed bug on getAllEntries service method.

## 2.3.1 - 2016-05-26

### Fixed
- Fixed casing of entry statuses template folder reference

## 2.3.0 - 2016-05-26

### Added
- Added Form Entry Statuses and customizable status workflow
- Added front-end field support for Entries Relations field
- Added front-end field support for Categories Relations field
- Added front-end field support for Tags Relations field
- Added support for S3 Asset uploads and file attachments
- Added option for third-party form submissions to also save a copy of the submission to the Craft database

### Changed
- Added an advanced settings section to allow customization of which user-facing advanced settings to display
- Improved labeling around custom form template overrides options
- Improved error logging
- Updated form redirect behavior to follow Craft conventions

### Fixed
- Fixed several minor security vulnerabilities
- Added support for Form Entries Explorer chart in PHP 5.3
- Fixed issue where a checkbox field marked as required would require all checkboxes to be selected before validating

## 2.2.6 - 2016-04-20

### Added
- When form validation fails, the displayForm tag now adds focus to the first error in the form

### Changed
- Improved &quot;Save as new form&quot; behavior

### Fixed
- Fixed method signature compatibility error with FieldsService::saveField() method in PHP7
- Fixed deprecation error introduced in Craft 2.6.2779

## 2.2.5 - 2016-04-07

### Added
- Added support for editing fields via the field modal workflow

### Changed
- Improved Sprout Reports integration adding support for updating report options on the fly

### Fixed
- Fixed a bug where deleting a Form via the bulk action dropdown didn&#039;t delete the Form&#039;s related content table
- Fixed issue where Form field input tag data attributes were not wrapped in quotations
- Fixed bug where the Tab name would be repeated before each field in notifications if a Form had more than one tab
- Fixed a bug where Tab names could misbehave if they had blank spaces

## 2.2.3 - 2016-03-31

### Added
- Added Form Entries Explorer Chart
- Added Recent Form Entries Chart dashboard widget

## 2.2.2 - 2016-03-03

### Added
- Added PHP 7 compatibility
- Added support for creating fields via a modal

### Changed
- Improved workflow around creating new fields and tabs
- Improved sending of notifications via the service layer
- Added &#039;Save and continue editing&#039; option in the save form dropdown
- Updated default form save behavior to save and redirect to form index page
- Various code cleanup and improved organization

### Fixed
- Fixed error when displaying fields that were not in the content table
- Added form handle validation

## 2.2.1 - 2016-02-05

### Fixed
- Fixed bug on forms using an Assets field

## 2.2.0 - 2016-02-04

### Added
- Added Recent Form Entries dashboard widget
- Added support for filtering and ordering Form Entries using the `craft.sproutForms.entries` tag

### Changed
- New Forms are now immediately editable

### Fixed
- Fixed broken link in sidebar documentation

## 2.1.5 - 2016-01-13

### Added
- Added support for Form redirects to use relative URLs
- Added support for importing Form and Form Entry Elements using Sprout Import

### Changed
- Improved messaging around spam protection using Sprout Invisible Captcha

## 2.1.4 - 2015-12-31

### Added
- Added upcoming Sprout Reports Form Entries integration

### Fixed
- Fixed Sprout Email integration where a notification email could default to the most recent entry instead of the most recent entry of a particular form type.
- Fixed issue with notification subject line encoding that could  when using PHP Mail protocol.
- Fixed issue where email notification template couldn&#039;t be overridden when using custom template overrides.
- Fixed issue where bulk renaming forms titles failed when some special characters were in the title.

## 2.1.3 - 2015-12-04

### Fixed
- Fixed asset upload bug introduced in Craft 2.5 updates

## 2.1.2 - 2015-12-03

### Fixed
- Fixed redirect issue (404) after installation

## 2.1.1 - 2015-12-02

### Added
- The entire Control Panel has been updated to work with Craft 2.5
- Added Plugin icon
- Added Plugin description
- Added link to documentation
- Added link to plugin settings
- Added link to release feed
- Added subnav in place of tabs for top level navigation
- Added Sprout Migrate support for SproutForms_Form Element Type
- Added Sprout Migrate support for SproutForms_Entry Element Type
- Form Entry titles on existing entries get updated when Title Format is changed
- Field variables used in Title Format will be updated dynamically if field name is changed

### Changed
- Improved and standardized display of Sprout plugin info in footer
- Improved errors and messaging on examples page
- Updated emails to use filter_var for validation
- Updated `SproutForms_FormRecord::validateRecipent()` method to allow twig syntax
- Removed customize source link on Form element index page

### Fixed
- Fixed a bug where setting an id using renderingOptions would output duplicate ids
- Fixed a bug where checkbox field only captured last value
- Fixed a bug where sidebar list stopped showing in Craft 2.5
- Fixed a bug where making field required or removing it would fail in Craft 2.5
- Fixed a bug where Form Entry element types could throw an error on Form Entry
- element index page

## 2.0.0 - 2015-10-20

### Custom Template Overrides
- Designers and front-end developers now have 100% control over every form template. Customize the HTML, CSS, and Javascript used to output your forms, tabs, fields,  error messages, and notification emails.

### Added
- Added [Custom Template Overrides](https://sprout.barrelstrengthdesign.com/craft-plugins/forms/docs/customization/template-overrides) setting to override default form templates with custom templates on a per-form basis.
- Added Custom Template Override support for email notification templates
- Added setting to allow files to be attached to email notifications on a per-form basis (when using Local Asset Sources)
- Template Folder Override form setting defaults to Template Folder Override global setting when a new form is created
- Added support to dynamically set form options in templates using displayForm, displayTab, and displayField tags.
- Added [Front-end Field API](https://sprout.barrelstrengthdesign.com/craft-plugins/forms/docs/customization/custom-front-end-fields)
- Added support for plugins to register one or more custom front-end fields
- Added form.getField() method which returns a complete FieldModel
- Added front-end support for Assets field and single and multiple file submissions
- Added front-end support for Number field with `number` attribute and decimal validation
- Added support for `for` attribute and multiple labels with the Checkboxes and RadioButtons fields
- Added support for `required` attribute on input tags
- Added support for fields that should not display a value on the front-end using SproutFormsBaseField::isPlainInput()
- Added support for fields that need to use multiple labels using SproutFormsBaseField::hasMultipleLabels()
- Added `craft.sproutForms.addFieldVariables()` tag which makes Twig _context, and the option to make additional variables, available to fields. There&#039;s a new field type on the way and you won&#039;t even see it coming!
- Added actionForwardEntry() action to handle form submissions to third-party locations
- Added SproutForms_EntryModel::getPayloadFields() to clean up form fields before forwarding
- Added support for form field validation before forwarding
- Added support for payload error messages to be returned to form just like all other form error messages
- Added `craft.sproutForms.getEntry()` tag which gets an active or new SproutForms_EntryModel.
- Front-end FieldModel now includes the `required` attribute
- Added integration with upcoming Sprout Reports

### Changed
- Improved underlying front-end form templates and removed form macro dependencies
- Migrated all supported front-end fields to use the Front-end Field API
- Removed `enableTemplateOverrides` and `enableFileAttachments` config settings as they are no longer needed
- Moved several Form settings from the _Overview_ tab to a new _Advanced_ settings tab.
- Renamed Form settings _Overview_ tab to _Settings_
- Renamed plugin settings _Control Panel_ tab to _Settings_

### Front-end Field API
- Developers can now add field type support for front-end forms as easily as they can add support for fields in Craft. Sprout Forms currently supports seven Standard Fields. Additional support can be added for native or custom field types via the Front-end Field API.

### Payload Forwarding
- Manage forms that submit your data to third-party endpoints. Enjoy the benefits of the Sprout Forms form builder and integration with Craft relations and validation while sending your data someplace else (for example, to have tighter integration between your website and CRM or to meet specific data security requirements).

## 1.1.0 - 2015-08-17

### Added
- Added the ability to delete entries via bulk actions
- Added the ability to sort entries by the number of fields
- Added the ability to sort entries by the number of total entries
- Added the ability to duplicate a form via Save as new form option

### Changed
- Improved breadcrumbs and save button styles

### Fixed
- Fixed an issue that occurred while deleting a field from duplicated form

## 1.0.3 - 2015-07-01

### Fixed
- Fixes logging error in migration

## 1.0.2 - 2015-06-29

### Changed
- Updated naming conventions of rules options

## 1.0.1 - 2015-06-27

### Changed
- Updated onSaveEntry event to take place after context switching
- Updated Sprout Forms Save Entry event input name to avoid collisions

## 1.0.0 - 2015-05-13

### Added
- Commercial Release

## 0.9.1 - 2015-05-07

### Added
- Adds the ability for a Sprout Email users to trigger notifications when a form entry is saved

## 0.9.0 - 2015-04-24

### Added
- Added conditional validation to Form Notification fields
- Added craft.sproutForms.getForm() tag
- Added `has-errors` class to field container of form output
- Added example US English translation file

### Changed
- Form Entries now display in the default order of most recent first
- Updated Form Entry page to display fields by tab
- Improved organization of templates and code
- Updated Field Name instructions to indicate usage on front-end as well

### Fixed
- Removed Number of Fields and Number of Entries sorting options which were throwing errors

## 0.8.8 - 2015-04-14

### Added
- Added support for {siteUrl} and all entry and entry content attributes in redirect rule
- Added support for attaching files to notification emails via hidden config setting (sproutForms =&gt; enableFileAttachments)

### Changed
- Added support for tabs in Notification emails
- Notification emails now order fields in the order they appear
- Improved error messages when submitted fields don&#039;t validate
- Cleaned up code in tab template for displayForm() tag output
- Template overrides now need to be enabled via a hidden config setting (sproutForms =&gt; enableTemplateOverrides)

### Fixed
- Fixed output of date on Form Entries in Control Panel

## 0.8.7 - 2015-03-19

### Added
- Added support for front-end file uploads via custom Asset fields
- Added support for Sprout Forms Entry Element queries

### Fixed
- Fixed rendering issue which cased notifications to fail silently
- Removed initial draft of event integration API
- Fixed issue where Forms could not be organized into multiple groups

## 0.8.6 - 2015-01-29

### Changed
- Improved how integrations with older versions of Sprout Email are handled

### Fixed
- Fixed issue where defineSproutEmailEvents() was called even if SproutEmailBaseEvent did not exist

## 0.8.5 - 2015-01-23

### Added
- Added sproutForms.saveEntry event integration for the upcoming release of Sprout Email

### Changed
- Improved instructions on how to set up notification emails
- Improved the way field values are checked to prevent arrays from being outputted in string context

### Fixed
- Fixed issue where admin notifications were not being sent on Ajax submissions
- Fixed issue where a fatal syntax error in older versions of PHP would break layouts

## 0.8.4 - 2015-01-09

### Fixed
- Fixed issue where globals would disappear after a failed form submission
- Fixed issue where templates using template_from_string() would not finish rendering after failed submission

## 0.8.3 - 2014-12-08

### Fixed
- Fixed sorting issue on Form Entries index caused by the Craft 2.3 update

## 0.8.2 - 2014-11-14

### Added
- Forms now support all native Craft fields and custom third-party fields
- Forms now use Craft&#039;s Field Layout Editor
- Added Edit Field option to Field Layout Designer
- Build multi-page Forms and single-page Forms with multiple sections
- Forms are searchable
- Forms can be grouped
- Forms can be related to other content via the Sprout Forms Relations Field Type
- Forms can submit to third-party locations
- Form Entry Titles can be customized with any Form field values
- Form Entry Titles can be customized with values from any Form Fields using the Title Format syntax
- Form Entries are searchable (by Title)
- Form Entries are filterable by the Form they belong to
- Form Entries can now be edited in the Control Panel
- Form Entries can be related to other content via the Sprout Form Entry Relations Field Type
- All data from the last Form Entry can be viewed on the Thank You page with the lastEntry() tag
- Form Entries can now be submitted via ajax
- Output your simple forms with one line of code using the displayForm() tag (Supported Fields: Text, Textarea, Number, Dropdown, Checkboxes, Radio Buttons, Multi-select
- Build complex front-end Forms using all field types just as you would with a Craft Entry Form
- Third-party developers can add front-end output support for custom fields
- Distiguish between Basic and Advanced field types when creating Fields
- Override the default Form templates to have 100% control over your dynamic template design
- Override the default email template to have 100% control over your notification email design
- Updated example Forms and various help notes throughout the interface into tool tips
- Forms are now Element Types
- Entries are now Element Types
- Added CSRF Support
- Added support for Command+S to all forms
- Added `editSproutFormsSettings` permission
- Added sproutForms.modifyForm hook to form displayForm() template
- Improved layout of Form Edit page
- Form submission can now be faked by third-party plugins
- Added the `SproutFormsFieldType` Class
- Added `onSaveEntry` and `onBeforeSaveEntry` events

### Changed
- Notifications can be customized with the values submitted in a Form Entry
- Deprecated `onBeforeSubmitFormEvent`

### Fixed
- Fixed Entries page display bug where entries would disappear
- Fixed bug where dropdown and radio fields did not retain state

## 0.7.1 - 2014-04-10

### Fixed
- Fixed bug if IP address or browser info don&#039;t exist when viewing pre-existing entries

## 0.7.0 - 2014-04-10

### Added
- Added support for error handling on pages with multiple forms
- Added Contact Form and Mailing List example forms available on installation
- Added onBeforeSubmitForm() and onBeforeSaveEntry() Events
- Added Twig support to notification field
- Added initial framework for unit testing
- Each submitted entry now captures IP address and browser info
- Added Form settings for submit button type and label
- Added subject and reply-to fields to notifications
- Added support for Sprout Email notifications options to select specific forms

### Changed
- Added support for displaying form fields without the need for Twig&#039;s raw filter
- Various UI adjustments
- Removed Sprout Footer from form entries index page
- Improve email recipient list validation when saving
- A form that fails validation now returns an object that matches the forms handle and falls back to a &#039;form&#039; object
- A form that fails validation will now return errors as part of the form object formHandle.errors or form.errors. Return values &#039;error&#039; and &#039;errors&#039; have been removed.
- A form that fails validation no longer returns an &#039;entry&#039; object
- Removed error handling using msg() variable
- Deprecated sproutFormsPrePost hook, use onBeforeSubmitForm() Event instead

### Fixed
- Fixed Publish tab code examples to reflect new syntax
- Fixed recipient list validation bug

## 0.6.0.1 - 2014-01-25

### Added
- Added a &quot;type&quot; variable to each field
- Added a type classname to the parent div when outputting forms programmatically &lt;div class=&quot;field checkbox&quot;&gt;
- Added an on-submit redirect url form setting on backend that outputs in the form tag
- Added displayField tag: craft.sproutForms.displayField(&#039;fieldHandle&#039;)
- Added auto-population for &#039;handle&#039; when title is written in on Form Settings tab
- Added support for Checkboxes, Dropdown, Multi-select, and Radio button fields
- Added field.required variable
- Added support to drag-and-drop fields on field settings page
- Added &lt;span class=&quot;required&quot;&gt; * &lt;/span&gt; when outputting forms programmatically

### Changed
- A form that doesn&#039;t validate now returns an &#039;errors&#039; variable
- Renamed PublicController to EntriesController
- Renamed PublicController &#039;post&#039; function to EntriesController &#039;saveEntry&#039;
- Renamed field.html to field.input
- You can now retrieve submitted form values using the &#039;entry&#039; object which is returned when a form doesn&#039;t validate
- Renamed field.name to field.label
- Notification emails now recognize line breaks

### Fixed
- Fixed bug on settings page where clicking &#039;Publish Your Form&#039; would cause a 404
- Fixed bug on notification settings page where clicking &#039;Submit&#039; would cause a 404
- Remove extraneous output from error messages

## 0.5.1.7 - 2014-01-11

### Added
- Private Beta
