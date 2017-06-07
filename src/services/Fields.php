<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use yii\base\Component;
use craft\base\Field;
use craft\records\Field as FieldRecord;
use barrelstrength\sproutforms\integrations\sproutforms\fields\PlainText;
use craft\records\FieldLayoutField as FieldLayoutFieldRecord;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;
use craft\helpers\FileHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\events\RegisterFieldsEvent;

class Fields extends Component
{
	/**
	 * @var SproutFormsBaseField[]
	 */
	protected $registeredFields;

	/**
	 * @event RegisterFieldsEvent The event that is triggered when registering the fields available.
	 */
	const EVENT_REGISTER_FIELDS = 'registerFieldsEvent';

	/**
	 * @param array $fieldIds
	 *
	 * @throws \CDbException
	 * @throws \Exception
	 * @return bool
	 */
	public function reorderFields($fieldIds)
	{
		$transaction = Craft::$app->db->getTransaction() === null ? Craft::$app->db->beginTransaction() : null;

		try
		{
			foreach ($fieldIds as $fieldOrder => $fieldId)
			{
				$fieldLayoutFieldRecord            = $this->_getFieldLayoutFieldRecordByFieldId($fieldId);
				$fieldLayoutFieldRecord->sortOrder = $fieldOrder + 1;
				$fieldLayoutFieldRecord->save();
			}

			if ($transaction !== null)
			{
				$transaction->commit();
			}
		}
		catch (\Exception $e)
		{

			if ($transaction !== null)
			{
				$transaction->rollback();
			}

			throw $e;
		}

		return true;
	}

	/**
	 * @param int $fieldId
	 *
	 * @throws Exception
	 * @return FieldLayoutFieldRecord
	 */
	protected function _getFieldLayoutFieldRecordByFieldId($fieldId = null)
	{
		if ($fieldId)
		{
			$record = FieldLayoutFieldRecord::find('fieldId=:fieldId', array(':fieldId' => $fieldId));

			if (!$record)
			{
				throw new Exception(SproutForms::t('No field exists with the ID “{id}”', array('id' => $fieldId)));
			}
		}
		else
		{
			$record = new FieldLayoutFieldRecord();
		}

		return $record;
	}

	public function getSproutFormsTemplates(FormElement $form = null)
	{
		$templates              = array();
		$settings               = Craft::$app->plugins->getPlugin('sproutforms')->getSettings();
		$templateFolderOverride = $settings->templateFolderOverride;

		if ($form->enableTemplateOverrides)
		{
			$templateFolderOverride = $form->templateOverridesFolder;
		}

		$defaultTemplate = Craft::getAlias('@barrelstrength/sproutforms/templates/_special/templates/');

		// Set our defaults
		$templates['form']  = $defaultTemplate;
		$templates['tab']   = $defaultTemplate;
		$templates['field'] = $defaultTemplate;
		$templates['email'] = $defaultTemplate;

		// See if we should override our defaults
		if ($templateFolderOverride)
		{
			$formTemplate  = Craft::$app->path->getSiteTemplatesPath() . $templateFolderOverride . '/form';
			$tabTemplate   = Craft::$app->path->getSiteTemplatesPath() . $templateFolderOverride . '/tab';
			$fieldTemplate = Craft::$app->path->getSiteTemplatesPath() . $templateFolderOverride . '/field';
			$emailTemplate = Craft::$app->path->getSiteTemplatesPath() . $templateFolderOverride . '/email';

			foreach (Craft::$app->config->get('defaultTemplateExtensions') as $extension)
			{
				if (IOHelper::fileExists($formTemplate . '.' . $extension))
				{
					$templates['form'] = Craft::$app->path->getSiteTemplatesPath() . $templateFolderOverride . '/';
				}

				if (IOHelper::fileExists($tabTemplate . '.' . $extension))
				{
					$templates['tab'] = Craft::$app->path->getSiteTemplatesPath() . $templateFolderOverride . '/';
				}

				if (IOHelper::fileExists($fieldTemplate . '.' . $extension))
				{
					$templates['field'] = Craft::$app->path->getSiteTemplatesPath() . $templateFolderOverride . '/';
				}

				if (IOHelper::fileExists($emailTemplate . '.' . $extension))
				{
					$templates['email'] = Craft::$app->path->getSiteTemplatesPath() . $templateFolderOverride . '/';
				}
			}
		}

		return $templates;
	}

	/**
	 * @return array|SproutFormsBaseField[]
	 */
	public function getRegisteredFields()
	{
		if (is_null($this->registeredFields))
		{
			$this->registeredFields = [];

			// Our fields are registered in the SproutForms main class
			$event = new RegisterFieldsEvent([
				'fields' => []
			]);

			$this->trigger(self::EVENT_REGISTER_FIELDS, $event);

			$fields = $event->fields;

			/**
			 * @var SproutFormsBaseField $instance
			*/
			foreach ($fields as $instance)
			{
				$this->registeredFields[get_class($instance)] = $instance;
			}
		}

		return $this->registeredFields;
	}

