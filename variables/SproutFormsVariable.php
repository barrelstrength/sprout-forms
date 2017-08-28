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
		craft()->templates->setTemplatesPath($templatePaths['tab']);

		$bodyHtml = craft()->templates->render(
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
		craft()->templates->setTemplatesPath($templatePaths['form']);

		// Build our complete form
		$formHtml = craft()->templates->render(
			'form', array(
				'form'             => $form,
				'entry'            => $entry,
				'body'             => $bodyHtml,
				'errors'           => $entry->getErrors(),
				'renderingOptions' => $renderingOptions
			)
		);

		craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

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

		$form          = sproutForms()->forms->getFormByHandle($formHandle);
		$entry         = sproutForms()->entries->getEntryModel($form);
		$fields        = sproutForms()->fields->getRegisteredFields();
		$templatePaths = sproutForms()->fields->getSproutFormsTemplates($form);

		// Set Tab template path
		craft()->templates->setTemplatesPath($templatePaths['tab']);

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
		$tabHtml = craft()->templates->render(
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

		craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

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

		$field = $form->getField($fieldHandle);

		if ($field)
		{
			$fieldTypeClass  = get_class($field->getFieldType());
			$fieldTypeString = str_replace('Craft\\', '', str_replace('FieldType', '', $fieldTypeClass));
			$formField       = sproutForms()->fields->getRegisteredField($fieldTypeString);

			if ($formField)
			{
				$value = craft()->request->getPost($field->handle);

				craft()->templates->setTemplatesPath($formField->getTemplatesPath());

				$formField->getInputHtml($field, $value, $field->getFieldType()->getSettings(), $renderingOptions);

				// Set Tab template path
				craft()->templates->setTemplatesPath($templatePaths['field']);

				// Build the HTML for our form field
				$fieldHtml = craft()->templates->render(
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

				craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

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
		return sproutForms()->forms->getFormById($id);
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
	 *
	 * @return  SproutForms_EntryModel
	 */
	public function getEntryById($id)
	{
		return sproutForms()->entries->getEntryById($id);
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
		return sproutForms()->entries->getEntryModel($form);
	}

	/**
	 * Gets last entry submitted
	 *
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
	 *
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
	 *
	 * @return SproutForms_FormModel
	 */
	public function getFormsByGroupId($id)
	{
		return sproutForms()->groups->getFormsByGroupId($id);
	}

	/**
	 * @see sproutForms()->fields->prepareFieldTypeSelection()
	 *
	 * @return array
	 */
	public function prepareFieldTypeSelection()
	{
		return sproutForms()->fields->prepareFieldTypeSelection();
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
	 * @throws Exception
	 * @return null|SproutFormsBaseField
	 */
	public function getRegisteredField($type)
	{
		$fields = sproutForms()->fields->getRegisteredFields();

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

		if (craft()->config->get('devMode'))
		{
			throw new Exception(Craft::t($message));
		}
	}

	public function getTemplatesPath()
	{
		return craft()->path->getTemplatesPath();
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
		return sproutForms()->canCreateExamples();
	}

	/**
	 * @return bool
	 */
	public function hasExamples()
	{
		return sproutForms()->hasExamples();
	}

	/**
	 * @param string
	 *
	 * @return bool
	 */
	public function isPluginInstalled($plugin)
	{
		$plugins = craft()->plugins->getPlugins(false);

		if (array_key_exists($plugin, $plugins))
		{
			$invisibleCaptcha = $plugins[$plugin];

			if ($invisibleCaptcha->isInstalled)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isInvisibleCaptchaEnabled()
	{
		$plugins = craft()->plugins->getPlugins(false);

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
		return sproutForms()->entries->getAllEntryStatuses();
	}

	/**
	 * @return bool
	 */
	public function getSettings()
	{
		$settings = craft()->plugins->getPlugin('sproutforms')->getSettings();

		return $settings;
	}

	/**
	 * @return null|HttpException
	 */
	public function userCanViewEntries()
	{
		sproutForms()->entries->userCanViewEntries();
	}

	/**
	 * @return null|HttpException
	 */
	public function userCanEditForms()
	{
		sproutForms()->forms->userCanEditForms();
	}
}
