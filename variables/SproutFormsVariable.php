<?php
namespace Craft;

class SproutFormsVariable
{
	/**
	 * @var ElementCriteriaModel
	 */
	public $entries;

	public function __construct()
	{
		$this->entries = craft()->elements->getCriteria('SproutForms_Entry');
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		$plugin = craft()->plugins->getPlugin('sproutforms');

		return $plugin->getName();
	}

	/**
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
	 * @param string     $formHandle
	 * @param array|null $renderingOptions
	 *
	 * @return string
	 * @internal param string $form_handle
	 */
	public function displayForm($formHandle, array $renderingOptions = null)
	{
		$form          = sproutForms()->forms->getFormByHandle($formHandle);
		$entry         = sproutForms()->entries->getEntryModel($form);
		$fields        = sproutForms()->fields->getRegisteredFields();
		$templatePaths = sproutForms()->fields->getSproutFormsTemplates($form);

		// Set Tab template path
		craft()->path->setTemplatesPath($templatePaths['tab']);

		$bodyHtml = craft()->templates->render(
			'tab', array(
				'tabs'                 => $form->getFieldLayout()->getTabs(),
				'entry'                => $entry,
				'formFields'           => $fields,
				'thirdPartySubmission' => !!$form->submitAction,
				'displaySectionTitles' => $form->displaySectionTitles,
				'renderingOptions'     => $renderingOptions
			)
		);

		// Check if we need to update our Front-end Form Template Path
		craft()->path->setTemplatesPath($templatePaths['form']);

		// Build our complete form
		$formHtml = craft()->templates->render(
			'form', array(
				'form'             => $form,
				'body'             => $bodyHtml,
				'errors'           => $entry->getErrors(),
				'renderingOptions' => $renderingOptions
			)
		);

		craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return TemplateHelper::getRaw($formHtml);
	}

	public function displayTab($formTabHandle)
	{
		list($formHandle, $tabHandle) = explode('.', $formTabHandle);
		$tabHandle = strtolower($tabHandle);

		if (!$formHandle || !$tabHandle)
		{
			return '';
		}

		$form  = sproutForms()->forms->getFormByHandle($formHandle);
		$entry = sproutForms()->entries->getEntryModel($form);

		// Set our Sprout Forms Front-end Form Template path
		// craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'sproutforms/templates/_special/templates/');

		// Set our Sprout Forms support field classes folder
		// $fieldtypesFolder = craft()->path->getPluginsPath().'sproutforms/fields/';

		// Create a list of the name, class, and file of fields we support
		// $fields = sproutForms()->fields->getSproutFormsFields($fieldtypesFolder);
		$fields = sproutForms()->fields->getRegisteredFields(true);

		// Determine where our form and field template should come from
		$templatePaths = sproutForms()->fields->getSproutFormsTemplates($form);

		// Set Tab template path
		craft()->path->setTemplatesPath($templatePaths['tab']);

		$tabIndex = null;

		foreach ($form->getFieldLayout()->getTabs() as $key => $tabInfo)
		{
			$currentTabHandle = str_replace('-', '', ElementHelper::createSlug($tabInfo->name));

			if ($tabHandle == $currentTabHandle)
			{
				$tabIndex = $key;
			}
		}

		if (empty($tabIndex))
		{
			return false;
		}

		$layoutTabs = $form->getFieldLayout()->getTabs();
		$layoutTab  = isset($layoutTabs[$tabIndex]) ? $layoutTabs[$tabIndex] : null;

		// Build the HTML for our form tabs and fields
		$tabHtml = craft()->templates->render(
			'tab',
			array(
				'tabs'                 => array($layoutTab),
				'entry'                => $entry,
				'supportedFields'      => $fields,
				'displaySectionTitles' => $form->displaySectionTitles,
				'thirdPartySubmission' => ($form->submitAction) ? true : false
			)
		);

		craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return TemplateHelper::getRaw($tabHtml);
	}

	/**
	 * Returns a complete field for display in template
	 *
	 * @param       $handle
	 * @param array $renderingOptions
	 *
	 * @return string
	 * @internal param string $form_handle
	 *
	 */
	public function displayField($handle, array $renderingOptions = null)
	{
		list($formHandle, $fieldHandle) = explode('.', $handle);


		if (empty($formHandle) || empty($fieldHandle))
		{
			return false;
		}

		if (!is_null($renderingOptions))
		{
			$renderingOptions = array(
				'fields' => array(
					$fieldHandle => $renderingOptions
				)
			);
		}

		$form  = sproutForms()->forms->getFormByHandle($formHandle);
		$entry = sproutForms()->entries->getEntryModel($form);

		// Determine where our form and field template should come from
		$templatePaths = sproutForms()->fields->getSproutFormsTemplates($form);

		$field = craft()->fields->getFieldByHandle($fieldHandle);

		if ($field)
		{
			$registeredFields = sproutForms()->fields->getRegisteredFields();

			if (isset($registeredFields[$field->getFieldType()->type]))
			{
				$value     = craft()->request->getPost($field->handle);
				$formField = isset($registeredFields[$field->type]) ? $registeredFields[$field->type] : null;

				craft()->path->setTemplatesPath($formField->getTemplatesPath());

				$formField->getInputHtml($field, $value, $field->getSettings(), $renderingOptions);

				// Set Tab template path
				craft()->path->setTemplatesPath($templatePaths['field']);

				// Build the HTML for our form field
				$fieldHtml = craft()->templates->render(
					'field', array(
						'value'                => $value,
						'field'                => $field,
						'element'              => $entry,
						'required'             => $field->getFieldType()->required,
						'formField'            => $formField,
						'renderingOptions'     => $renderingOptions,
						'thirdPartySubmission' => !!$form->submitAction,
					)
				);

				craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

				return TemplateHelper::getRaw($fieldHtml);
			}
		}
	}

