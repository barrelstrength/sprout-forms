<?php
namespace Craft;

class SproutFormsVariable
{
	/**
	 * Errors for public side validation
	 *
	 * @var array
	 */
	public static $errors;

	public $settings;
	public $fields;
	public $templates;
	public $namespace;
	public $isNakedField;

	/**
	 * @var ElementCriteriaModel
	 */
	public $entries;

	public function __construct()
	{
		$this->entries = craft()->elements->getCriteria('SproutForms_Entry');
	}

	/**
	 * Plugin Name
	 * Make your plugin name available as a variable
	 * in your templates as {{ craft.YourPlugin.name }}
	 *
	 * @return string
	 */
	public function getName()
	{
		$plugin = craft()->plugins->getPlugin('sproutforms');

		return $plugin->getName();
	}

	/**
	 * Get plugin version
	 *
	 * @return string
	 */
	public function getVersion()
	{
		$plugin = craft()->plugins->getPlugin('sproutforms');

		return $plugin->getVersion();
	}

	/**
	 * Returns a complete form for display in template
	 *
	 * @param string $formHandle
	 * @param array|null $customSettings
	 *
	 * @return string
	 * @internal param string $form_handle
	 */
	public function displayForm($formHandle, array $customSettings = null)
	{
		$form  = sproutForms()->forms->getFormByHandle($formHandle);
		$entry = sproutForms()->entries->getEntryModel($form);

		$this->settings = craft()->plugins->getPlugin('sproutforms')->getSettings();

		// Set our Sprout Forms Front-end Form Template path
		craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/_special/templates/');

		// Set our Sprout Forms support field classes folder
		$fieldtypesFolder = craft()->path->getPluginsPath() . 'sproutforms/fields/';

		// Create a list of the name, class, and file of fields we support 
		$this->fields = sproutForms()->fields->getSproutFormsFields($fieldtypesFolder);

		// Determine where our form and field template should come from
		$this->templates = sproutForms()->fields->getSproutFormsTemplates();

		// Set Tab template path
		craft()->path->setTemplatesPath($this->templates['tab']);

		// Build the HTML for our form tabs and fields
		$bodyHtml = craft()->templates->render('tab', array(
			'tabs'                 => $form->getFieldLayout()->getTabs(),
			'entry'                => $entry,
			'supportedFields'      => $this->fields,
			'displaySectionTitles' => $form->displaySectionTitles,
			'thirdPartySubmission' => ($form->submitAction) ? true : false
		));

		// Check if we need to update our Front-end Form Template Path
		craft()->path->setTemplatesPath($this->templates['form']);

		// Build our complete form
		$formHtml = craft()->templates->render('form', array(
			'form'   => $form,
			'body'   => $bodyHtml,
			'errors' => $entry->getErrors()
		));

		craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return new \Twig_Markup($formHtml, craft()->templates->getTwig()->getCharset());
	}

	public function displayTab($formTabHandle)
	{
		list($formHandle, $tabHandle) = explode('.', $formTabHandle);
		$tabHandle = strtolower($tabHandle);

		if (!$formHandle || !$tabHandle) return '';

		$form  = sproutForms()->forms->getFormByHandle($formHandle);
		$entry = sproutForms()->entries->getEntryModel($form);

		// Set our Sprout Forms Front-end Form Template path
		craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/_special/templates/');

		// Set our Sprout Forms support field classes folder
		$fieldtypesFolder = craft()->path->getPluginsPath() . 'sproutforms/fields/';

		// Create a list of the name, class, and file of fields we support 
		$this->fields = sproutForms()->fields->getSproutFormsFields($fieldtypesFolder);

		// Determine where our form and field template should come from
		$this->templates = sproutForms()->fields->getSproutFormsTemplates();

		// Set Tab template path
		craft()->path->setTemplatesPath($this->templates['tab']);

		$tabIndex = null;
		foreach ($form->getFieldLayout()->getTabs() as $key => $tabInfo) {
			$thisTabHandle = str_replace(" ", "", strtolower($tabInfo->name));

			// If our tab exists, grab the id
			if ($tabHandle == $thisTabHandle) {
				$tabIndex = $key;
			}
		}

		if (is_null($tabIndex)) return '';

		$layoutTabs = $form->getFieldLayout()->getTabs();
		$layoutTab  = isset($layoutTabs[$tabIndex]) ? $layoutTabs[$tabIndex] : null;

		// Build the HTML for our form tabs and fields
		$tabHtml = craft()->templates->render('tab',
			array(
				'tabs'                 => array($layoutTab),
				'entry'                => $entry,
				'supportedFields'      => $this->fields,
				'displaySectionTitles' => $form->displaySectionTitles,
				'thirdPartySubmission' => ($form->submitAction) ? true : false
			)
		);

		craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return new \Twig_Markup($tabHtml, craft()->templates->getTwig()->getCharset());
	}

