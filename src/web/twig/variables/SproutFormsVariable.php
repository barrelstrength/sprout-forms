<?php

namespace barrelstrength\sproutforms\web\twig\variables;

use barrelstrength\sproutforms\base\Condition;
use barrelstrength\sproutforms\elements\db\EntryQuery;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\services\Forms;
use Craft;
use craft\base\ElementInterface;
use craft\base\FieldInterface;
use craft\errors\MissingComponentException;
use craft\helpers\ArrayHelper;
use craft\helpers\Template as TemplateHelper;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\base\FormField;
use ReflectionException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Markup;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * SproutForms provides an API for accessing information about forms. It is accessible from templates via `craft.sproutForms`.
 *
 */
class SproutFormsVariable
{
    /**
     * @return string
     */
    public function getName(): string
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

        return $plugin->name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->plugins->getPlugin('sprout-forms');

        return $plugin->getVersion();
    }

    public function getFormByHandle($formHandle) {
        return SproutForms::$app->forms->getFormByHandle($formHandle);
    }
    public function getCurrentFormEntry($form) {
        return SproutForms::$app->entries->getEntry($form);
    }
    public function setSiteTemplatePath() {
        Craft::$app->getView()->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());
    }
    public function setTabTemplatePath($form) {
        $templatePaths = SproutForms::$app->forms->getFormTemplatePaths($form);

        // Set Tab template path
        Craft::$app->getView()->setTemplatesPath($templatePaths['tab']);
    }
    public function setFieldTemplatePathAndGetInputHtml($form, $entry, $field, $renderingOptions) {
        if ($renderingOptions !== null) {
            $renderingOptions = [
                'fields' => $renderingOptions['fields'] ?? null
            ];
        }

        $view = Craft::$app->getView();

        $templatePaths = SproutForms::$app->forms->getFormTemplatePaths($form);

        $view->setTemplatesPath($field->getTemplatesPath());

        $inputFilePath = $templatePaths['fields'].DIRECTORY_SEPARATOR.$field->getFieldInputFolder().DIRECTORY_SEPARATOR.'input';

        // Allow input field templates to be overridden
        foreach (Craft::$app->getConfig()->getGeneral()->defaultTemplateExtensions as $extension) {
            if (file_exists($inputFilePath.'.'.$extension)) {

                // Override Field Input template path
                $view->setTemplatesPath($templatePaths['fields']);
                break;
            }
        }

        $fieldRenderingOptions = $renderingOptions['fields'][$field->handle] ?? null;

        $value = $entry->getFieldValue($field->handle);

        $inputHtml = $field->getFrontEndInputHtml($value, $fieldRenderingOptions);

        // Set Field template path (we handled the case for overriding the field input templates above)
        $view->setTemplatesPath($templatePaths['field']);

        return $inputHtml;
    }

    /**
     * Returns a complete form for display in template
     *
     * @param            $formHandle
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws \Exception
     * @throws Exception
     */
    public function displayFormNew($formHandle, array $renderingOptions = null): Markup
    {
        /**
         * @var $form Form
         */
//        $form = SproutForms::$app->forms->getFormByHandle($formHandle);
//
//        if (!$form) {
//            throw new Exception('Unable to find form with the handle: '.$formHandle);
//        }

        $view = Craft::$app->getView();

//        $entry = SproutForms::$app->entries->getEntry($form);

//        $templatePaths = SproutForms::$app->forms->getFormTemplatePaths($form);

        // Check if we need to update our Front-end Form Template Path
//        $view->setTemplatesPath($templatePaths['form']);

        $formHtml = $view->renderTemplate('sprout-forms/form-templates/accessible/form', [
                'formHandle' => $formHandle,
//                'entry' => $entry,
                'renderingOptions' => $renderingOptions
            ]
        );

        // Build our complete form
//        $formHtml = $view->renderTemplate('sprout-forms/form-templates/accessible/form', [
//                'form' => $form,
//                'entry' => $entry,
//                'renderingOptions' => $renderingOptions
//            ]
//        );

        $view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

        return TemplateHelper::raw($formHtml);
    }

    /**
     * Returns a complete form for display in template
     *
     * @param            $formHandle
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws \Exception
     * @throws Exception
     */
    public function displayForm($formHandle, array $renderingOptions = null): Markup
    {
        /**
         * @var $form Form
         */
        $form = SproutForms::$app->forms->getFormByHandle($formHandle);

        if (!$form) {
            throw new Exception('Unable to find form with the handle: '.$formHandle);
        }

        $view = Craft::$app->getView();

        $entry = SproutForms::$app->entries->getEntry($form);

        $templatePaths = SproutForms::$app->forms->getFormTemplatePaths($form);

        // Check if we need to update our Front-end Form Template Path
        $view->setTemplatesPath($templatePaths['form']);

        // Build our complete form
        $formHtml = $view->renderTemplate('form', [
                'form' => $form,
                'entry' => $entry,
                'renderingOptions' => $renderingOptions
            ]
        );

        $view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

        return TemplateHelper::raw($formHtml);
    }

    /**
     * @param Form       $form
     * @param int        $tabId
     * @param array|null $renderingOptions
     *
     * @return bool|Markup
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function displayTab(Form $form, int $tabId, array $renderingOptions = null)
    {
        if (!$form) {
            throw new Exception('The displayTab tag requires a Form model.');
        }

        if (!$tabId) {
            throw new Exception('The displayTab tag requires a Tab ID.');
        }

        $view = Craft::$app->getView();

        $entry = SproutForms::$app->entries->getEntry($form);

        $templatePaths = SproutForms::$app->forms->getFormTemplatePaths($form);

        // Set Tab template path
        $view->setTemplatesPath($templatePaths['tab']);

        $tabIndex = null;

        $layoutTabs = $form->getFieldLayout()->getTabs();

        foreach ($layoutTabs as $key => $tabInfo) {
            if ($tabId == $tabInfo->id) {
                $tabIndex = $key;
            }
        }

        if ($tabIndex === null) {
            return false;
        }

        $layoutTab = $layoutTabs[$tabIndex] ?? null;

        // Build the HTML for our form tabs and fields
        $tabHtml = $view->renderTemplate('tab', [
            'form' => $form,
            'entry' => $entry,
            'tabs' => [$layoutTab],
            'renderingOptions' => $renderingOptions
        ]);

        $siteTemplatesPath = Craft::$app->path->getSiteTemplatesPath();

        $view->setTemplatesPath($siteTemplatesPath);

        return TemplateHelper::raw($tabHtml);
    }

    /**
     * Returns a complete field for display in template
     *
     * @param Form       $form
     * @param FormField  $field
     * @param array|null $renderingOptions
     *
     * @return Markup
     * @throws Exception
     * @throws ReflectionException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function displayField(Form $form, FormField $field, array $renderingOptions = null): Markup
    {
        if (!$form) {
            throw new Exception('The displayField tag requires a Form model.');
        }

        if (!$field) {
            throw new Exception('The displayField tag requires a Field model.');
        }

        if ($renderingOptions !== null) {
            $renderingOptions = [
                'fields' => $renderingOptions['fields'] ?? null
            ];
        }

        $view = Craft::$app->getView();

        $entry = SproutForms::$app->entries->getEntry($form);

        $templatePaths = SproutForms::$app->forms->getFormTemplatePaths($form);

        $view->setTemplatesPath($field->getTemplatesPath());

        $inputFilePath = $templatePaths['fields'].DIRECTORY_SEPARATOR.$field->getFieldInputFolder().DIRECTORY_SEPARATOR.'input';

        // Allow input field templates to be overridden
        foreach (Craft::$app->getConfig()->getGeneral()->defaultTemplateExtensions as $extension) {
            if (file_exists($inputFilePath.'.'.$extension)) {

                // Override Field Input template path
                $view->setTemplatesPath($templatePaths['fields']);
                break;
            }
        }

        $fieldRenderingOptions = $renderingOptions['fields'][$field->handle] ?? null;

        $value = $entry->getFieldValue($field->handle);

        $inputHtml = $field->getFrontEndInputHtml($value, $fieldRenderingOptions);

        // Set Field template path (we handled the case for overriding the field input templates above)
        $view->setTemplatesPath($templatePaths['field']);

        // Build the HTML for our form field
        $fieldHtml = $view->renderTemplate('field', [
                'form' => $form,
                'entry' => $entry,
                'field' => $field,
                'input' => $inputHtml,
                'renderingOptions' => $renderingOptions
            ]
        );

        $view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

        return TemplateHelper::raw($fieldHtml);
    }

    /**
     * Gets a specific form. If no form is found, returns null
     *
     * @param int $id
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
     * @param string $formHandle
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
    public function getAllForms(): array
    {
        return SproutForms::$app->forms->getAllForms();
    }

    /**
     * Gets entry by ID
     *
     * @param $id
     *
     * @return ElementInterface|null
     */
    public function getEntryById($id)
    {
        return SproutForms::$app->entries->getEntryById($id);
    }

    /**
     * Returns an active or new entry model
     *
     * @param Form $form
     *
     * @return mixed
     */
    public function getEntry(Form $form)
    {
        return SproutForms::$app->entries->getEntry($form);
    }

    /**
     * Set an active entry for use in your Form Templates
     *
     * See the Entries service setEntry method for more details.
     *
     * @param Form         $form
     * @param EntryElement $entry
     */
    public function setEntry(Form $form, Entry $entry)
    {
        SproutForms::$app->entries->setEntry($form, $entry);
    }

    /**
     * Gets last entry submitted
     *
     * @return array|ElementInterface|null
     * @throws MissingComponentException
     */
    public function getLastEntry()
    {
        if (Craft::$app->getSession()->get('lastEntryId')) {
            $entryId = Craft::$app->getSession()->get('lastEntryId');
            $entry = SproutForms::$app->entries->getEntryById($entryId);

            Craft::$app->getSession()->remove('lastEntryId');
        }

        return $entry ?? null;
    }

    /**
     * Gets Form Groups
     *
     * @param int $id Group ID (optional)
     *
     * @return array
     */
    public function getAllFormGroups($id = null): array
    {
        return SproutForms::$app->groups->getAllFormGroups($id);
    }

    /**
     * Gets all forms in a specific group by ID
     *
     * @param $id
     *
     * @return Form[]
     */
    public function getFormsByGroupId($id): array
    {
        return SproutForms::$app->groups->getFormsByGroupId($id);
    }

    /**
     * @param $settings
     *
     * @throws MissingComponentException
     */
    public function multiStepForm($settings)
    {
        $currentStep = $settings['currentStep'] ?? null;
        $totalSteps = $settings['totalSteps'] ?? null;

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

        Craft::$app->getSession()->set('multiStepForm', true);
        Craft::$app->getSession()->set('currentStep', $currentStep);
        Craft::$app->getSession()->set('totalSteps', $totalSteps);
    }

    /**
     * @param $type
     *
     * @return FormField|mixed|null
     * @throws Exception
     */
    public function getRegisteredField($type)
    {
        $fields = SproutForms::$app->fields->getRegisteredFields();

        foreach ($fields as $field) {
            if ($field->getType() == $type) {
                return $field;
            }
        }

        $message = Craft::t('sprout-forms', '{type} field does not support front-end display using Sprout Forms.', [
                'type' => $type
            ]
        );

        Craft::error($message, __METHOD__);

        if (Craft::$app->getConfig()->getGeneral()->devMode) {
            throw new Exception($message);
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getTemplatesPath()
    {
        return Craft::$app->getView()->getTemplatesPath();
    }

    /**
     * @param array $variables
     */
    public function addFieldVariables(array $variables)
    {
        Forms::addFieldVariables($variables);
    }

    /**
     * @param string
     *
     * @return bool
     */
    public function isPluginInstalled($plugin): bool
    {
        $plugins = Craft::$app->plugins->getAllPlugins();

        if (array_key_exists($plugin, $plugins)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getEntryStatuses(): array
    {
        return SproutForms::$app->entryStatuses->getAllEntryStatuses();
    }

    /**
     * @return array|FormField[]
     */
    public function getRegisteredFields(): array
    {
        return SproutForms::$app->fields->getRegisteredFields();
    }

    /**
     * @return array
     */
    public function getRegisteredFieldsByGroup(): array
    {
        return SproutForms::$app->fields->getRegisteredFieldsByGroup();
    }

    /**
     * @param $registeredFields
     * @param $sproutFormsFields
     *
     * @return mixed
     */
    public function getCustomFields($registeredFields, $sproutFormsFields)
    {
        foreach ($sproutFormsFields as $group) {
            foreach ($group as $field) {
                unset($registeredFields[$field]);
            }
        }

        return $registeredFields;
    }

    /**
     * @param $field
     *
     * @return string
     */
    public function getFieldClassName($field): string
    {
        return get_class($field);
    }

    /**
     * @return array
     */
    public function getAllCaptchas(): array
    {
        return SproutForms::$app->forms->getAllCaptchas();
    }

    /**
     * @param Form|null $form
     * @param bool      $generalSettings
     *
     * @return array
     */
    public function getTemplateOptions(Form $form = null, $generalSettings = false): array
    {
        $defaultFormTemplates = new AccessibleTemplates();

//        if ($generalSettings) {
//            $options[] = [
//                'optgroup' => Craft::t('sprout-forms', 'Global Templates')
//            ];
//
//            $options[] = [
//                'label' => Craft::t('sprout-forms', 'Default Form Templates'),
//                'value' => null
//            ];
//        }

        $templates = SproutForms::$app->forms->getAllFormTemplates();
        $templateIds = [];

//        if ($generalSettings) {
//            $options[] = [
//                'optgroup' => Craft::t('sprout-forms', 'Form-Specific Templates')
//            ];
//        }

        $formTemplates = [];
        foreach ($templates as $template) {
            $formTemplates[] = [
                'hint' => $template->getName(),
                'name' => $template->getTemplateFolder()
            ];
//            $templateIds[] = $template->getTemplateId();
        }

//        $templateFolder = null;
//        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');

//        if ($plugin) {
//            $settings = $plugin->getSettings();
//        }

//        $templateFolder = $form->formTemplate ?? $settings->formTemplateDefaultValue ?? $defaultFormTemplates->getPath();
//
//        $options[] = [
//            'optgroup' => Craft::t('sprout-forms', 'Custom Template Folder')
//        ];
//
//        if (!in_array($templateFolder, $templateIds, false) && $templateFolder != '') {
//            $options[] = [
//                'label' => $templateFolder,
//                'value' => $templateFolder
//            ];
//        }

        $suggestions[] = [
            'label' => Craft::t('sprout-forms', 'Form Templates'),
            'data' => $formTemplates
        ];

        return $suggestions;

//        [
//{
//    label: 'Common Dividers'|t('sprout-seo'),
//data: [
//{ hint: 'Dash'|t('sprout-seo'), name: '-' },
//{ hint: 'Bullet'|t('sprout-seo'), name: '•' },
//{ hint: 'Pipe'|t('sprout-seo'), name: '|' },
//{ hint: 'Forward Slash'|t('sprout-seo'), name: '/' },
//{ hint: 'Colon'|t('sprout-seo'), name: ':' }
//]
//}
//]
    }

    /**
     * Returns the available template path suggestions for template inputs.
     *
     * @return string[]
     */
    public function getTemplateFolderSuggestions(): array
    {
        // Get all the template files sorted by path length
        $root = Craft::$app->getPath()->getSiteTemplatesPath();

        if (!is_dir($root)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        /** @var \SplFileInfo[] $files */
        $files = [];
        $pathLengths = [];

        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isDir()) {
                $files[] = $file;
                $pathLengths[] = strlen($file->getRealPath());
            }
        }

        array_multisort($pathLengths, SORT_NUMERIC, $files);

        // Now build the suggestions array
        $suggestions = [];
        $templates = [];
        $sites = [];
        $config = Craft::$app->getConfig()->getGeneral();
        $rootLength = strlen($root);

        foreach (Craft::$app->getSites()->getAllSites() as $site) {
            $sites[$site->handle] = Craft::t('sprout-forms', $site->name);
        }

        foreach ($files as $file) {

            $template = substr($file->getRealPath(), $rootLength + 1);

            // Can we chop off the extension?
//            $extension = $file->getExtension();
//            if (in_array($extension, $config->defaultTemplateExtensions, true)) {
//                $template = substr($template, 0, strlen($template) - (strlen($extension) + 1));
//            }

            $hint = null;

            // Is it in a site template directory?
            foreach ($sites as $handle => $name) {
                if (strpos($template, $handle . DIRECTORY_SEPARATOR) === 0) {
                    $hint = $name;
                    $template = substr($template, strlen($handle) + 1);
                    break;
                }
            }

            // Avoid listing the same template path twice (considering localized templates)
            if (isset($templates[$template]) || $template === false) {
                continue;
            }

            $templates[$template] = true;
            $suggestions[] = [
                'name' => $template,
                'hint' => $hint,
            ];
        }

        ArrayHelper::multisort($suggestions, 'name');

        return [
            [
                'label' => Craft::t('sprout-forms', 'Template Folders'),
                'data' => $suggestions,
            ]
        ];
    }

    /**
     * Returns a new EntryQuery instance.
     *
     * @param mixed $criteria
     *
     * @return EntryQuery
     */
    public function entries($criteria = null): EntryQuery
    {
        $query = EntryElement::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }

    /**
     * @return array
     */
    public function getIntegrationOptions(): array
    {
        $integrations = SproutForms::$app->integrations->getAllIntegrations();

        $options[] = [
            'label' => Craft::t('sprout-forms', 'Add Integration...'),
            'value' => ''
        ];

        foreach ($integrations as $integration) {
            $options[] = [
                'label' => $integration::displayName(),
                'value' => get_class($integration)
            ];
        }

        return $options;
    }


    /**
     * @param $field
     *
     * @return mixed
     */
    public function validateField($field)
    {
        return $field instanceof FormField;
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public function getFieldClass($field)
    {
        return get_class($field);
    }

    /**
     * @param $formFieldHandle
     * @param $formId
     *
     * @return FieldInterface|null
     * @throws InvalidConfigException
     */
    public function getFormField($formFieldHandle, $formId)
    {
        $form = SproutForms::$app->forms->getFormById($formId);

        return $form->getField($formFieldHandle);
    }

    /**
     * @param $conditionClass
     * @param $formField
     *
     * @return Condition
     */
    public function getFieldCondition($conditionClass, $formField): Condition
    {
        return new $conditionClass(['formField' => $formField]);
    }
}

