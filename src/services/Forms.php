<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\base\FormTemplates;
use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\migrations\CreateFormContentTable;
use barrelstrength\sproutforms\records\Form as FormRecord;
use barrelstrength\sproutforms\rules\FieldRule;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\MigrationHelper;
use craft\helpers\StringHelper;
use Throwable;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 *
 * @property array    $allEnabledCaptchas
 * @property array    $allCaptchas
 * @property string[] $allFormTemplateTypes
 * @property string[] $allFormTemplates
 * @property string   $captchasHtml
 * @property string[] $allCaptchaTypes
 */
class Forms extends Component
{
    const EVENT_REGISTER_CAPTCHAS = 'registerSproutFormsCaptchas';

    const EVENT_REGISTER_FORM_TEMPLATES = 'registerFormTemplatesEvent';

    /**
     * @var array
     */
    protected static $fieldVariables = [];

    /**
     * @var
     */
    public $activeEntries;

    /**
     * @var
     */
    public $activeCpEntry;

    /**
     * @var FormRecord
     */
    protected $formRecord;

    /**
     * Constructor
     *
     * @param object $formRecord
     */
    public function __construct($formRecord = null)
    {
        $this->formRecord = $formRecord;

        if ($this->formRecord === null) {
            $this->formRecord = new FormRecord();
        }

        parent::__construct($formRecord);
    }

    /**
     *
     * Allows a user to add variables to an object that can be parsed by fields
     *
     * @param array $variables
     *
     * @example
     * {% do craft.sproutForms.addFieldVariables({ entryTitle: entry.title }) %}
     * {{ craft.sproutForms.displayForm('contact') }}
     *
     */
    public static function addFieldVariables(array $variables)
    {
        static::$fieldVariables = array_merge(static::$fieldVariables, $variables);
    }

    /**
     * @return mixed
     */
    public static function getFieldVariables()
    {
        return static::$fieldVariables;
    }