	/**
	 * Returns a complete field for display in template
	 *
	 * @param string $form_handle
	 * @return string
	 */
	public function displayField($formFieldHandle)
	{
		list($formHandle, $fieldHandle) = explode('.', $formFieldHandle);
		if (!$formHandle || !$fieldHandle) return '';

		$form  = sproutForms()->forms->getFormByHandle($formHandle);
		$entry = sproutForms()->entries->getEntryModel($form);

		// Set our Sprout Forms Front-end Form Template path
		craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/_special/templates/');

		// Determine where our form and field template should come from
		$this->templates = sproutForms()->fields->getSproutFormsTemplates();

		// Set Tab template path
		craft()->path->setTemplatesPath($this->templates['field']);

		$fieldHtml = "";

		// @TODO - there's got to be a better way to do this
		foreach ($form->getFieldLayout()->getFields() as $field) {
			if ($field->getField()->handle == $fieldHandle) {
				// Build the HTML for our form field
				$fieldHtml = craft()->templates->render('field', array(
					'field'                => $field->getField(),
					'required'             => $field->required,
					'element'              => $entry,
					'thirdPartySubmission' => ($form->submitAction) ? true : false
				));
				break;
			}
		}

		craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return new \Twig_Markup($fieldHtml, craft()->templates->getTwig()->getCharset());
	}

	public function getFieldInfo(FieldModel $field, SproutForms_EntryModel $element)
	{
		// Set our Sprout Forms support field classes folder
		$fieldtypesFolder = craft()->path->getPluginsPath() . 'sproutforms/fields/';

		// Create a list of the name, class, and file of fields we support 
		$this->fields = sproutForms()->fields->getSproutFormsFields($fieldtypesFolder);

		$fieldtype = craft()->fields->populateFieldType($field, $element);

		// If we support our current fieldtype, render it
		if (isset($this->fields[$field->type])) {
			// Instantiate it
			$class = __NAMESPACE__ . '\\' . $this->fields[$field->type]['class'];

			// Make sure the our front-end Field Type class exists
			if (!class_exists($class)) {
				require $this->fields[$field->type]['file'];
			}

			// Create a new instance of our Field Type
			$frontEndField = new $class;

			$fieldModel = $fieldtype->model;
			$settings   = $fieldtype->getSettings();

			$postFields = craft()->request->getPost('fields');
			$value      = (isset($postFields[$field->handle]) ? $postFields[$field->handle] : "");

			// Determine where our form and field template should come from
			$this->templates = sproutForms()->fields->getSproutFormsTemplates();

			// Set template path
			craft()->path->setTemplatesPath($this->fields[$field->type]['templateFolder']);

			// Create the HTML for the input field
			$input = $frontEndField->getInputHtml($fieldModel, $value, $settings);

			$this->namespace    = $frontEndField->getNamespace();
			$this->isNakedField = $frontEndField->isNakedField;

			// Set template path back to default
			craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/_special/templates/');
		}
		else
		{
			// Field Type is not supported
			// @TODO - provide better error here pointing to docs on how to solve this.
			$input = '<p class="error">' . Craft::t("The “" . $field->type . "” field is not supported by default to be output in front-end templates.") . '</p>';
		}

		// Identify PlainText and Textarea fields distinctly
		if ($field->type == 'PlainText' && $field->settings['multiline'] == 1)
		{
			$field->type = 'textarea';
		}

		// @TODO - improve naming and handling of this
		$fieldInfo['namespace']    = $this->namespace;
		$fieldInfo['isNakedField'] = $this->isNakedField;
		$fieldInfo['type']         = $field->type;
		$fieldInfo['input']        = new \Twig_Markup($input, craft()->templates->getTwig()->getCharset());

		// Set our Sprout Forms Front-end Form Template path
		craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/_special/templates/');

		// Determine where our form and field template should come from
		$this->templates = sproutForms()->fields->getSproutFormsTemplates();

		// Set Tab template path
		craft()->path->setTemplatesPath($this->templates['field']);

		return $fieldInfo;
	}

