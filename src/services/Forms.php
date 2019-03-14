<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\FormTemplates;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\records\Form as FormRecord;
use barrelstrength\sproutforms\migrations\CreateFormContentTable;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\events\RegisterComponentTypesEvent;
use yii\base\Component;
use craft\helpers\StringHelper;
use craft\helpers\MigrationHelper;
use yii\base\Exception;


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
     * @var array
     */
    protected static $fieldVariables = [];

    /**
     *
     * Allows a user to add variables to an object that can be parsed by fields
     *
     * @example
     * {% do craft.sproutForms.addFieldVariables({ entryTitle: entry.title }) %}
     * {{ craft.sproutForms.displayForm('contact') }}
     *
     * @param array $variables
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
     * Returns a criteria model for SproutForms_Form elements
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function getCriteria(array $attributes = [])
    {
        return Craft::$app->elements->getCriteria(FormElement::class, $attributes);
    }

    /**
     * @param FormElement $form
     *
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function saveForm(FormElement $form): bool
    {
        $isNewForm = true;
        $hasLayout = null;
        $oldForm = null;

        if ($form->id) {
            /** @var FormRecord $formRecord */
            $formRecord = FormRecord::findOne($form->id);

            if (!$formRecord) {
                throw new Exception(Craft::t('sprout-forms', 'No form exists with the ID “{id}”', ['id' => $form->id]));
            }

            $oldForm = $formRecord;
            $isNewForm = false;

            $hasLayout = count($form->getFieldLayout()->getFields()) > 0;

            // Add the oldHandle to our model so we can determine if we
            // need to rename the content table
            $form->oldHandle = $formRecord->getOldHandle();

            if ($form->saveAsNew) {
                $form->name = $oldForm->name;
                $form->handle = $oldForm->handle;
                $form->oldHandle = null;
            }
        }

        $form->titleFormat = ($form->titleFormat ?: "{dateCreated|date('D, d M Y H:i:s')}");

        $form->validate();

        if ($form->hasErrors()) {
            SproutForms::error('Form has errors');

            return false;
        }

        $transaction = Craft::$app->db->beginTransaction();

        try {
            // Set the field context
            Craft::$app->content->fieldContext = $form->getFieldContext();
            Craft::$app->content->contentTable = $form->getContentTable();

            if ($isNewForm) {
                $fieldLayout = $form->getFieldLayout();

                // Save the field layout
                Craft::$app->getFields()->saveLayout($fieldLayout);

                // Assign our new layout id info to our form model and records
                $form->fieldLayoutId = $fieldLayout->id;
                $form->setFieldLayout($fieldLayout);
                $form->fieldLayoutId = $fieldLayout->id;
            } else if ($hasLayout) {
                // Delete our previous record
                Craft::$app->getFields()->deleteLayoutById($oldForm->fieldLayoutId);

                $fieldLayout = $form->getFieldLayout();

                // Save the field layout
                Craft::$app->getFields()->saveLayout($fieldLayout);

                // Assign our new layout id info to our
                // form model and records
                $form->fieldLayoutId = $fieldLayout->id;
                $form->setFieldLayout($fieldLayout);
                $form->fieldLayoutId = $fieldLayout->id;
            } else {
                // We don't have a field layout right now
                $form->fieldLayoutId = null;
            }

            // Create the content table first since the form will need it
            $oldContentTable = $this->getContentTableName($form, true);
            $newContentTable = $this->getContentTableName($form);

            // Do we need to create/rename the content table?
            if (!Craft::$app->db->tableExists($newContentTable) && !$form->saveAsNew) {
                if ($oldContentTable && Craft::$app->db->tableExists($oldContentTable)) {
                    MigrationHelper::renameTable($oldContentTable, $newContentTable);
                } else {
                    $this->_createContentTable($newContentTable);
                }
            }

            $success = Craft::$app->elements->saveElement($form, false);

            if (!$success) {
                SproutForms::error('Couldn’t save Element on saveForm service.');

                return false;
            }

            // FormRecord saved on afterSave form element
            $transaction->commit();

            SproutForms::info('Form Saved!');
        } catch (\Exception $e) {
            SproutForms::error('Failed to save form: '.$e->getMessage());
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
     * @throws \Throwable
     */
    public function deleteForm(FormElement $form): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            $originalContentTable = Craft::$app->content->contentTable;
            $contentTable = $this->getContentTableName($form);
            Craft::$app->content->contentTable = $contentTable;

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
                SproutForms::error('Couldn’t delete Form on deleteForm service.');

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
     * Creates the content table for a Form.
     *
     * @param $name
     *
     * @throws \Throwable
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
                $newTitleFormat = preg_replace('/\{'.$field->handle.'.*\}/', '', $formRecord->titleFormat);
                $formRecord->titleFormat = $newTitleFormat;
                $formRecord->save(false);

                return $formRecord->titleFormat;
            }
        }

        return null;
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
     * @throws \Throwable
     */
    public function deleteForms($formElements): bool
    {
        foreach ($formElements as $key => $formElement) {
            $form = SproutForms::$app->forms->getFormById($formElement->id);

            if ($form) {
                SproutForms::$app->forms->deleteForm($form);
            } else {
                SproutForms::error("Can't delete the form with id: {$formElement->id}");
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
     * @throws \Throwable
     */
    public function createNewForm($name = null, $handle = null)
    {
        $form = new FormElement();
        $name = $name ?? 'Form';
        $handle = $handle ?? 'form';

        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        $settings = $plugin->getSettings();

        if ($settings->enableSaveData && $settings->enableSaveDataPerFormBasis) {
            $form->saveData = $settings->saveDataByDefault;
        }

        $form->name = $this->getFieldAsNew('name', $name);
        $form->handle = $this->getFieldAsNew('handle', $handle);
        $accessible = new AccessibleTemplates();
        $form->templateOverridesFolder = $settings->templateFolderOverride ?? $accessible->getTemplateId();
        if ($settings->enablePerFormTemplateFolderOverride && $settings->templateFolderOverride) {
            $form->templateOverridesFolder = $settings->templateFolderOverride;
        }
        // Set default tab
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

        uasort($templates, function($a, $b) {
            /**
             * @var $a FormTemplates
             * @var $b FormTemplates
             */
            return $a->getName() <=> $b->getName();
        });

        return $templates;
    }

    /**
     * @param FormElement|null $form
     *
     * @return array
     * @throws Exception
     */
    public function getFormTemplatePaths(FormElement $form = null): array
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        $settings = $plugin->getSettings();

        $templates = [];
        $templateFolderOverride = '';
        $defaultVersion = new AccessibleTemplates();
        $defaultTemplate = $defaultVersion->getPath();

        if ($settings->templateFolderOverride) {
            $templatePath = $this->getFormTemplateById($settings->templateFolderOverride);
            if ($templatePath) {
                // custom path by template API
                $templateFolderOverride = $templatePath->getPath();
            } else {
                // custom folder on site path
                $templateFolderOverride = $this->getSitePath($settings->templateFolderOverride);
            }
        }

        if ($form->templateOverridesFolder) {
            $formTemplatePath = $this->getFormTemplateById($form->templateOverridesFolder);
            if ($formTemplatePath) {
                // custom path by template API
                $templateFolderOverride = $formTemplatePath->getPath();
            } else {
                // custom folder on site path
                $templateFolderOverride = $this->getSitePath($form->templateOverridesFolder);
            }
        }

        // Set our defaults
        $templates['form'] = $defaultTemplate;
        $templates['tab'] = $defaultTemplate;
        $templates['field'] = $defaultTemplate;
        $templates['fields'] = $defaultTemplate;
        $templates['email'] = $defaultTemplate;

        // See if we should override our defaults
        if ($templateFolderOverride) {

            $formTemplate = $templateFolderOverride.DIRECTORY_SEPARATOR.'form';
            $tabTemplate = $templateFolderOverride.DIRECTORY_SEPARATOR.'tab';
            $fieldTemplate = $templateFolderOverride.DIRECTORY_SEPARATOR.'field';
            $fieldsFolder = $templateFolderOverride.DIRECTORY_SEPARATOR.'fields';
            $emailTemplate = $templateFolderOverride.DIRECTORY_SEPARATOR.'email';
            $basePath = $templateFolderOverride.DIRECTORY_SEPARATOR;

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
     * @param $path
     *
     * @return string
     * @throws \yii\base\Exception
     */
    private function getSitePath($path): string
    {
        return Craft::$app->path->getSiteTemplatesPath().DIRECTORY_SEPARATOR.$path;
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
        $sproutFormsSettings = Craft::$app->getPlugins()->getPlugin('sprout-forms')->getSettings();
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
     * @return string
     */
    public function getCaptchasHtml(): string
    {
        $captchas = $this->getAllEnabledCaptchas();
        $captchaHtml = '';

        foreach ($captchas as $captcha) {
            $captchaHtml .= $captcha->getCaptchaHtml();
        }

        return $captchaHtml;
    }
}
