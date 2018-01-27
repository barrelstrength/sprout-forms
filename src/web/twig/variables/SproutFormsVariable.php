<?php

namespace barrelstrength\sproutforms\web\twig\variables;

use Craft;
use craft\helpers\Template as TemplateHelper;
use craft\helpers\ElementHelper;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\models\FieldGroup;
use barrelstrength\sproutforms\models\FieldLayout;
use barrelstrength\sproutforms\contracts\SproutFormsBaseField;

/**
 * SproutForms provides an API for accessing information about forms. It is accessible from templates via `craft.sproutforms`.
 *
 */
class SproutFormsVariable
{
    /**
     * @var ElementCriteriaModel
     */
    public $entries;

    public function __construct()
    {
        //$this->entries = Craft::$app->elements->getCriteria('SproutForms_Entry');
        $this->entries = EntryElement::find();
    }

    /**
     * @return string
     */
    public function getName()
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

        return $plugin->getName();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

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
        $form = SproutForms::$app->forms->getFormByHandle($formHandle);

        if (!$form) {
            throw new \Exception(Craft::t('sprout-forms','Unable to find form with the handle `{handle}`', [
                'handle' => $formHandle
            ]));
        }

        $entry = SproutForms::$app->entries->getEntry($form);
        $fields = SproutForms::$app->fields->getRegisteredFields();
        $templatePaths = SproutForms::$app->fields->getSproutFormsTemplates($form);

        $view = Craft::$app->getView();

        // Set Tab template path
        $view->setTemplatesPath($templatePaths['tab']);

        $bodyHtml = $view->renderTemplate(
            'tab', [
                'form' => $form,
                'tabs' => $form->getFieldLayout()->getTabs(),
                'entry' => $entry,
                'formFields' => $fields,
                'thirdPartySubmission' => !!$form->submitAction,
                'displaySectionTitles' => $form->displaySectionTitles,
                'renderingOptions' => $renderingOptions
            ]
        );

        // Check if we need to update our Front-end Form Template Path
        $view->setTemplatesPath($templatePaths['form']);

        // Build our complete form
        $formHtml = $view->renderTemplate(
            'form', [
                'form' => $form,
                'entry' => $entry,
                'body' => $bodyHtml,
                'errors' => $entry->getErrors(),
                'renderingOptions' => $renderingOptions
            ]
        );

        $view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

