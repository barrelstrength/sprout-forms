# Changelog

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
- Fixed bug where Sprout Lists integration was not being recognized for Notification Emails ([#106-sproutemail])

[#247]: https://github.com/barrelstrength/craft-sprout-forms/issues/247
[#254]: https://github.com/barrelstrength/craft-sprout-forms/issues/254
[#263]: https://github.com/barrelstrength/craft-sprout-forms/issues/263
[#266]: https://github.com/barrelstrength/craft-sprout-forms/issues/266
[#106-sproutemail]: https://github.com/barrelstrength/craft-sprout-email/issues/106

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
- Updated Report export naming to use toString method ([#9][#9-sproutbasereports])
- Updated barrelstrength/sprout-base-email requirement to v1.0.3
- Updated barrelstrength/sprout-base-reports requirement to v1.0.1
- Updated barrelstrength/sprout-base requirement v4.0.7

### Fixed
- Added Report Element migration ([#44][#44-sproutreports])
- Fixed TypeError in migration ([#259])

[#9-sproutbasereports]: https://github.com/barrelstrength/craft-sprout-base/pull/9
[#44-sproutreports]: https://github.com/barrelstrength/craft-sprout-reports/issues/44
[#259]: https://github.com/barrelstrength/craft-sprout-forms/issues/259

## 3.0.0-beta.46 - 2019-03-13
> {warning} This is a critical release. Please update to the latest to ensure your Address Field Administrative Area code data is being saved correctly.

### Changed
- Updated barrelstrength/sprout-base-fields requirement v1.0.3

### Fixed
- Fixed bug where Administrative Area Input was not populated correctly ([#85][#85fields])

[#85fields]: https://github.com/barrelstrength/craft-sprout-fields/issues/85

## 3.0.0-beta.45 - 2019-02-26

### Changed
- Updated craftcms/cms requirement to v3.1.15
- Updated barrelstrength/sprout-base-fields requirement v1.0.1

### Fixed 
- Fixed Address Field settings that blocked field from being saved in Postgres and Project Config ([#77][#77sproutfields], [#81][#81sproutfields])
- Fixed bug where Address Table was not created on new installation

[#77sproutfields]: https://github.com/barrelstrength/craft-sprout-fields/issues/77
[#81sproutfields]: https://github.com/barrelstrength/craft-sprout-fields/issues/81

## 3.0.0-beta.44 - 2019-02-18

> {note} This release includes updates to the default Notification Email Templates and updates to what variables are defined by default for the Hidden and Invisible Fields. Please be sure to review your custom Form implementations if you use these features and ensure everything is working as you'd like.
 
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
- Fixed false positives that could occur with Notification Email validation ([#100][#100email])

[#216]: https://github.com/barrelstrength/craft-sprout-forms/issues/216
[#227]: https://github.com/barrelstrength/craft-sprout-forms/issues/227
[#235]: https://github.com/barrelstrength/craft-sprout-forms/issues/235
[#238]: https://github.com/barrelstrength/craft-sprout-forms/issues/238
[#239]: https://github.com/barrelstrength/craft-sprout-forms/issues/239
[#240]: https://github.com/barrelstrength/craft-sprout-forms/issues/240
[#100email]: https://github.com/barrelstrength/craft-sprout-email/issues/100

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