    /**
     * @param FormElement $form
     * @param bool        $duplicate
     *
     * @return bool
     * @throws Throwable
     * @throws InvalidConfigException
     */
    public function saveForm(FormElement $form, bool $duplicate = false): bool
    {
        $isNew = !$form->id;
        $hasLayout = count($form->getFieldLayout()->getFields()) > 0;
        $oldForm = null;

        if (!$isNew) {
            // Add the oldHandle to our model so we can determine if we
            // need to rename the content table
            /** @var FormRecord $formRecord */
            $formRecord = FormRecord::findOne($form->id);
            $form->oldHandle = $formRecord->getOldHandle();
            $oldForm = $formRecord;

            if ($duplicate) {
                $form->name = $oldForm->name;
                $form->handle = $oldForm->handle;
                $form->oldHandle = null;
            }
        }

        $form->validate();

        if ($form->hasErrors()) {
            Craft::error($form->getErrors(), __METHOD__);

            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();

        try {
            if ($isNew) {
                $fieldLayout = $form->getFieldLayout();

                // Save the field layout
                Craft::$app->getFields()->saveLayout($fieldLayout);

                // Assign our new layout id info to our form model and record
                $form->fieldLayoutId = $fieldLayout->id;
                $form->setFieldLayout($fieldLayout);
            } else if ($oldForm !== null && $hasLayout) {
                // Delete our previous record
                Craft::$app->getFields()->deleteLayoutById($oldForm->fieldLayoutId);

                $fieldLayout = $form->getFieldLayout();

                // Save the field layout
                Craft::$app->getFields()->saveLayout($fieldLayout);

                // Assign our new layout id info to our form model
                $form->fieldLayoutId = $fieldLayout->id;
                $form->setFieldLayout($fieldLayout);
            } else {
                // We don't have a field layout right now
                $form->fieldLayoutId = null;
            }

            // Set the field context
            Craft::$app->content->fieldContext = $form->getFieldContext();
            Craft::$app->content->contentTable = $form->getContentTable();

            // Create the content table first since the form will need it
            $oldContentTable = $this->getContentTableName($form, true);
            $newContentTable = $this->getContentTableName($form);

            // Do we need to create/rename the content table?
            if (!Craft::$app->db->tableExists($newContentTable) && !$duplicate) {
                if ($oldContentTable && Craft::$app->db->tableExists($oldContentTable)) {
                    MigrationHelper::renameTable($oldContentTable, $newContentTable);
                } else {
                    $this->_createContentTable($newContentTable);
                }
            }

            // Save the Form
            if (!Craft::$app->elements->saveElement($form)) {
                Craft::error('Couldn’t save Element.', __METHOD__);

                return false;
            }

            // FormRecord saved on afterSave form element
            $transaction->commit();

            Craft::info('Form Saved.', __METHOD__);
        } catch (\Exception $e) {
            Craft::error('Unable to save form: '.$e->getMessage(), __METHOD__);
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Removes a form and related records from the database
     *
     * @param FormElement $form
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function deleteForm(FormElement $form): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            $originalContentTable = Craft::$app->content->contentTable;
            $contentTable = $this->getContentTableName($form);
            Craft::$app->content->contentTable = $contentTable;

            //Delete all entries
            $entries = (new Query())
                ->select(['elementId'])
                ->from([$contentTable])
                ->all();

            foreach ($entries as $entry) {
                Craft::$app->elements->deleteElementById($entry['elementId']);
            }

            // Delete form fields
            foreach ($form->getFields() as $field) {
                Craft::$app->getFields()->deleteField($field);
            }

            // Delete the Field Layout
            Craft::$app->getFields()->deleteLayoutById($form->fieldLayoutId);

            // Drop the content table
            Craft::$app->db->createCommand()
                ->dropTable($contentTable)
                ->execute();

            Craft::$app->content->contentTable = $originalContentTable;

            // Delete the Element and Form
            $success = Craft::$app->elements->deleteElementById($form->id);

            if (!$success) {
                $transaction->rollBack();
                Craft::error('Couldn’t delete Form', __METHOD__);

                return false;
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * Returns an array of models for forms found in the database
     *
     * @param int|null $siteId
     *
     * @return FormElement[]
     */
    public function getAllForms(int $siteId = null): array
    {
        $query = FormElement::find();
        $query->siteId($siteId);
        $query->orderBy(['name' => SORT_ASC]);

        // @todo - look into enabledForSite method
        // $query->enabledForSite(false);

        return $query->all();
    }

    /**
     * Returns a form model if one is found in the database by id
     *
     * @param int      $formId
     * @param int|null $siteId
     *
     * @return FormElement|ElementInterface|null
     */
    public function getFormById(int $formId, int $siteId = null)
    {
        $query = FormElement::find();
        $query->id($formId);
        $query->siteId($siteId);

        // @todo - look into enabledForSite method
        // $query->enabledForSite(false);

        return $query->one();
    }

    /**
     * Returns a form model if one is found in the database by handle
     *
     * @param string   $handle
     * @param int|null $siteId
     *
     * @return Form|ElementInterface|null
     */
    public function getFormByHandle(string $handle, int $siteId = null)
    {
        $query = FormElement::find();
        $query->handle($handle);
        $query->siteId($siteId);
        // @todo - look into enabledForSite method
        // $query->enabledForSite(false);

        return $query->one();
    }

    /**
     * Returns the content table name for a given form field
     *
     * @param FormElement $form
     * @param bool        $useOldHandle
     *
     * @return string|false
     */
    public function getContentTableName(FormElement $form, $useOldHandle = false)
    {
        if ($useOldHandle) {
            if (!$form->oldHandle) {
                return false;
            }

            $handle = $form->oldHandle;
        } else {
            $handle = $form->handle;
        }

        $name = '_'.StringHelper::toLowerCase($handle);

        return '{{%sproutformscontent'.$name.'}}';
    }

    /**
     * @param $formId
     *
     * @return string
     */
    public function getContentTable($formId): string
    {
        $form = $this->getFormById($formId);

        if ($form) {
            return sprintf('sproutformscontent_%s', strtolower(trim($form->handle)));
        }

        return 'content';
    }

    /**
     * Returns the value of a given field
     *
     * @param $field
     * @param $value
     *
     * @return null|FormRecord
     */
    public function getFieldValue($field, $value)
    {
        return FormRecord::findOne([
            $field => $value
        ]);
    }

    /**
     * Remove a field handle from title format
     *
     * @param int $fieldId
     *
     * @return string newTitleFormat
     */
    public function cleanTitleFormat($fieldId): string
    {
        /** @var Field $field */
        $field = Craft::$app->getFields()->getFieldById($fieldId);

        if ($field) {
            $context = explode(':', $field->context);
            $formId = $context[1];

            /** @var FormRecord $formRecord */
            $formRecord = FormRecord::findOne($formId);

            // Check if the field is in the titleformat
            if (strpos($formRecord->titleFormat, $field->handle) !== false) {
                // Let's remove the field from the titleFormat
                $newTitleFormat = preg_replace('/{'.$field->handle.'.*}/', '', $formRecord->titleFormat);
                $formRecord->titleFormat = $newTitleFormat;
                $formRecord->save(false);

                return $formRecord->titleFormat;
            }
        }

        return null;
    }

    /**
     * IF a field is deleted remove it from the rules
     *
     * @param $oldHandle
     * @param $form
     *
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function removeFieldRulesUsingField($oldHandle, $form)
    {
        $rules = SproutForms::$app->rules->getRulesByFormId($form->id);

        /** @var FieldRule $rule */
        foreach ($rules as $rule) {
            $conditions = $rule->conditions;
            if ($conditions) {
                foreach ($conditions as $key => $orConditions) {
                    foreach ($orConditions as $key2 => $condition) {
                        if (isset($condition[0]) && $condition[0] === $oldHandle) {
                            unset($conditions[$key][$key2]);
                        }
                    }

                    if (count($conditions[$key]) === 0) {
                        unset($conditions[$key]);
                    }
                }
            }
            $rule->conditions = $conditions;
            SproutForms::$app->rules->saveRule($rule);
        }
    }

    /**
     * IF a field is deleted remove it from the rules
     *
     * @param string      $oldHandle
     * @param string      $newHandle
     * @param FormElement $form
     *
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function updateFieldOnFieldRules($oldHandle, $newHandle, $form)
    {
        $rules = SproutForms::$app->rules->getRulesByFormId($form->id);

        /** @var FieldRule $rule */
        foreach ($rules as $rule) {
            $conditions = $rule->conditions;
            if ($conditions) {
                foreach ($conditions as $key => $orConditions) {
                    foreach ($orConditions as $key2 => $condition) {
                        if (isset($condition[0]) && $condition[0] === $oldHandle) {
                            $conditions[$key][$key2][0] = $newHandle;
                        }
                    }
                }
            }

            $rule->conditions = $conditions;
            SproutForms::$app->rules->saveRule($rule);
        }
    }

    /**
     * IF a field is updated, update the integrations
     *
     * @param string      $oldHandle
     * @param string      $newHandle
     * @param FormElement $form
     *
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function updateFieldOnIntegrations($oldHandle, $newHandle, $form)
    {
        $integrations = SproutForms::$app->integrations->getIntegrationsByFormId($form->id);

        /** @var Integration $integration */
        foreach ($integrations as $integration) {
            $integrationResult = (new Query())
                ->select(['id', 'settings'])
                ->from(['{{%sproutforms_integrations}}'])
                ->where(['id' => $integration->id])
                ->one();

            if ($integrationResult === null) {
                continue;
            }

            $settings = json_decode($integrationResult['settings'], true);

            $fieldMapping = $settings['fieldMapping'];
            foreach ($fieldMapping as $pos => $map) {
                if (isset($map['sourceFormField']) && $map['sourceFormField'] === $oldHandle) {
                    $fieldMapping[$pos]['sourceFormField'] = $newHandle;
                }
            }

            $integration->fieldMapping = $fieldMapping;
            SproutForms::$app->integrations->saveIntegration($integration);
        }
    }

    /**
     * Update a field handle with an new title format
     *
     * @param string $oldHandle
     * @param string $newHandle
     * @param string $titleFormat
     *
     * @return string newTitleFormat
     */
    public function updateTitleFormat($oldHandle, $newHandle, $titleFormat): string
    {
        return str_replace($oldHandle, $newHandle, $titleFormat);
    }

    /**
     * Create a secuencial string for the "name" and "handle" fields if they are already taken
     *
     * @param $field
     * @param $value
     *
     * @return null|string
     */
    public function getFieldAsNew($field, $value)
    {
        $newField = null;
        $i = 1;
        $band = true;
        do {
            $newField = $field == 'handle' ? $value.$i : $value.' '.$i;
            $form = $this->getFieldValue($field, $newField);
            if ($form === null) {
                $band = false;
            }

            $i++;
        } while ($band);

        return $newField;
    }

    /**
     * Removes forms and related records from the database given the ids
     *
     * @param $formElements
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function deleteForms($formElements): bool
    {
        foreach ($formElements as $key => $formElement) {
            $form = SproutForms::$app->forms->getFormById($formElement->id);

            if ($form) {
                SproutForms::$app->forms->deleteForm($form);
            } else {
                Craft::error("Can't delete the form with id: {$formElement->id}", __METHOD__);
            }
        }

        return true;
    }

    /**
     * Creates a form with a empty default tab
     *
     * @param string|null $name
     * @param string|null $handle
     *
     * @return FormElement|null
     * @throws \Exception
     * @throws Throwable
     */
    public function createNewForm($name = null, $handle = null)
    {
        $form = new FormElement();
        $name = $name ?? 'Form';
        $handle = $handle ?? 'form';

        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        $settings = $plugin->getSettings();

        $form->name = $this->getFieldAsNew('name', $name);
        $form->handle = $this->getFieldAsNew('handle', $handle);
        $form->titleFormat = "{dateCreated|date('D, d M Y H:i:s')}";
        $form->formTemplate = '';
        $form->saveData = $settings->enableSaveData ? $settings->enableSaveDataDefaultValue : false;

        // Set default tab

        /** @var Field $field */
        $field = null;
        $form = SproutForms::$app->fields->addDefaultTab($form, $field);

        if ($this->saveForm($form)) {
            // Let's delete the default field
            if ($field !== null && $field->id) {
                Craft::$app->getFields()->deleteFieldById($field->id);
            }

            return $form;
        }

        return null;
    }

    /**
     * Returns all available Form Templates Class Names
     *
     * @return array
     */
    public function getAllFormTemplateTypes(): array
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_FORM_TEMPLATES, $event);

