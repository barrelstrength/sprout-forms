<?php

namespace barrelstrength\sproutforms\variables;

use Craft;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\models\FieldGroup;
use barrelstrength\sproutforms\models\FieldLayout;

/**
 * SproutForms provides an API for accessing information about forms. It is accessible from templates via `craft.sproutforms`.
 *
 */
class SproutFormsVariable
{
	/**
	 * @var ElementCriteriaModel

	public $entries;

	public function __construct()
	{
		$this->entries = Craft::$app->elements->getCriteria('SproutForms_Entry');
	}*/

	/**
	 * @return string
	 */
	public function getName()
	{
		$plugin = Craft::$app->plugins->getPlugin('sproutforms');

		return $plugin->getName();
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		$plugin = Craft::$app->plugins->getPlugin('sproutforms');

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
		$form          = SproutForms::$api->forms->getFormByHandle($formHandle);
		$entry         = SproutForms::$api->entries->getEntryModel($form);
		$fields        = SproutForms::$api->fields->getRegisteredFields();
		$templatePaths = SproutForms::$api->fields->getSproutFormsTemplates($form);

		// Set Tab template path
		Craft::$app->templates->setTemplatesPath($templatePaths['tab']);

		$bodyHtml = Craft::$app->templates->render(
			'tab', array(
				'form'                 => $form,
				'tabs'                 => $form->getFieldLayout()->getTabs(),
				'entry'                => $entry,
				'formFields'           => $fields,
				'thirdPartySubmission' => !!$form->submitAction,
				'displaySectionTitles' => $form->displaySectionTitles,
				'renderingOptions'     => $renderingOptions
			)
		);

		// Check if we need to update our Front-end Form Template Path
		Craft::$app->templates->setTemplatesPath($templatePaths['form']);

		// Build our complete form
		$formHtml = Craft::$app->templates->render(
			'form', array(
				'form'             => $form,
				'entry'            => $entry,
				'body'             => $bodyHtml,
				'errors'           => $entry->getErrors(),
				'renderingOptions' => $renderingOptions
			)
		);

		Craft::$app->templates->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

		return TemplateHelper::getRaw($formHtml);
	}

	public function displayTab($formTabHandle, array $renderingOptions = null)
	{
		list($formHandle, $tabHandle) = explode('.', $formTabHandle);
		$tabHandle = strtolower($tabHandle);

		if (!$formHandle || !$tabHandle)
		{
			return '';
		}

		$form          = SproutForms::$api->forms->getFormByHandle($formHandle);
		$entry         = SproutForms::$api->entries->getEntryModel($form);
		$fields        = SproutForms::$api->fields->getRegisteredFields();
		$templatePaths = SproutForms::$api->fields->getSproutFormsTemplates($form);

		// Set Tab template path
		Craft::$app->templates->setTemplatesPath($templatePaths['tab']);

		$tabIndex = null;

		foreach ($form->getFieldLayout()->getTabs() as $key => $tabInfo)
		{
			$currentTabHandle = str_replace('-', '', ElementHelper::createSlug($tabInfo->name));

			if ($tabHandle == $currentTabHandle)
			{
				$tabIndex = $key;
			}
		}

		if (is_null($tabIndex))
		{
			return false;
		}

		$layoutTabs = $form->getFieldLayout()->getTabs();
		$layoutTab  = isset($layoutTabs[$tabIndex]) ? $layoutTabs[$tabIndex] : null;

		// Build the HTML for our form tabs and fields
		$tabHtml = Craft::$app->templates->render(
			'tab',
			array(
				'form'                 => $form,
				'tabs'                 => array($layoutTab),
				'entry'                => $entry,
				'formFields'           => $fields,
				'displaySectionTitles' => $form->displaySectionTitles,
				'thirdPartySubmission' => !!$form->submitAction,
				'displaySectionTitles' => $form->displaySectionTitles,
				'renderingOptions'     => $renderingOptions
			)
		);

		Craft::$app->templates->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

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

		$form  = SproutForms::$api->forms->getFormByHandle($formHandle);
		$entry = SproutForms::$api->entries->getEntryModel($form);

		// Determine where our form and field template should come from
		$templatePaths = SproutForms::$api->fields->getSproutFormsTemplates($form);

		$field = $form->getField($fieldHandle);

		if ($field)
		{
			$fieldTypeClass  = get_class($field->getFieldType());
			$fieldTypeString = str_replace('Craft\\', '', str_replace('FieldType', '', $fieldTypeClass));
			$formField       = SproutForms::$api->fields->getRegisteredField($fieldTypeString);

			if ($formField)
			{
				$value = Craft::$app->request->getPost($field->handle);

				Craft::$app->templates->setTemplatesPath($formField->getTemplatesPath());

				$formField->getInputHtml($field, $value, $field->getFieldType()->getSettings(), $renderingOptions);

				// Set Tab template path
				Craft::$app->templates->setTemplatesPath($templatePaths['field']);

				// Build the HTML for our form field
				$fieldHtml = Craft::$app->templates->render(
					'field', array(
						'form'                 => $form,
						'value'                => $value,
						'field'                => $field,
						'required'             => $field->required,
						'element'              => $entry,
						'formField'            => $formField,
						'renderingOptions'     => $renderingOptions,
						'thirdPartySubmission' => !!$form->submitAction,
					)
				);

				Craft::$app->templates->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

				return TemplateHelper::getRaw($fieldHtml);
			}
		}
	}

