<?php
namespace Craft;

class SenorFormVariable
{
	/**
	 * Plugin Name
	 * Make your plugin name available as a variable 
	 * in your templates as {{ craft.YourPlugin.name }}
	 * 
	 * @return string
	 */
	public function getName()
	{
		$plugin = craft()->plugins->getPlugin('senorform');
	    return $plugin->getName();
	}

	/**
	 * Get plugin version
	 * 
	 * @return string
	 */
	public function getVersion()
	{
		$plugin = craft()->plugins->getPlugin('senorform');
	    return $plugin->getVersion();
	}

	/**
     * Get a specific form. If no form is found, returns null
     *
     * @param  int   $id
     * @return mixed
     */
    public function getFormById($formId)
    {
    	return craft()->senorForm->getFormById($formId);
    }
	
	/**
	 * Get a form field given field id
	 * 
	 * @param int $fieldId
	 * @return obj
	 */
	public function getFieldById($fieldId)
	{
		$fieldModel = craft()->senorForm_field->getFieldById($fieldId);

		// Remove our namespace so the user can use their chosen handle
		$handle = craft()->senorForm->adjustFieldName($fieldModel, 'human');	

		if (isset($handle))
		{
			$fieldModel->handle = $handle;
		}

		$available_validations = craft()->senorForm_field->getValidationOptions();
		$field_validations = explode(',', $fieldModel->validation);
		$fieldModel->validation = $field_validations;
		
		// if all available validations are in current validation, we'll set 'all' selected
		if(array_intersect($available_validations, $field_validations) == $available_validations)
		{
			$fieldModel->validation = array();
		}
		
		return $fieldModel;
	}
	
	/**
	 * Get a form given associated field id
	 *
	 * @param int $fieldId
	 * @return obj
	 */
	public function getFormByFieldId($params)
	{
		if( ! isset($params['fieldId']))
		{
			return null;
		}
		
		$form = craft()->senorForm->getFormByFieldId($params['fieldId']);
		
		if(isset($params['idOnly']) && $params['idOnly'] == true)
		{
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
		$fields = craft()->senorForm->getFields($formId);

		foreach ($fields as $key => $value) {

			if ($handle = craft()->senorForm->adjustFieldName($value, 'human'))
			{
				$fields[$key]['handle'] = $handle;
			}

		}
		
		return $fields;
	}
	
	/**
	 * Return field entry for display in entries table
	 * 
	 * @param int $formId
	 * @param int $field
	 * @param obj $entry SenorForm_ContentEntry
	 */
	public function getFieldEntry($formId, $field, $entry)
	{
		$field_name = "formId{$formId}_{$field->handle}";
		$res = $entry->$field_name ? $entry->$field_name : '';
		$json = json_decode($res);
		if($json && ! is_int($json))
		{
			$options_data = array();
			foreach($json as $option_label => $option_value)
			{
				$options_data[] = str_replace(' ', '&nbsp;', $option_label) . ':&nbsp;' . str_replace(' ', '&nbsp;', $option_value);
			}
			return implode('<br/>', $options_data);
		}
		return str_replace(' ', '&nbsp;', $res);
	}
	
	/**
	 * Return fields for display
	 * 
	 * @param string $form_handle
	 * @return array
	 */
	public function getFormFields($form_handle)
	{
		$fields = array();

		if($formFields = craft()->senorForm->getFieldsByFormHandle($form_handle))
		{			
			foreach($formFields as $key => $fieldInfo)
			{
				// Remove our namespace so the user can use their chosen handle
				$handle = craft()->senorForm->adjustFieldName($fieldInfo, 'human');	
				
				$func = '_sf_' . strtolower($fieldInfo->type);
				if(function_exists($func))
				{	
					// set HTML output with a custom function
					// @TODO - let's try to do this with a Twig Template.
					// How does craft do this with Settings in plugins?
					$fields[$handle] = $func($fieldInfo);
				}
				else 
				{
					$fields[$handle]['html'] = '';
				}
				
				$fields[$handle]['instructions'] = $fieldInfo->instructions;
				$fields[$handle]['hint'] = isset($fieldInfo->settings['hint']) ? $fieldInfo->settings['hint'] : '';
				$fields[$handle]['name'] = $fieldInfo->name;
				$fields[$handle]['error'] = isset($_POST['errors'][$handle]) ? '<div class="field-error">' . implode('<br/>', $_POST['errors'][$handle]) . '</div>' : '';
			}
		}
		
		return $fields;
	}
	
	public function getValidationOptions()
	{
		return craft()->senorForm_field->getValidationOptions();
	}
	
	/**
	 * Get all forms
	 * 
	 * @return array
	 */
	public function getAllForms()
	{
		return craft()->senorForm->getAllForms();
	}
	
	/**
	 * Returns all entries for all forms
	 * 
	 * @param int form id
	 * @return array
	 */
	public function getAllEntries($formId)
	{
		return craft()->senorForm->getEntries($formId);
	}
	
	/**
	 * Get entry
	 * 
	 * @param int $id
	 */
	public function getEntryById($id)
	{
		return craft()->senorForm->getEntryById($id);
	}
	
	/**
	 * Returns all installed fieldtypes.
	 *
	 * @return array
	 */
	public function getAllFieldTypes()
	{
		$include = array('Checkboxes', 'Dropdown', 'PlainText', 'RadioButtons');
		$fieldTypes = craft()->fields->getAllFieldTypes();
		foreach($fieldTypes as $k=>$v)
		{
			if( ! in_array($k, $include))
			{
				unset($fieldTypes[$k]);
			}
		}
		return FieldTypeVariable::populateVariables($fieldTypes);
	}
	
	/**
	 * Display message to user
	 * 
	 * @return void
	 */
	public function msg()
	{
		$notice = \Yii::app()->user->getFlash('notice');
		$error = \Yii::app()->user->getFlash('error');
		echo $notice ? '<div class="notice">' . $notice . '</div>' : '';
		echo $error ? '<div class="error">' . $error . '</div>' : '';
	}
	
	/**
	 * Helper function for debugging 
	 * @param mixed $msg
	 * @return void
	 */
	public function dump($msg)
	{
		dump($msg);die();
	}
}