        return $event->types;
    }

    /**
     * Returns all available Form Templates
     *
     * @return array[]
     */
    public function getAllFormTemplates(): array
    {
        $templateTypes = $this->getAllFormTemplateTypes();
        $templates = [];

        foreach ($templateTypes as $templateType) {
            $templates[$templateType] = new $templateType();
        }

        uasort($templates, static function($a, $b) {
            /**
             * @var $a FormTemplates
             * @var $b FormTemplates
             */
            return $a->getName() <=> $b->getName();
        });

        return $templates;
    }

    /**
     * @param FormElement $form
     *
     * @return array
     * @throws Exception
     */
    public function getFormTemplatePaths(FormElement $form): array
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        $settings = $plugin->getSettings();

        $templates = [];
        $templateFolder = '';
        $defaultVersion = new AccessibleTemplates();
        $defaultTemplate = $defaultVersion->getPath();

        if ($settings->formTemplateDefaultValue) {
            $templatePath = $this->getFormTemplateById($settings->formTemplateDefaultValue);
            if ($templatePath) {
                // custom path by template API
                $templateFolder = $templatePath->getPath();
            } else {
                // custom folder on site path
                $templateFolder = $this->getSitePath($settings->formTemplateDefaultValue);
            }
        }

        if ($form->formTemplate) {
            $formTemplatePath = $this->getFormTemplateById($form->formTemplate);
            if ($formTemplatePath) {
                // custom path by template API
                $templateFolder = $formTemplatePath->getPath();
            } else {
                // custom folder on site path
                $templateFolder = $this->getSitePath($form->formTemplate);
            }
        }

        // Set our defaults
        $templates['form'] = $defaultTemplate;
        $templates['tab'] = $defaultTemplate;
        $templates['field'] = $defaultTemplate;
        $templates['fields'] = $defaultTemplate;
        $templates['email'] = $defaultTemplate;

        // See if we should override our defaults
        if ($templateFolder) {

            $formTemplate = $templateFolder.DIRECTORY_SEPARATOR.'form';
            $tabTemplate = $templateFolder.DIRECTORY_SEPARATOR.'tab';
            $fieldTemplate = $templateFolder.DIRECTORY_SEPARATOR.'field';
            $fieldsFolder = $templateFolder.DIRECTORY_SEPARATOR.'fields';
            $emailTemplate = $templateFolder.DIRECTORY_SEPARATOR.'email';
            $basePath = $templateFolder.DIRECTORY_SEPARATOR;

            foreach (Craft::$app->getConfig()->getGeneral()->defaultTemplateExtensions as $extension) {

                if (file_exists($formTemplate.'.'.$extension)) {
                    $templates['form'] = $basePath;
                }

                if (file_exists($tabTemplate.'.'.$extension)) {
                    $templates['tab'] = $basePath;
                }

                if (file_exists($fieldTemplate.'.'.$extension)) {
                    $templates['field'] = $basePath;
                }

                if (file_exists($fieldsFolder)) {
                    $templates['fields'] = $basePath.'fields';
                }

                if (file_exists($emailTemplate.'.'.$extension)) {
                    $templates['email'] = $basePath;
                }
            }

            if (file_exists($fieldsFolder)) {
                $templates['fields'] = $basePath.'fields';
            }
        }

        return $templates;
    }

    /**
     * @param $templateId
     *
     * @return null|FormTemplates
     */
    public function getFormTemplateById($templateId)
    {
        $templates = SproutForms::$app->forms->getAllFormTemplates();

        foreach ($templates as $template) {
            if ($template->getTemplateId() == $templateId) {
                return $template;
            }
        }

        return null;
    }

    /**
     * Returns all available Captcha classes
     *
     * @return array[]
     */
    public function getAllCaptchaTypes(): array
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_CAPTCHAS, $event);

        return $event->types;
    }

    /**
     * @return array
     */
    public function getAllCaptchas(): array
    {
        $captchaTypes = $this->getAllCaptchaTypes();
        $captchas = [];

        foreach ($captchaTypes as $captchaType) {
            $captchas[$captchaType] = new $captchaType();
        }

        return $captchas;
    }

    /**
     * @return array
     */
    public function getAllEnabledCaptchas(): array
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        $sproutFormsSettings = $plugin->getSettings();
        $captchaTypes = $this->getAllCaptchas();
        $captchas = [];

        foreach ($captchaTypes as $captchaType) {
            $isEnabled = $sproutFormsSettings->captchaSettings[$captchaType->getCaptchaId()]['enabled'] ?? false;
            if ($isEnabled) {
                $captchas[get_class($captchaType)] = $captchaType;
            }
        }

        return $captchas;
    }

    /**
     * @param $context
     *
     * @return string|null
     */
    public function handleModifyFormHook($context)
    {
        /** @var Form $form */
        $form = $context['form'] ?? null;
        if ($form !== null && $form->enableCaptchas) {
            return $this->getCaptchasHtml($form);
        }

        return null;
    }

    /**
     * @param FormElement $form
     *
     * @return string
     */
    public function getCaptchasHtml(Form $form): string
    {
        $captchas = $this->getAllEnabledCaptchas();
        $captchaHtml = '';

        foreach ($captchas as $captcha) {
            $captcha->form = $form;
            $captchaHtml .= $captcha->getCaptchaHtml();
        }

        return $captchaHtml;
    }

    /**
     * Checks if the current plugin edition allows a user to create a Form
     *
     * @return bool
     */
    public function canCreateForm(): bool
    {
        $isPro = SproutBase::$app->settings->isEdition('sprout-forms', SproutForms::EDITION_PRO);

        if (!$isPro) {
            $forms = $this->getAllForms();

            if (count($forms) >= 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param FormElement $form
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getTabsForFieldLayout(Form $form): array
    {
        $tabs = [];

        foreach ($form->getFieldLayout()->getTabs() as $index => $tab) {
            // Do any of the fields on this tab have errors?
            $hasErrors = false;

            if ($form->hasErrors()) {
                foreach ($tab->getFields() as $field) {
                    /** @var Field $field */
                    if ($hasErrors = $form->hasErrors($field->handle.'.*')) {
                        break;
                    }
                }
            }

            $tabs[$tab->id] = [
                'label' => Craft::t('sprout-forms', $tab->name),
                'url' => '#sproutforms-tab-'.$tab->id,
                'class' => $hasErrors ? 'error' : null
            ];
        }

        return $tabs;
    }

    /**
     * Creates the content table for a Form.
     *
     * @param $name
     *
     * @throws Throwable
     */
    private function _createContentTable($name)
    {
        $migration = new CreateFormContentTable([
            'tableName' => $name
        ]);

        ob_start();
        $migration->up();
        ob_end_clean();
    }

    /**
     * @param $path
     *
     * @return string
     * @throws Exception
     */
    private function getSitePath($path): string
    {
        return Craft::$app->path->getSiteTemplatesPath().DIRECTORY_SEPARATOR.$path;
    }
}
