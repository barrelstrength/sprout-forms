<?php
namespace Craft;

class SproutForms_FormSproutImportElementImporter extends BaseSproutImportElementImporter
{
	public $isNewForm;

	/**
	 * @return null|string
	 */
	public function getName()
	{
		return Craft::t("Sprout Forms Forms");
	}

	public function getModelName()
	{
		return 'SproutForms_Form';
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

		$fieldLayout    = array();
		$requiredFields = array();

		foreach ($fieldLayoutTabs as $tab)
		{
			$tabName = $tab['name'];
			$fields  = $tab['fields'];

			foreach ($fields as $fieldSettings)
			{
				$field = sproutImport()->settingsImporter->saveSetting($fieldSettings);

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
