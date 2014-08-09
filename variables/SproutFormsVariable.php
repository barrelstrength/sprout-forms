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
		
		
		// @TODO - setup ability to override default macros in templates
		// $templatePath = craft()->path->getPluginsPath() . 'sproutforms/templates/_macros/';
		// $templateOverridePath = "";

		// if ($templateFolderOverride) 
		// {
		// 	$templateOverridePath = craft()->path->getTemplatesPath() . $templateFolderOverride . "/";
		// }
		
	
		// Build the HTML for our form fields
		$fieldsHtml = '';

		// Loop through all of our fields
		foreach ($form->getFieldLayout()->getFields() as $field) 
		{
			// Identify Entry ELement Model here if editing Entry 
			$element = null;
			$static = false;

			$field = $field->getField();

			// @TODO - logic is broken here if element is not empty
			$value = (!empty($element) ? $element : null);
			$errors = ((!empty($element) AND $static == false) ? $element->getErrors($field->handle) : null);
			$fieldtype = craft()->fields->populateFieldType($field, $element);
			$instructions = ($static == false ? Craft::t($field->instructions) : null);

			if ($fieldtype) 
			{
				if ($static == false)
				{
					// @TODO - ERROR
					// Unable to find the template “_components/fieldtypes/PlainText/input”.
					// getInputHtml is not friendly to front end requests... do we need to recreate this path?
					// craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/_macros/forms/craft/');
					// And we can seem to call getInputHtml in a way that actuall helps a different custom
					// field find it's templates in its own directory
					// if ($field->type == 'SproutEmailField_Email')
					// craft()->path->setTemplatesPath(craft()->path->getPluginsPath());
					
					// if (file_exists($templateOverridePath))
					// {
					// 	craft()->path->setTemplatesPath($templateOverridePath);
					// } 


					craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/');
					
					// $macroFolder = '_macros';
					// $fieldtypesFolderOverride = craft()->path->getTemplatesPath() . '_macros/fieldtypes/';
					$fieldtypesFolder = craft()->path->getPluginsPath() . 'sproutforms/integrations/sproutforms_fieldtypes/';

					$frontEndFieldTypes = craft()->sproutForms_fields->findAllFrontEndFieldTypes($fieldtypesFolder);

					$type = $fieldtype->model->type;
					
					if (isset($frontEndFieldTypes[$type])) 
					{
						// Instantiate it
						$class = __NAMESPACE__.'\\'.$frontEndFieldTypes[$type]['class'];
						
						// Make sure the class exists
						if (!class_exists($class))
						{
							require $frontEndFieldTypes[$type]['file'];
						}

						$frontEndField = new $class;

						// $type = $fieldtype->model->type;
						// $handle = $fieldtype->model->handle;
						$fieldModel = $fieldtype->model;
						$settings = $fieldtype->getSettings();

						$input = $frontEndField->getInputHtml($fieldModel, $settings);
					}
					
					
					// switch ($fieldType) {
					// 	case 'PlainText':
							
					// 		$input = craft()->templates->render('_macros/fieldtypes/PlainText/input', array(
					// 			'name' => $handle,
					// 			'value'=> craft()->request->getPost($handle),
					// 			'settings' => $fieldtype->getSettings()
					// 		));

					// 		break;

					// 	case 'Checkboxes':
							
					// 		$input = craft()->templates->render('_macros/fieldtypes/Checkboxes/input', array(
					// 			'name'    => $handle,
					// 			'options' => $fieldtype->getSettings()->options,
					// 			'values'  => array()
					// 		));

					// 		break;

					// 	case 'Dropdown':
							
					// 		$input = craft()->templates->render('_macros/fieldtypes/Dropdown/input', array(
					// 			'name'    => $handle,
					// 			'options' => $fieldtype->getSettings()->options,
					// 			'values'  => array()
					// 		));

					// 		break;

					// 	case 'MultiSelect':
							
					// 		$input = craft()->templates->render('_macros/fieldtypes/MultiSelect/input', array(
					// 			'name'    => $handle,
					// 			'options' => $fieldtype->getSettings()->options,
					// 			'values'  => array()
					// 		));

					// 		break;

					// 	case 'RadioButtons':
							
					// 		$input = craft()->templates->render('_macros/fieldtypes/RadioButtons/input', array(
					// 			'name'    => $handle,
					// 			'options' => $fieldtype->getSettings()->options,
					// 			'values'  => array()
					// 		));

					// 		break;

					// 	case 'SproutEmailField_Email':

					// 		$input = craft()->templates->render('_macros/fieldtypes/SproutEmailField/input', array(
					// 			// 'id' => $namespaceInputId,
					// 			'name' => $handle,
					// 			'value'=> craft()->request->getPost($handle),
					// 			'settings' => $fieldtype->getSettings()
					// 		));

					// 		break;

					// 	case 'SproutLinkField_Link':

					// 		$input = craft()->templates->render('_macros/fieldtypes/SproutLinkField/input', array(
					// 			// 'id' => $namespaceInputId,
					// 			'name' => $handle,
					// 			'value'=> craft()->request->getPost($handle),
					// 			'settings' => $fieldtype->getSettings()
					// 		));

					// 		break;
						
					// 	default:
					// 		# code...
					// 		break;
					// }
					

					// $input = $fieldtype->getInputHtml($field->handle, $value);
				}
				else
				{
					// $input = $fieldtype->getStaticHtml($value);
				}
			}
			else
			{
				$input = '<p class="error">' . Craft::t("The fieldtype class “{class}” could not be found.", array('class', $field->type)) . '</p>';
			}

			if ($input OR $instructions) 
			{
				$fieldsHtml .= craft()->templates->render('_macros/forms/field', array(
					'label'        => Craft::t($field->name),
					'required'     => ($static == false ? $field->required : false),
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



	// ============================================================
	// @TODO - Review Variables Below - Migrated from previous plugin
	// ============================================================

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
	 * Get a form field given field id
	 * 
	 * @param int $fieldId
	 * @return obj
	 */
	public function getFieldById($fieldId)
	{
		$field = craft()->fields->getFieldById($fieldId);
		
		return $field;
	}
	
	/**
	 * Get a form given associated field id
	 *
	 * @param int $fieldId
	 * @return obj
	 */
	public function getFormByFieldId($params)
	{
		if (!isset($params['fieldId'])) {
			return null;
		}
		
		$form = craft()->sproutForms->getFormByFieldId($params['fieldId']);
		
		if (isset($params['idOnly']) && $params['idOnly'] == true) {
			return $form->id;
		}
		
		return $form;
	}
	
	/** 
	 * Get form fields for specified form
	 * 
	 * @param int $formId
	 * @return array
	 */
	public function getFields($formId)
	{
		$fields = craft()->sproutForms->getFields($formId);
		
		foreach ($fields as $key => $value) {
			if ($handle = craft()->sproutForms->adjustFieldName($value, 'human')) {
				$fields[$key]['handle'] = $handle;
			}
		}
		
		return $fields;
	}
	
	/**
	 * Get all forms
	 * 
	 * @return array
	 */
	public function getAllForms()
	{
		return craft()->sproutForms->getAllForms();
	}
	
	/**
	 * Returns all entries for all forms
	 * 
	 * @param int form id
	 * @return array
	 */
	public function getAllEntries($formId)
	{
		return craft()->sproutForms->getEntries($formId);
	}
	
	/**
	 * Get entry
	 * 
	 * @param int $id
	 */
	public function getEntryById($id)
	{
		return craft()->sproutForms_entries->getEntryById($id);
	}

	public function getAllFormGroups($id = null)
	{
		return craft()->sproutForms_groups->getAllFormGroups($id);
	}

	public function getFormsByGroupId($groupId)
	{
		return craft()->sproutForms_groups->getFormsByGroupId($groupId);
	}

	public function prepareFieldTypesDropdown($fieldTypes)
	{
		return craft()->sproutForms_fields->prepareFieldTypesDropdown($fieldTypes);
	}
}