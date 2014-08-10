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

		craft()->content->fieldContext = $form->getFieldContext();
		craft()->content->contentTable = $form->getContentTable();

		$settings = craft()->plugins->getPlugin('sproutforms')->getSettings();
		$templateFolderOverride = $settings->templateFolderOverride;
		
		// Build the HTML for our form fields
		$fieldsHtml = '';

		// Loop through all of our fields
		foreach ($form->getFieldLayout()->getFields() as $field) 
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
			$errors = ((!empty($element) AND $static == false) ? $element->getErrors($field->handle) : null);
			$fieldtype = craft()->fields->populateFieldType($field, $element);
			$instructions = ($static == false ? Craft::t($field->instructions) : null);

			if ($fieldtype) 
			{
				// Set our templates path
				// @TODO - check for template override path first: $templateFolderOverride
				// @TODO - Do all of these settings need to be at the fieldtype level or some can be elsewhere?
				craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/');

				// Set our supported fieldtype overrides folder
				$fieldtypesFolder = craft()->path->getPluginsPath() . 'sproutforms/integrations/sproutforms_fieldtypes/';

				// Create a list of the name, class, and file of fields we support 
				// based on what we find in our $fieldtypesFolder
				$frontEndFieldTypes = craft()->sproutForms_fields->findAllFrontEndFieldTypes($fieldtypesFolder);
				
				// Get our field type
				$type = $fieldtype->model->type;
				
				// If we support our current fieldtype, render it
				if (isset($frontEndFieldTypes[$type])) 
				{
					// Instantiate it
					$class = __NAMESPACE__.'\\'.$frontEndFieldTypes[$type]['class'];
					
					// Make sure the our front-end Field Type class exists
					if (!class_exists($class))
					{
						require $frontEndFieldTypes[$type]['file'];
					}

					// Create a new instance of our Field Type
					$frontEndField = new $class;

					$fieldModel = $fieldtype->model;
					$settings = $fieldtype->getSettings();

					// Create the HTML for the input field
					$input = $frontEndField->getInputHtml($fieldModel, $settings);
				}
				else
				{	
					// Field Type is not supported
					// @TODO - provide better error here pointing to docs on how to solve this.
					$input = '<p class="error">' . Craft::t("The “".$type."” field is not supported by default to be output in front-end templates.") . '</p>';
				}
			}

			// Render our field
			if ($input OR $instructions) 
			{	
				$fieldsHtml .= craft()->templates->render('_macros/forms/field', array(
					'label'        => Craft::t($field->name),
					'required'     => $required,
					'instructions' => $instructions,
					'id'           => $field->handle,
					'errors'       => $errors,
					'input'        => $input,
				));
			}
		}

		// Build our complete form
		$formHtml = craft()->templates->render('_macros/form', array(
			'form'   => $form,
			'fields' => $fieldsHtml,
			'errors' => $form->getErrors()
		));
		
		return new \Twig_Markup($formHtml, craft()->templates->getTwig()->getCharset());
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

		$form = craft()->sproutForms_forms->getFormByHandle($formHandle);

		craft()->content->fieldContext = $form->getFieldContext();
		craft()->content->contentTable = $form->getContentTable();
		craft()->path->setTemplatesPath(craft()->path->getCpTemplatesPath());
		
		// @TODO - Maybe use renderMacro() instead
		$fieldHtml = craft()->templates->render('_includes/field', array(
			'field' => craft()->fields->getFieldByHandle($fieldHandle)->getAttributes()
		));

		craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());
		
		return new \Twig_Markup($fieldHtml, craft()->templates->getTwig()->getCharset());
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