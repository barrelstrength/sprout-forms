<?php
namespace Craft;

class SproutForms_FormSproutImportImporter extends SproutImportBaseImporter
{
	public $isNewForm;

	public function getModel()
	{
		$model = 'Craft\\SproutForms_FormModel';
		return new $model;
	}

	public function populateModel($model, $settings)
	{
		// @todo - we need this because we should refactor how deleting/weeding
		// things works. Deleting passes in a different settings array...
		if (isset($settings['attributes']))
		{
			$settings = $settings['attributes'];
		}
		// Assign any setting values we can to the model
		$model->setAttributes($settings);

		$this->model = $model;
	}

	public function save()
	{
		//$this->isNewForm = ($model->id) ? false : true;
		
		return craft()->sproutForms_forms->saveForm($this->model);
	}

	public function deleteById($id)
	{
		$form = craft()->sproutForms_forms->getFormById($id);

		if ($form)
		{
			craft()->sproutForms_forms->deleteForm($form);
		}
	}

	public function resolveNestedSettings($model, $settings)
	{
		// Check to see if we have any Entry Types we should also save
		if (empty($settings['attributes']['fieldLayout']) OR empty($model->id)) 
		{
			return true;
		}

		$fieldLayoutTabs = $settings['attributes']['fieldLayout'];
			
		craft()->content->fieldContext = $model->fieldContext;
		craft()->content->contentTable = $model->contentTable;

		//------------------------------------------------------------

		// Do we have a new field that doesn't exist yet?  
		// If so, save it and grab the id.		

		$fieldLayout = array();
		$requiredFields = array();

		foreach ($fieldLayoutTabs as $tab)
		{	
			$tabName = $tab['name'];
			$fields = $tab['fields'];
			
			foreach ($fields as $fieldSettings) 
			{
				$field = craft()->sproutImport->saveSetting($fieldSettings);

				$fieldLayout[$tabName][] = $field->id;
				
				if ($field->required) 
				{
					$requiredFields[] = $field->id;
				}
			}
		}

		// @TODO - move this to a different place to save?
		
		// Set the field layout
		$fieldLayout = craft()->fields->assembleLayout($fieldLayout, $requiredFields);

		// @todo FieldLayout Type should be dynamic
		$fieldLayout->type = 'SproutForms_Form';

		// @todo - get the parent SECTION (or Field Layout Container and resave things...)
		// Should I be using the MODEL or the JSON Settings? 
		// How do I know?  Hrmm....
		$model->setFieldLayout($fieldLayout);

		if (craft()->sproutForms_forms->saveForm($model))
		{
			return true;
		}
		else
		{
			Craft::dd($model->getErrors());
			return false;
		}
	}
}