	/**
	 * @param $type
	 *
	 * @return null|SproutFormsBaseField
	 */
	public function getRegisteredField($type)
	{
		$fields = $this->getRegisteredFields();

		foreach ($fields as $field)
		{
			if ($field->getType() == $type)
			{
				return $field;
			}
		}
	}

	/**
	 * Returns a field type selection array grouped by category
	 *
	 * Categories
	 * - Standard fields with front end rendering support
	 * - Custom fields that need to be registered using the Sprout Forms Field API
	 *
	 * @return array
	 */
	public function prepareFieldTypeSelection()
	{
		$fields         = $this->getRegisteredFields();
		$standardFields = [];

		if (count($fields))
		{
			// Loop through registered fields and add them to the standard group
			foreach ($fields as $class => $field)
			{
				$standardFields[$class] = $field::displayName();
			}

			// Sort fields alphabetically by name
			asort($standardFields);

			// Add the group label to the beginning of the standard group
			$standardFields = $this->prependKeyValue($standardFields, 'standardFieldGroup', array('optgroup' => SproutForms::t('Standard Fields')));
		}

		return $standardFields;
	}

	/**
	 * Returns the value of a given field
	 *
	 * @param string $field
	 * @param string $value
	 *
	 * @return FieldRecord
	 */
	public function getFieldValue($field, $value)
	{
		$result = FieldRecord::findOne([$field => $value]);

		return $result;
	}

	/**
	 * Create a secuencial string for the "name" and "handle" fields if they are already taken
	 *
	 * @param string
	 * @param string
	 * return string
	 */
	public function getFieldAsNew($field, $value)
	{
		$newField = null;
		$i        = 1;
		$band     = true;
		do
		{
			$newField = $field == "handle" ? $value . $i : $value . " " . $i;
			$form     = $this->getFieldValue($field, $newField);
			if (is_null($form))
			{
				$band = false;
			}

			$i++;
		}
		while ($band);

		return $newField;
	}

	/**
	 * This service allows create a default tab given a form
	 *
	 * @param FormElement $form
	 *
	 * @return SproutForms_FormModel | null
	 */
	public function addDefaultTab($form, &$field = null)
	{
		if ($form)
		{
			if (is_null($field))
			{
				$fieldsService = Craft::$app->getFields();
				$handle = $this->getFieldAsNew('handle', 'defaultField');

				$field = $fieldsService->createField([
					'type' => PlainText::class,
					'name' => SproutForms::t('Default Field'),
					'handle' => $handle,
					'instructions' => '',
					'translationMethod' => Field::TRANSLATION_METHOD_NONE,
				]);
				// Save our field
				Craft::$app->content->fieldContext = $form->getFieldContext();
				Craft::$app->fields->saveField($field);
			}

			// Create a tab
			$tabName           = $this->getDefaultTabName();
			$requiredFields    = array();
			$postedFieldLayout = array();

			// Add our new field
			if (isset($field) && $field->id != null)
			{
				$postedFieldLayout[$tabName][] = $field->id;
			}

			// Set the field layout
			$fieldLayout = Craft::$app->fields->assembleLayout($postedFieldLayout, $requiredFields);

			$fieldLayout->type = FormElement::class;
			// Set the tab to the form
			$form->setFieldLayout($fieldLayout);

			return $form;
		}

		return null;
	}

	/**
	 * This service allows duplicate fields from Layout
	 *
	 * @param SproutForms_FormModel $form
	 *
	 * @return SproutForms_FormModel | null
	 */
	public function getDuplicateLayout($form, $postFieldLayout)
	{
		if ($form && $postFieldLayout)
		{
			$postedFieldLayout = array();
			$requiredFields    = array();
			$tabs              = $postFieldLayout->getTabs();

			foreach ($tabs as $tab)
			{
				$fields = array();
				$fieldLayoutFields = $tab->getFields();

				foreach ($fieldLayoutFields as $fieldLayoutField)
				{
					$originalField = $fieldLayoutField->getField();

					$field               = new FieldModel();
					$field->name         = $originalField->name;
					$field->handle       = $originalField->handle;
					$field->instructions = $originalField->instructions;
					$field->required     = $fieldLayoutField->required;
					$field->translatable = $originalField->translatable;
					$field->type         = $originalField->type;

					if (isset($originalField->settings))
					{
						$field->settings = $originalField->settings;
					}

					Craft::$app->content->fieldContext = $form->getFieldContext();
					Craft::$app->content->contentTable = $form->getContentTable();
					// Save duplicate field
					Craft::$app->fields->saveField($field);
					array_push($fields, $field);

					if ($field->required)
					{
						array_push($requiredFields, $field->id);
					}
				}

				foreach ($fields as $field)
				{
					// Add our new field
					if (isset($field) && $field->id != null)
					{
						$postedFieldLayout[$tab->name][] = $field->id;
					}
				}
			}

			// Set the field layout
			$fieldLayout = Craft::$app->fields->assembleLayout($postedFieldLayout, $requiredFields);

			$fieldLayout->type = 'SproutForms_Form';

			return $fieldLayout;
		}

		return null;
	}

