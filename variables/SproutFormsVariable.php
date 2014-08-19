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
	 * @param string $form_handle
	 * @return string
	 */
	public function displayForm($formHandle, $customSettings = null)
	{	
		$form = craft()->sproutForms_forms->getFormByHandle($formHandle);

		// @TODO - consider what other places this might get accessed from
		// do we need to do any more checks to make sure this doesn't cause issues?
		// 
		// If a form has been submitted, use are existing EntryModel
		// otherwise, create a new EntryModel
		if (isset(craft()->sproutForms_forms->activeEntries[$formHandle]))
		{
			$entry = craft()->sproutForms_forms->activeEntries[$formHandle];	
		}
		else
		{
			$entry = new SproutForms_EntryModel();
		}

		// Backup our field context and content table
		$oldFieldContext = craft()->content->fieldContext;
		$oldContentTable = craft()->content->contentTable;

		// Set our field content and content table to work with our form output
		craft()->content->fieldContext = $form->getFieldContext();
		craft()->content->contentTable = $form->getContentTable();

		$this->settings = craft()->plugins->getPlugin('sproutforms')->getSettings();
		
		// Set our Sprout Forms Front-end Form Template path
		craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/_special/templates/');	

		// Set our Sprout Forms support field classes folder
		$fieldtypesFolder = craft()->path->getPluginsPath() . 'sproutforms/fields/';

		// Create a list of the name, class, and file of fields we support 
		$this->fields = craft()->sproutForms_fields->getSproutFormsFields($fieldtypesFolder);

		// Determine where our form and field template should come from
		$this->templates = craft()->sproutForms_fields->getSproutFormsTemplates();
		
		// Build the HTML for our form fields
		$fieldsHtml = '';

		// Loop through all of our fields
		foreach ($form->getFieldLayout()->getFields() as $field) 
		{	
			$fieldsHtml .= $this->_prepareField($field, $entry);
		}

		// Check if we need to update our Front-end Form Template Path
		craft()->path->setTemplatesPath($this->templates['form']);

		// Build our complete form
		$formHtml = craft()->templates->render('form', array(
			'form'   => $form,
			'fields' => $fieldsHtml,
			'errors' => $entry->getErrors()
		));

		craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		// Reset our field context and content table to what they were previously
		craft()->content->fieldContext = $oldFieldContext;
		craft()->content->contentTable = $oldContentTable;
		
		return new \Twig_Markup($formHtml, craft()->templates->getTwig()->getCharset());
	}

	/**
	 * @todo - this is broken!
	 * Returns a complete field for display in template
	 *
	 * @param string $form_handle
	 * @return string
	 */
	public function displayField($formFieldHandle)
	{
		list($formHandle, $fieldHandle) = explode('.', $formFieldHandle);
		if (!$formHandle || !$fieldHandle) return '';

		// @TODO - lots of duplicate code here to clean up that also appears in the
		// displayForm method
		$form = craft()->sproutForms_forms->getFormByHandle($formHandle);

		// @TODO - consider what other places this might get accessed from
		// do we need to do any more checks to make sure this doesn't cause issues?
		if (isset(craft()->sproutForms_forms->activeEntries[$formHandle]))
		{
			$entry = craft()->sproutForms_forms->activeEntries[$formHandle];	
		}
		else
		{
			$entry = new SproutForms_EntryModel();
		}

		// Backup our field context and content table
		$oldFieldContext = craft()->content->fieldContext;
		$oldContentTable = craft()->content->contentTable;

		// Set our field content and content table to work with our form output
		craft()->content->fieldContext = $form->getFieldContext();
		craft()->content->contentTable = $form->getContentTable();

		craft()->path->setTemplatesPath(craft()->path->getCpTemplatesPath());

		$fieldHtml = "";

		// @TODO - there's got to be a better way to do this
		foreach ($form->getFieldLayout()->getFields() as $field) 
		{	
			if ($field->getField()->handle == $fieldHandle) 
			{
				// Build the HTML for our form field
				$fieldHtml = $this->_prepareField($field, $entry);
				break;
			}
		}

		craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());
		
		// Reset our field context and content table to what they were previously
		craft()->content->fieldContext = $oldFieldContext;
		craft()->content->contentTable = $oldContentTable;

		return new \Twig_Markup($fieldHtml, craft()->templates->getTwig()->getCharset());
	}

	private function _prepareField(FieldLayoutFieldModel $field, SproutForms_EntryModel $entry)
	{
		// Set some values we'll hand off to the templates
		$required = $field->required;
		$field = $field->getField();
		
		// @TODO - what does this do!?
		$static = false;

		// Is this field for an Element Type?
		$element = (isset($field->getFieldType()->elementType)) ? $field->getFieldType()->model : null;
		
		// @TODO - logic is broken here if element is not empty
		$value = (!empty($element) ? $element : null);
		$errors = ((!empty($element) AND $static == false) ? $entry->getErrors($field->handle) : null);
		$fieldtype = craft()->fields->populateFieldType($field, $element);
		$instructions = ($static == false ? Craft::t($field->instructions) : null);

		if ($fieldtype) 
		{	
			// Get our field type
			$type = $fieldtype->model->type;		
			
			// If we support our current fieldtype, render it
			if (isset($this->fields[$type])) 
			{
				// Instantiate it
				$class = __NAMESPACE__.'\\'.$this->fields[$type]['class'];
				
				// Make sure the our front-end Field Type class exists
				if (!class_exists($class))
				{	
					require $this->fields[$type]['file'];
				}

				// Create a new instance of our Field Type
				$frontEndField = new $class;

				$fieldModel = $fieldtype->model;
				$settings = $fieldtype->getSettings();

				$postFields = craft()->request->getPost('fields');
				$value = (isset($postFields[$field->handle]) ? $postFields[$field->handle] : "");

				// Set template path
				craft()->path->setTemplatesPath($this->fields[$type]['templateFolder']);
				
				// Create the HTML for the input field
				$input = $frontEndField->getInputHtml($fieldModel, $value, $settings);
				
			}
			else
			{	
				// Field Type is not supported
				// @TODO - provide better error here pointing to docs on how to solve this.
				$input = '<p class="error">' . Craft::t("The “".$type."” field is not supported by default to be output in front-end templates.") . '</p>';
			}
		}

		// @TODO - hard coded. Abstract this.
		// if form.submitAction, we want this blank
		// for fields like inivisble captcha, we want this blank
		if ($type == 'SproutInvisibleCaptcha_InvisibleCaptcha') 
		{
			$namespace = '';
		}
		else
		{
			$namespace = 'fields';
		}
		

		$fieldHtml = "";

		// Render our field
		if ($input OR $instructions) 
		{	
			craft()->path->setTemplatesPath($this->templates['field']);

			$fieldHtml = craft()->templates->render('field', array(
				'label'        => Craft::t($field->name),
				'required'     => $required,
				'instructions' => $instructions,
				'id'           => $field->handle,
				'errors'       => $entry->getErrors($field->handle),
				'input'        => $input,
				'namespace'    => $namespace
			));
		}

		return $fieldHtml;
	}

	/**
	 * Get a specific form. If no form is found, returns null
	 *
	 * @param  int   $id
	 * @return mixed
	 */
	public function getFormById($formId)
	{
		return craft()->sproutForms_forms->getFormById($formId);
	}

	/**
	 * Get all forms
	 * 
	 * @return array
	 */
	public function getAllForms()
	{
		return craft()->sproutForms_forms->getAllForms();
	}

	/**
	 * Returns all entries for all forms
	 * 
	 * @param int form id
	 * @return array
	 */
	// public function getAllEntries($formId)
	// {
	// 	return craft()->sproutForms_entries->getEntries($formId);
	// }
	
	/**
	 * Get entry
	 * 
	 * @param int $id
	 */
	public function getEntryById($id)
	{
		return craft()->sproutForms_entries->getEntryById($id);
	}

	public function getLastEntry($formHandle)
	{
		if (isset(craft()->sproutForms_forms->activeEntries[$formHandle]))
		{
			return $entry = craft()->sproutForms_forms->activeEntries[$formHandle];	
		}
	}

	/**
	 * Get Form Groups
	 * 
	 * @param  int $id Group ID (optional)
	 * @return array
	 */
	public function getAllFormGroups($id = null)
	{
		return craft()->sproutForms_groups->getAllFormGroups($id);
	}

	/**
	 * Get all forms in a specific group
	 * 
	 * @param  int $id         Group ID
	 * @return SproutForms_FormModel
	 */
	public function getFormsByGroupId($groupId)
	{
		return craft()->sproutForms_groups->getFormsByGroupId($groupId);
	}

	/**
	 * Update fieldtypes into to option groups 
	 * 1) Basic fields we can output by default
	 * 2) Advanced fields that need some love before outputting
	 * 
	 * @param  array $fieldTypes
	 * @return array
	 */
	public function prepareFieldTypesDropdown($fieldTypes)
	{
		return craft()->sproutForms_fields->prepareFieldTypesDropdown($fieldTypes);
	}
}