	/**
	 * Gets a specific form. If no form is found, returns null
	 *
	 * @param  int $id
	 *
	 * @return mixed
	 */
	public function getFormById($id)
	{
		return SproutForms::$api->forms->getFormById($id);
	}

	/**
	 * Gets a specific form by handle. If no form is found, returns null
	 *
	 * @param  string $formHandle
	 *
	 * @return mixed
	 */
	public function getForm($formHandle)
	{
		return SproutForms::$api->forms->getFormByHandle($formHandle);
	}

	/**
	 * Get all forms
	 *
	 * @return array
	 */
	public function getAllForms()
	{
		return SproutForms::$api->forms->getAllForms();
	}

	/**
	 * Gets entry by ID
	 *
	 * @param int $id
	 *
	 * @return  SproutForms_EntryModel
	 */
	public function getEntryById($id)
	{
		return SproutForms::$api->entries->getEntryById($id);
	}

	/**
	 * Returns an active or new entry model
	 *
	 * @param SproutForms_FormModel $form
	 *
	 * @return mixed
	 */
	public function getEntry(SproutForms_FormModel $form)
	{
		return SproutForms::$api->entries->getEntryModel($form);
	}

	/**
	 * Gets last entry submitted
	 *
	 * @return SproutForms_EntryModel
	 */
	public function getLastEntry()
	{
		if (Craft::$app->httpSession->get('lastEntryId'))
		{
			$entryId = Craft::$app->httpSession->get('lastEntryId');
			$entry   = SproutForms::$api->entries->getEntryById($entryId);

			Craft::$app->httpSession->destroy('lastEntryId');
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
	public function getAllFormGroups($id = null)
	{
		return SproutForms::$api->groups->getAllFormGroups($id);
	}

	/**
	 * Gets all forms in a specific group by ID
	 *
	 * @param  int $id Group ID
	 *
	 * @return SproutForms_FormModel
	 */
	public function getFormsByGroupId($id)
	{
		return SproutForms::$api->groups->getFormsByGroupId($id);
	}

	/**
	 * @see SproutForms::$api->fields->prepareFieldTypeSelection()
	 *
	 * @return array
	 */
	public function prepareFieldTypeSelection()
	{
		return SproutForms::$api->fields->prepareFieldTypeSelection();
	}

	public function multiStepForm($settings)
	{
		$currentStep = isset($settings['currentStep']) ? $settings['currentStep'] : null;
		$totalSteps  = isset($settings['totalSteps']) ? $settings['totalSteps'] : null;

		if (!$currentStep OR !$totalSteps)
		{
			return;
		}

		if ($currentStep == 1)
		{
			// Make sure we are starting from scratch
			Craft::$app->httpSession->remove('multiStepForm');
			Craft::$app->httpSession->remove('multiStepFormEntryId');
			Craft::$app->httpSession->remove('currentStep');
			Craft::$app->httpSession->remove('totalSteps');
		}

		Craft::$app->httpSession->add('multiStepForm', true);
		Craft::$app->httpSession->add('currentStep', $currentStep);
		Craft::$app->httpSession->add('totalSteps', $totalSteps);
	}

	/**
	 * @param $type
	 *
	 * @throws Exception
	 * @return null|SproutFormsBaseField
	 */
	public function getRegisteredField($type)
	{
		$fields = SproutForms::$api->fields->getRegisteredFields();

		foreach ($fields as $field)
		{
			if ($field->getType() == $type)
			{
				return $field;
			}
		}

		$message = Craft::t('{type} field does not support front-end display using Sprout Forms.', array(
			'type' => $type
		));

		SproutFormsPlugin::log($message, LogLevel::Warning);

		if (Craft::$app->config->get('devMode'))
		{
			throw new Exception(Craft::t($message));
		}
	}

	public function getTemplatesPath()
	{
		return Craft::$app->path->getTemplatesPath();
	}

	/**
	 * @param array $variables
	 */
	public function addFieldVariables(array $variables)
	{
		SproutFormsBaseField::addFieldVariables($variables);
	}

	/**
	 * @return bool
	 */
	public function canCreateExamples()
	{
		return SproutForms::$api->canCreateExamples();
	}

	/**
	 * @return bool
	 */
	public function hasExamples()
	{
		return SproutForms::$api->hasExamples();
	}

	/**
	 * @param string
	 *
	 * @return bool
	 */
	public function isPluginInstalled($plugin)
	{
		$plugins = Craft::$app->plugins->getAllPlugins();

		if (array_key_exists($plugin, $plugins))
		{
			$invisibleCaptcha = $plugins[$plugin];

			/* @todo the isInstalled variable was removed on craft3
			if ($invisibleCaptcha->isInstalled)
			{
				return true;
			}
			*/
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isInvisibleCaptchaEnabled()
	{
		$plugins = Craft::$app->plugins->getPlugins(false);

		if (array_key_exists('sproutinvisiblecaptcha', $plugins))
		{
			$invisibleCaptcha = $plugins["sproutinvisiblecaptcha"];

			if ($invisibleCaptcha->getSettings()->sproutFormsDisplayFormTagOutput
				and $invisibleCaptcha->isInstalled
			)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function getEntryStatuses()
	{
		return SproutForms::$api->entries->getAllEntryStatuses();
	}
}