	/**
	 * This service allows add a field to a current FieldLayoutFieldRecord
	 *
	 * @param FieldModel            $field
	 * @param SproutForms_FormModel $form
	 * @param int                   $tabId
	 *
	 * @return boolean
	 */
	public function addFieldToLayout($field, $form, $tabId): bool
	{
		$response = false;

		if (isset($field) && isset($form))
		{
			$sortOrder = 0;

			$fieldLayoutFields = FieldLayoutFieldRecord::findAll([
				'tabId' => $tabId, 'layoutId' => $form->fieldLayoutId
			]);

			$sortOrder = count($fieldLayoutFields) + 1;

			$fieldRecord            = new FieldLayoutFieldRecord();
			$fieldRecord->layoutId  = $form->fieldLayoutId;
			$fieldRecord->tabId     = $tabId;
			$fieldRecord->fieldId   = $field->id;
			$fieldRecord->required  = 0;
			$fieldRecord->sortOrder = $sortOrder;

			$response = $fieldRecord->save(false);
		}

		return $response;
	}

	/**
	 * This service allows update a field to a current FieldLayoutFieldRecord
	 *
	 * @param FieldInterface        $field
	 * @param FormElement $form
	 * @param int                   $tabId
	 *
	 * @return boolean
	 */
	public function updateFieldToLayout($field, $form, $tabId): bool
	{
		$response = false;

		if (isset($field) && isset($form))
		{
			$fieldRecord  = FieldLayoutFieldRecord::findOne([
				'fieldId' => $field->id,
				'layoutId' => $form->fieldLayoutId
			]);

			if ($fieldRecord)
			{
				$fieldRecord->tabId = $tabId;

				$response = $fieldRecord->save(false);
			}
			else
			{
				SproutForms::error("Unable to find the FieldLayoutFieldRecord");
			}
		}

		return $response;
	}

	public function getDefaultTabName()
	{
		return SproutForms::t('Tab 1');
	}

	/**
	 * Loads the sprout modal field via ajax.
	 *
	 * @param FormElement $form
	 * @param FieldModel|null        $field
	 * @param int|null               $tabId
	 *
	 * @return array
	 */
	public function getModalFieldTemplate($form, $field = null, $tabId = null)
	{
		$fieldsService = Craft::$app->getFields();
		$request       = Craft::$app->getRequest();

		$data          = [];
		$data['tabId'] = null;
		$data['field'] = $fieldsService->createField(PlainText::class);

		if ($field)
		{
			$data['field'] = $field;
			$tabIdByPost   = $request->getBodyParam('tabId');

			if (isset($tabIdByPost))
			{
				$data['tabId'] = $tabIdByPost;
			}
			else if($tabId != null) //edit field
			{
				$data['tabId'] = $tabId;
			}

			if ($field->id != null)
			{
				$data['fieldId'] = $field->id;
			}
		}

		$data['sections'] = $form->getFieldLayout()->getTabs();
		$data['formId']   = $form->id;
		$view = Craft::$app->getView();

		$html = $view->renderTemplate('sproutforms/forms/_editFieldModal', $data);
		$js   = $view->getBodyHtml();
		$css  = $view->getHeadHtml();

		return [
			'html' => $html,
			'js'   => $js,
			'css'  => $css
		];
	}

	public function createDefaultField($type, $form)
	{
		$intanceField  = new $type;
		$fieldsService = Craft::$app->getFields();
		// get the field name and remove spaces
		$fieldName  = preg_replace('/\s+/','',$intanceField->displayName());
		$handleName = lcfirst($fieldName);

		$name   = $this->getFieldAsNew('name',$fieldName);
		$handle = $this->getFieldAsNew('handle',$handleName);

		$field = $fieldsService->createField([
			'type' => $type,
			'name' => $name,
			'handle' => $handle,
			'instructions' => '',
			// @todo - add locales
			'translationMethod' =>Field::TRANSLATION_METHOD_NONE,
		]);

		// Set our field context
		Craft::$app->content->fieldContext = $form->getFieldContext();
		Craft::$app->content->contentTable = $form->getContentTable();

		$fieldsService->saveField($field);

		return $field;
	}

	public function createNewTab($name, $sortOrder, FormElement $form)
	{
		$fieldLayout = $form->getFieldLayout();

		$tabRecord = new FieldLayoutTabRecord();
		$tabRecord->name = $name;
		$tabRecord->sortOrder = $sortOrder;
		$tabRecord->layoutId = $fieldLayout->id;

		$tabRecord->save();

		return $tabRecord;
	}

	/**
	 * Prepends a key/value pair to an array
	 *
	 * @see array_unshift()
	 *
	 * @param array  $haystack
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return array
	 */
	protected function prependKeyValue(array $haystack, $key, $value)
	{
		$haystack       = array_reverse($haystack, true);
		$haystack[$key] = $value;

		return array_reverse($haystack, true);
	}
}