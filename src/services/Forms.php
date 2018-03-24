<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\contracts\BaseFormTemplates;
use barrelstrength\sproutforms\integrations\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\records\Form as FormRecord;
use barrelstrength\sproutforms\migrations\CreateFormContentTable;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use yii\base\Component;
use craft\helpers\StringHelper;
use craft\helpers\MigrationHelper;
use craft\helpers\ArrayHelper;
use craft\mail\Message;
use yii\base\Exception;


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
    public function saveForm(FormElement $form)
    {
        $isNewForm = true;

        if ($form->id) {
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

        $form->titleFormat = ($form->titleFormat ? $form->titleFormat : "{dateCreated|date('D, d M Y H:i:s')}");

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
            } else {
                // If we have a layout use it, otherwise
                // since this is an existing form, grab the oldForm layout
                if ($hasLayout) {
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
            $transaction->rollback();

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
    public function deleteForm(FormElement $form)
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
                $transaction->rollback();
                SproutForms::error('Couldn’t delete Form on deleteForm service.');

                return false;
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();

            throw $e;
        }

        return true;
    }

    /**
     * Returns an array of models for forms found in the database
     *
     * @param int|null $siteId
     *
     * @return array|FormElement|null
     */
    public function getAllForms(int $siteId = null)
    {
        $query = FormElement::find();
        $query->siteId($siteId);
        $query->orderBy(['name' => SORT_ASC]);
        // @todo - research next function
        #$query->enabledForSite(false);

        return $query->all();
    }

    /**
     * Returns a form model if one is found in the database by id
     *
     * @param int $formId
     * @param int $siteId
     *
     * @return null|FormElement
     */
    public function getFormById(int $formId, int $siteId = null)
    {
        $query = FormElement::find();
        $query->id($formId);
        $query->siteId($siteId);
        // @todo - research next function
        #$query->enabledForSite(false);

        return $query->one();
    }

    /**
     * Returns a form model if one is found in the database by handle
     *
     * @param string   $handle
     * @param int|null $siteId
     *
     * @return array|\craft\base\ElementInterface|null
     */
    public function getFormByHandle(string $handle, int $siteId = null)
    {
        $query = FormElement::find();
        $query->handle($handle);
        $query->siteId($siteId);
        // @todo - research next function
        #$query->enabledForSite(false);

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
    public function getContentTable($formId)
    {
        $form = $this->getFormById($formId);

        if ($form) {
            return sprintf('sproutformscontent_%s', trim(strtolower($form->handle)));
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
    public function cleanTitleFormat($fieldId)
    {
        $field = Craft::$app->getFields()->getFieldById($fieldId);

        if ($field) {
            $context = explode(':', $field->context);
            $formId = $context[1];
            $formRecord = FormRecord::find($formId)->one();

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
    public function updateTitleFormat($oldHandle, $newHandle, $titleFormat)
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
     * Sprout Forms Send Notification service.
     *
     * @param FormElement  $form
     * @param EntryElement $entry
     * @param null         $post
     *
     * @return bool
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function sendNotification(FormElement $form, EntryElement $entry, $post = null)
    {
        // Get our recipients
        $recipients = ArrayHelper::toArray($form->notificationRecipients);
        $recipients = array_unique($recipients);
        $response = false;
        $view = Craft::$app->getView();

        if (count($recipients)) {
            $message = new Message();
            $tabs = $form->getFieldLayout()->getTabs();
            $templatePaths = SproutForms::$app->fields->getSproutFormsTemplates($form);
            $emailTemplate = $templatePaths['email'];

            // Set our Sprout Forms Email Template path
            $view->setTemplatesPath($emailTemplate);

            $htmlBodyTemplate = $view->renderTemplate(
                'email', [
                    'formName' => $form->name,
                    'tabs' => $tabs,
                    'element' => $entry
                ]
            );

            $message->setHtmlBody($htmlBodyTemplate);

            $view->setTemplatesPath(Craft::$app->path->getCpTemplatesPath());

            if ($post === null) {
                $post = $_POST;
            }

            $post = (object)$post;

            $message->setFrom($form->notificationSenderEmail);
            // @todo - how set from name on craft3?
            #$message->setFrom  = $form->notificationSenderName;
            $message->setSubject($form->notificationSubject);

            $mailer = Craft::$app->getMailer();

            try {
                $subject = null;
                // Has a custom subject been set for this form?
                if ($form->notificationSubject) {
                    $subject = $view->renderObjectTemplate($form->notificationSubject, $post, true);
                }

                $message->setSubject(SproutForms::$app->encodeSubjectLine($subject));

                // custom replyTo has been set for this form
                if ($form->notificationReplyToEmail) {
                    $repleyTo = $view->renderObjectTemplate($form->notificationReplyToEmail, $post, true);

                    $message->setReplyTo($repleyTo);

                    if (!filter_var($repleyTo, FILTER_VALIDATE_EMAIL)) {
                        $message->setReplyTo(null);
                    }
                }

                foreach ($recipients as $emailAddress) {
                    $toEmail = $view->renderObjectTemplate($emailAddress, $post, true);
                    $message->setTo($toEmail);

                    if (filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                        // @todo - add to the event
                        /*$options =
                            array(
                                'sproutFormsEntry'      => $entry,
                                'enableFileAttachments' => $form->enableFileAttachments,
                            );*/

                        $mailer->send($message);
                    }
                }

                $response = true;
            } catch (\Exception $e) {
                $response = false;
                SproutForms::error($e->getMessage());
            }
        }

        return $response;
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
    public function deleteForms($formElements)
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
        $settings = Craft::$app->getPlugins()->getPlugin('sprout-forms')->getSettings();

        if ($settings->enableSaveData) {
            if ($settings->enableSaveDataPerFormBasis) {
                $form->saveData = $settings->saveDataByDefault;
            }
        }

        $form->name = $this->getFieldAsNew('name', $name);
        $form->handle = $this->getFieldAsNew('handle', $handle);
        $accessible = new AccessibleTemplates();
        $form->templateOverridesFolder = $accessible->getTemplateId();
        // Set default tab
        $field = null;
        $form = SproutForms::$app->fields->addDefaultTab($form, $field);

        if ($this->saveForm($form)) {
            // Let's delete the default field
            if (isset($field) && $field->id) {
                Craft::$app->getFields()->deleteFieldById($field->id);
            }

            return $form;
        }

        return null;
    }

    /**
     * Returns all available Global Form Templates
     *
     * @return string[]
     */
    public function getAllGlobalTemplateTypes()
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_FORM_TEMPLATES, $event);

        return $event->types;
    }

    /**
     * Returns all available Global Form Templates
     *
     * @return string[]
     */
    public function getAllGlobalTemplates()
    {
        $templateTypes = $this->getAllGlobalTemplateTypes();
        $templates = [];

        foreach ($templateTypes as $templateType) {
            $templates[$templateType] = new $templateType();
        }

        uasort($templates, function($a, $b) {
            /**
             * @var $a BaseFormTemplates
             * @var $b BaseFormTemplates
             */
            return $a->getName() <=> $b->getName();
        });

        return $templates;
    }

    /**
     * Returns all available Captcha classes
     *
     * @return string[]
     */
    public function getAllCaptchaTypes()
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
    public function getAllCaptchas()
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
    public function getAllEnabledCaptchas()
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
     * @param FormElement|null $form
     *
     * @return array
     * @throws \yii\base\Exception
     */
    public function getSproutFormsTemplates(FormElement $form = null)
    {
        $templates = [];
        $settings = Craft::$app->plugins->getPlugin('sprout-forms')->getSettings();
        $templateFolderOverride = '';
        $defaultVersion = new AccessibleTemplates();
        $defaultTemplate = $defaultVersion->getPath();

        if ($settings->templateFolderOverride) {
            $templatePath = $this->getTemplateById($settings->templateFolderOverride);
            if ($templatePath) {
                // custom path by template API
                $templateFolderOverride = $templatePath->getPath();
            } else {
                // custom folder on site path
                $templateFolderOverride = $this->getSitePath($settings->templateFolderOverride);
            }
        }

        if ($form->templateOverridesFolder) {
            $formTemplatePath = $this->getTemplateById($form->templateOverridesFolder);
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
                    $templates['fields'] = $basePath . 'fields';
                }

                if (file_exists($emailTemplate.'.'.$extension)) {
                    $templates['email'] = $basePath;
                }
            }

            if (file_exists($fieldsFolder)) {
                $templates['fields'] = $basePath . 'fields';
            }
        }

        return $templates;
    }

    /**
     * @param $path
     *
     * @return string
     * @throws \yii\base\Exception
     */
    private function getSitePath($path)
    {
        return Craft::$app->path->getSiteTemplatesPath().DIRECTORY_SEPARATOR.$path;
    }

    /**
     * @param $templateId
     *
     * @return null|BaseFormTemplates
     */
    public function getTemplateById($templateId)
    {
        $templates = SproutForms::$app->forms->getAllGlobalTemplates();

        foreach ($templates as $template) {
            if ($template->getTemplateId() == $templateId) {
                return $template;
            }
        }

        return null;
    }
}