	/**
	 * Gets a specific form. If no form is found, returns null
	 *
	 * @param  int $id
	 * @return mixed
	 */
	public function getFormById($formId)
	{
		return sproutForms()->forms->getFormById($formId);
	}

	/**
	 * Gets a specific form by handle. If no form is found, returns null
	 *
	 * @param  string $formHandle
	 * @return mixed
	 */
	public function getForm($formHandle)
	{
		return sproutForms()->forms->getFormByHandle($formHandle);
	}

	/**
	 * Get all forms
	 *
	 * @return array
	 */
	public function getAllForms()
	{
		return sproutForms()->forms->getAllForms();
	}

	/**
	 * Gets entry by ID
	 *
	 * @param int $id
	 * @return  SproutForms_EntryModel
	 */
	public function getEntryById($id)
	{
		return sproutForms()->entries->getEntryById($id);
	}

	/**
	 * Gets last entry submitted
	 *
	 * @param  string $formHandle Form handle
	 * @return SproutForms_EntryModel
	 */
	public function getLastEntry()
	{
		if (craft()->httpSession->get('lastEntryId'))
		{
			$entryId = craft()->httpSession->get('lastEntryId');
			$entry   = sproutForms()->entries->getEntryById($entryId);

			craft()->httpSession->destroy('lastEntryId');
		}

		return (isset($entry)) ? $entry : null;
	}

	/**
	 * Gets Form Groups
	 *
	 * @param  int $id Group ID (optional)
	 * @return array
	 */
	public function getAllFormGroups($id = null)
	{
		return sproutForms()->groups->getAllFormGroups($id);
	}

	/**
	 * Gets all forms in a specific group by ID
	 *
	 * @param  int $id Group ID
	 * @return SproutForms_FormModel
	 */
	public function getFormsByGroupId($groupId)
	{
		return sproutForms()->groups->getFormsByGroupId($groupId);
	}

	/**
	 * Builds FieldType dropdown by grouping fields into to basic and advanced
	 *
	 * 1) Basic fields we can output by default
	 * 2) Advanced fields that need some love before outputting
	 *
	 * @param  array $fieldTypes
	 * @return array
	 */
	public function prepareFieldTypesDropdown($fieldTypes)
	{
		return sproutForms()->fields->prepareFieldTypesDropdown($fieldTypes);
	}

	public function multiStepForm($settings)
	{
		$currentStep = isset($settings['currentStep']) ? $settings['currentStep'] : null;
		$totalSteps  = isset($settings['totalSteps']) ? $settings['totalSteps'] : null;

		if (!$currentStep OR !$totalSteps) return;

		if ($currentStep == 1)
		{
			// Make sure we are starting from scratch
			craft()->httpSession->remove('multiStepForm');
			craft()->httpSession->remove('multiStepFormEntryId');
			craft()->httpSession->remove('currentStep');
			craft()->httpSession->remove('totalSteps');
		}

		craft()->httpSession->add('multiStepForm', true);
		craft()->httpSession->add('currentStep', $currentStep);
		craft()->httpSession->add('totalSteps', $totalSteps);
	}
}