	public function getFieldInfo(
		FieldModel $field,
		SproutForms_EntryModel $element,
		array $renderingOptions = null
	) {

		// Set our Sprout Forms support field classes folder
		$fieldtypesFolder = craft()->path->getPluginsPath().'sproutforms/fields/';

		// Create a list of the name, class, and file of fields we support
		$fields = sproutForms()->fields->getSproutFormsFields($fieldtypesFolder);

		$fieldtype = craft()->fields->populateFieldType($field, $element);

		$formId = array_pop(explode(':', $field->context));

		$form = sproutForms()->forms->getFormById($formId);

		// If we support our current fieldtype, render it
		Craft::dump($fields);

		$registeredFormFields = sproutForms()->fields->getRegisteredFields();

		if (array_key_exists($field->type, $registeredFormFields))
		{

			$value = craft()->request->getPost($field->handle);

			Craft::dd($registeredFormFields[$field->type]->getInputHtml($field, $value, $field->getFieldType()->getSettings(), $renderingOptions));
		}

		if (isset($fields[$field->type]))
		{
			// Instantiate it
			$class = __NAMESPACE__.'\\'.$fields[$field->type]['class'];

			// Make sure the our front-end Field Type class exists
			if (!class_exists($class))
			{
				require $fields[$field->type]['file'];
			}

			// Create a new instance of our Field Type
			$frontEndField = new $class;

			$fieldModel = $fieldtype->model;
			$settings   = $fieldtype->getSettings();

			$postFields = craft()->request->getPost('fields');
			$value      = (isset($postFields[$field->handle]) ? $postFields[$field->handle] : "");

			// Determine where our form and field template should come from
			$templatePaths = sproutForms()->fields->getSproutFormsTemplates($form);

			// Set template path
			craft()->path->setTemplatesPath();

			// Create the HTML for the input field
			$input = $frontEndField->getInputHtml($fieldModel, $value, $settings, $renderingOptions);

			$this->namespace    = $frontEndField->getNamespace();
			$this->isNakedField = $frontEndField->isNakedField;

			// Set template path back to default
			craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'sproutforms/templates/_special/templates/');
		}
		else
		{
			// Field Type is not supported
			// @TODO - provide better error here pointing to docs on how to solve this.
			$input = '<p class="error">'.Craft::t("The “".$field->type."” field is not supported by default to be output in front-end templates.").'</p>';
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
		craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'sproutforms/templates/_special/templates/');

		// Determine where our form and field template should come from
		$templatePaths = sproutForms()->fields->getSproutFormsTemplates($form);

		// Set Tab template path
		craft()->path->setTemplatesPath($templatePaths['field']);

		return $fieldInfo;
	}

	/**
	 * Gets a specific form. If no form is found, returns null
	 *
	 * @param  int $id
	 *
	 * @return mixed
	 */
	public
	function getFormById(
		$formId
	) {
		return sproutForms()->forms->getFormById($formId);
	}

	/**
	 * Gets a specific form by handle. If no form is found, returns null
	 *
	 * @param  string $formHandle
	 *
	 * @return mixed
	 */
	public
	function getForm(
		$formHandle
	) {
		return sproutForms()->forms->getFormByHandle($formHandle);
	}

	/**
	 * Get all forms
	 *
	 * @return array
	 */
	public
	function getAllForms()
	{
		return sproutForms()->forms->getAllForms();
	}

	/**
	 * Gets entry by ID
	 *
	 * @param int $id
	 *
	 * @return  SproutForms_EntryModel
	 */
	public
	function getEntryById(
		$id
	) {
		return sproutForms()->entries->getEntryById($id);
	}

	/**
	 * Gets last entry submitted
	 *
	 * @param  string $formHandle Form handle
	 *
	 * @return SproutForms_EntryModel
	 */
	public
	function getLastEntry()
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
	 *
	 * @return array
	 */
	public
	function getAllFormGroups(
		$id = null
	) {
		return sproutForms()->groups->getAllFormGroups($id);
	}

	/**
	 * Gets all forms in a specific group by ID
	 *
	 * @param  int $id Group ID
	 *
	 * @return SproutForms_FormModel
	 */
	public
	function getFormsByGroupId(
		$groupId
	) {
		return sproutForms()->groups->getFormsByGroupId($groupId);
	}

	/**
	 * Builds FieldType dropdown by grouping fields into to basic and advanced
	 *
	 * 1) Basic fields we can output by default
	 * 2) Advanced fields that need some love before outputting
	 *
	 * @param  array $fieldTypes
	 *
	 * @return array
	 */
	public
	function prepareFieldTypesDropdown(
		$fieldTypes
	) {
		return sproutForms()->fields->prepareFieldTypesDropdown($fieldTypes);
	}

	public
	function multiStepForm(
		$settings
	) {
		$currentStep = isset($settings['currentStep']) ? $settings['currentStep'] : null;
		$totalSteps  = isset($settings['totalSteps']) ? $settings['totalSteps'] : null;

		if (!$currentStep OR !$totalSteps)
		{
			return;
		}

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

	/**
	 * @param $type
	 *
	 * @return null|SproutFormsBaseFormField
	 */
	public function getRegisteredField($type)
	{
		$fields = sproutForms()->fields->getRegisteredFields();

		if (isset($fields[$type]))
		{
			return $fields[$type];
		}
	}

	public function getTemplatesPath()
	{
		return craft()->path->getTemplatesPath();
	}
}