        return TemplateHelper::raw($formHtml);
    }

    public function getFieldClass($field): string
    {
        return get_class($field);
    }

    public function displayTab($formTabHandle, array $renderingOptions = null)
    {
        list($formHandle, $tabHandle) = explode('.', $formTabHandle);
        $tabHandle = strtolower($tabHandle);

        if (!$formHandle || !$tabHandle) {
            return '';
        }

        $form = SproutForms::$app->forms->getFormByHandle($formHandle);
        $entry = SproutForms::$app->entries->getEntry($form);
        $fields = SproutForms::$app->fields->getRegisteredFields();
        $templatePaths = SproutForms::$app->fields->getSproutFormsTemplates($form);

        $view = Craft::$app->getView();

        // Set Tab template path
        $view->setTemplatesPath($templatePaths['tab']);

        $tabIndex = null;

        foreach ($form->getFieldLayout()->getTabs() as $key => $tabInfo) {
            $currentTabHandle = str_replace('-', '', ElementHelper::createSlug($tabInfo->name));

            if ($tabHandle == $currentTabHandle) {
                $tabIndex = $key;
            }
        }

        if (is_null($tabIndex)) {
            return false;
        }

        $layoutTabs = $form->getFieldLayout()->getTabs();
        $layoutTab = isset($layoutTabs[$tabIndex]) ? $layoutTabs[$tabIndex] : null;

        // Build the HTML for our form tabs and fields
        $tabHtml = $view->renderTemplate(
            'tab',
            [
                'form' => $form,
                'tabs' => [$layoutTab],
                'entry' => $entry,
                'formFields' => $fields,
                'displaySectionTitles' => $form->displaySectionTitles,
                'thirdPartySubmission' => !!$form->submitAction,
                'displaySectionTitles' => $form->displaySectionTitles,
                'renderingOptions' => $renderingOptions
            ]
        );

        $view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

        return TemplateHelper::raw($tabHtml);
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

        if (empty($formHandle) || empty($fieldHandle)) {
            return false;
        }

        if (!is_null($renderingOptions)) {
            $renderingOptions = [
                'fields' => [
                    $fieldHandle => $renderingOptions
                ]
            ];
        }

        $form = SproutForms::$app->forms->getFormByHandle($formHandle);
        $entry = SproutForms::$app->entries->getEntry($form);

        $view = Craft::$app->getView();

        // Determine where our form and field template should come from
        $templatePaths = SproutForms::$app->fields->getSproutFormsTemplates($form);

        $field = $form->getField($fieldHandle);

        if ($field) {
            $fieldTypeString = $this->getFieldClass($field);
            $formField = SproutForms::$app->fields->getRegisteredField($fieldTypeString);

            if ($formField) {
                $value = Craft::$app->request->getBodyParam($field->handle);

                $view->setTemplatesPath($formField->getTemplatesPath());

                $formField->getInputHtml($field, $value, $field->getSettings(), $renderingOptions);

                // Set Tab template path
                $view->setTemplatesPath($templatePaths['field']);

                // Build the HTML for our form field
                $fieldHtml = $view->renderTemplate(
                    'field', [
                        'form' => $form,
                        'value' => $value,
                        'field' => $field,
                        'required' => $field->required,
                        'element' => $entry,
                        'formField' => $formField,
                        'renderingOptions' => $renderingOptions,
                        'thirdPartySubmission' => !!$form->submitAction,
                    ]
                );

                $view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

                return TemplateHelper::raw($fieldHtml);
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
        return SproutForms::$app->forms->getFormById($id);
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
        return SproutForms::$app->forms->getFormByHandle($formHandle);
    }

    /**
     * Get all forms
     *
     * @return array
     */
    public function getAllForms()
    {
        return SproutForms::$app->forms->getAllForms();
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
        return SproutForms::$app->entries->getEntryById($id);
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
        return SproutForms::$app->entries->getEntryModel($form);
    }

    /**
     * Gets last entry submitted
     *
     * @return SproutForms_EntryModel
     */
    public function getLastEntry()
    {
        if (Craft::$app->getSession()->get('lastEntryId')) {
            $entryId = Craft::$app->getSession()->get('lastEntryId');
            $entry = SproutForms::$app->entries->getEntryById($entryId);

            Craft::$app->getSession()->destroy('lastEntryId');
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
        return SproutForms::$app->groups->getAllFormGroups($id);
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
        return SproutForms::$app->groups->getFormsByGroupId($id);
    }

    /**
     * @see SproutForms::$app->fields->prepareFieldTypeSelection()
     *
     * @return array
     */
    public function prepareFieldTypeSelection()
    {
        return SproutForms::$app->fields->prepareFieldTypeSelection();
    }

    public function multiStepForm($settings)
    {
        $currentStep = isset($settings['currentStep']) ? $settings['currentStep'] : null;
        $totalSteps = isset($settings['totalSteps']) ? $settings['totalSteps'] : null;

        if (!$currentStep OR !$totalSteps) {
            return;
        }

        if ($currentStep == 1) {
            // Make sure we are starting from scratch
            Craft::$app->getSession()->remove('multiStepForm');
            Craft::$app->getSession()->remove('multiStepFormEntryId');
            Craft::$app->getSession()->remove('currentStep');
            Craft::$app->getSession()->remove('totalSteps');
        }

        Craft::$app->getSession()->add('multiStepForm', true);
        Craft::$app->getSession()->add('currentStep', $currentStep);
        Craft::$app->getSession()->add('totalSteps', $totalSteps);
    }

    /**
     * @param $type
     *
     * @throws Exception
     * @return null|SproutFormsBaseField
     */
    public function getRegisteredField($type)
    {
        $fields = SproutForms::$app->fields->getRegisteredFields();

        foreach ($fields as $field) {
            if ($field->getType() == $type) {
                return $field;
            }
        }

        $message = Craft::t('sprout-forms','{type} field does not support front-end display using Sprout Forms.', [
                'type' => $type
            ]
        );

        SproutForms::error($message);

        if (isset(Craft::$app->getConfig()->getGeneral()->devMode) && Craft::$app->getConfig()->getGeneral()->devMode) {
            throw new \Exception($message);
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
        return SproutForms::$app->canCreateExamples();
    }

    /**
     * @return bool
     */
    public function hasExamples()
    {
        return SproutForms::$app->hasExamples();
    }

    /**
     * @param string
     *
     * @return bool
     */
    public function isPluginInstalled($plugin)
    {
        $plugins = Craft::$app->plugins->getAllPlugins();

        if (array_key_exists($plugin, $plugins)) {
            $invisibleCaptcha = $plugins[$plugin];

            /* @todo the isInstalled variable was removed on craft3
             * if ($invisibleCaptcha->isInstalled)
             * {
             * return true;
             * }
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

        if (array_key_exists('sproutinvisiblecaptcha', $plugins)) {
            $invisibleCaptcha = $plugins["sproutinvisiblecaptcha"];

            if ($invisibleCaptcha->getSettings()->sproutFormsDisplayFormTagOutput
                and $invisibleCaptcha->isInstalled
            ) {
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
        return SproutForms::$app->entries->getAllEntryStatuses();
    }

    /**
     * @param FieldModel
     *
     * @return bool
     */
    public function getRegisteredFieldByModel($field)
    {
        $registeredFields = SproutForms::$app->fields->getRegisteredFields();

        foreach ($registeredFields as $sproutFormfield) {
            if ($sproutFormfield->getType() == get_class($field) && get_class($field) == 'craft\fields\PlainText') {
                return $sproutFormfield;
            }
        }

        return null;
    }

    public function getRegisteredFields()
    {
        return SproutForms::$app->fields->getRegisteredFields();
    }

		public function getRegisteredFieldsByGroup()
		{
			return SproutForms::$app->fields->getRegisteredFieldsByGroup();
		}

	public function getCustomFields($registeredFields, $sproutFormsFields)
	{
		foreach ($sproutFormsFields as $group) {
			foreach ($group as $field) {
				unset($registeredFields[$field]);
			}
		}

		return $registeredFields;
	}

    public function getFieldClassName($field)
    {
        return get_class($field);
    }
}

