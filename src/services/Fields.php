<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\integrations\sproutforms\fields\FileUpload;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Categories;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Checkboxes;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Dropdown;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Email;
use barrelstrength\sproutforms\integrations\sproutforms\fields\EmailDropdown;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Hidden;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Invisible;
use barrelstrength\sproutforms\integrations\sproutforms\fields\MultiSelect;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Name;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Number;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Paragraph;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Phone;
use barrelstrength\sproutforms\integrations\sproutforms\fields\MultipleChoice;
use barrelstrength\sproutforms\integrations\sproutforms\fields\RegularExpression;
use barrelstrength\sproutforms\integrations\sproutforms\fields\PrivateNotes;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Entries;
use barrelstrength\sproutforms\integrations\sproutforms\fields\CustomHtml;
use barrelstrength\sproutforms\integrations\sproutforms\fields\SectionHeading;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Tags;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Url;
use Craft;
use yii\base\Component;
use craft\base\Field;
use craft\records\Field as FieldRecord;
use barrelstrength\sproutforms\integrations\sproutforms\fields\SingleLine;
use craft\records\FieldLayoutField as FieldLayoutFieldRecord;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;

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
     * @param $fieldIds
     *
     * @return bool
     * @throws Exception
     * @throws \Exception
     */
    public function reorderFields($fieldIds)
    {
        $transaction = Craft::$app->db->getTransaction() === null ? Craft::$app->db->beginTransaction() : null;

        try {
            foreach ($fieldIds as $fieldOrder => $fieldId) {
                $fieldLayoutFieldRecord = $this->_getFieldLayoutFieldRecordByFieldId($fieldId);
                $fieldLayoutFieldRecord->sortOrder = $fieldOrder + 1;
                $fieldLayoutFieldRecord->save();
            }

            if ($transaction !== null) {
                $transaction->commit();
            }
        } catch (\Exception $e) {

            if ($transaction !== null) {
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
        if ($fieldId) {
            $record = FieldLayoutFieldRecord::find('fieldId=:fieldId', [':fieldId' => $fieldId]);

            if (!$record) {
                throw new Exception(Craft::t('sprout-forms', 'No field exists with the ID “{id}”', ['id' => $fieldId]));
            }
        } else {
            $record = new FieldLayoutFieldRecord();
        }

        return $record;
    }

    /**
     * @return array|SproutFormsBaseField[]
     */
    public function getRegisteredFields()
    {
        if (is_null($this->registeredFields)) {
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
            foreach ($fields as $instance) {
                $this->registeredFields[get_class($instance)] = $instance;
            }
        }

        return $this->registeredFields;
    }

    /**
     * @return array
     */
    public function getRegisteredFieldsByGroup()
    {
        $standardLabel = Craft::t('sprout-forms', 'Standard Fields');
        $advancedLabel = Craft::t('sprout-forms', 'Special Fields');

        // Standard
        $groupedFields[$standardLabel][] = SingleLine::className();
        $groupedFields[$standardLabel][] = Paragraph::className();
        $groupedFields[$standardLabel][] = MultipleChoice::className();
        $groupedFields[$standardLabel][] = Dropdown::className();
        $groupedFields[$standardLabel][] = Checkboxes::className();
        $groupedFields[$standardLabel][] = Number::className();
        $groupedFields[$standardLabel][] = FileUpload::className();
        $groupedFields[$standardLabel][] = SectionHeading::className();

        // Advanced
        $groupedFields[$advancedLabel][] = Name::class;
        $groupedFields[$advancedLabel][] = Email::className();
        $groupedFields[$advancedLabel][] = EmailDropdown::className();
        $groupedFields[$advancedLabel][] = Phone::className();
        $groupedFields[$advancedLabel][] = Url::className();
        $groupedFields[$advancedLabel][] = CustomHtml::className();
        $groupedFields[$advancedLabel][] = PrivateNotes::className();
        $groupedFields[$advancedLabel][] = Categories::className();
        $groupedFields[$advancedLabel][] = Entries::className();
        $groupedFields[$advancedLabel][] = Hidden::className();
        $groupedFields[$advancedLabel][] = Invisible::className();
        $groupedFields[$advancedLabel][] = MultiSelect::className();
        $groupedFields[$advancedLabel][] = RegularExpression::className();
        $groupedFields[$advancedLabel][] = Tags::className();

        return $groupedFields;
    }

    /**
     * @param $type
     *
     * @return SproutFormsBaseField|mixed
     */
    public function getRegisteredField($type)
    {
        $fields = $this->getRegisteredFields();

        foreach ($fields as $field) {
            if ($field->getType() == $type) {
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
        $fields = $this->getRegisteredFields();
        $standardFields = [];

        if (count($fields)) {
            // Loop through registered fields and add them to the standard group
            foreach ($fields as $class => $field) {
                $standardFields[$class] = $field::displayName();
            }

            // Sort fields alphabetically by name
            asort($standardFields);

            // Add the group label to the beginning of the standard group
            $standardFields = $this->prependKeyValue($standardFields, 'standardFieldGroup', ['optgroup' => Craft::t('sprout-forms', 'Standard Fields')]);
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
     * @param $field
     * @param $value
     *
     * @return null|string|string[]
     */
    public function getFieldAsNew($field, $value)
    {
        $newField = null;
        $i = 1;
        $band = true;

        do {
            if ($field == 'handle') {
                // Append a number to our handle to ensure it is unique
                $newField = $value.$i;

                $form = $this->getFieldValue($field, $newField);

                if (is_null($form)) {
                    $band = false;
                }
            } else {
                // Add spaces before any capital letters in our name
                $newField = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value);
                $band = false;
            }

            $i++;
        } while ($band);

        return $newField;
    }

    /**
     * This service allows create a default tab given a form
     *
     * @param      $form
     * @param null $field
     *
     * @return null
     * @throws \Throwable
     */
    public function addDefaultTab($form, &$field = null)
    {
        if ($form) {
            if (is_null($field)) {
                $fieldsService = Craft::$app->getFields();
                $handle = $this->getFieldAsNew('handle', 'defaultField');

                $field = $fieldsService->createField([
                    'type' => SingleLine::class,
                    'name' => Craft::t('sprout-forms', 'Default Field'),
                    'handle' => $handle,
                    'instructions' => '',
                    'translationMethod' => Field::TRANSLATION_METHOD_NONE,
                ]);
                // Save our field
                Craft::$app->content->fieldContext = $form->getFieldContext();
                Craft::$app->fields->saveField($field);
            }

            // Create a tab
            $tabName = $this->getDefaultTabName();
            $requiredFields = [];
            $postedFieldLayout = [];

            // Add our new field
            if (isset($field) && $field->id != null) {
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
     * @param $form
     * @param $postFieldLayout
     *
     * @return \craft\models\FieldLayout|null
     * @throws \Throwable
     */
    public function getDuplicateLayout($form, $postFieldLayout)
    {
        if ($form && $postFieldLayout) {
            $postedFieldLayout = [];
            $requiredFields = [];
            $tabs = $postFieldLayout->getTabs();

            foreach ($tabs as $tab) {
                $fields = [];
                $fieldLayoutFields = $tab->getFields();

                foreach ($fieldLayoutFields as $fieldLayoutField) {
                    $originalField = $fieldLayoutField->getField();

                    $field = new FieldModel();
                    $field->name = $originalField->name;
                    $field->handle = $originalField->handle;
                    $field->instructions = $originalField->instructions;
                    $field->required = $fieldLayoutField->required;
                    $field->translatable = $originalField->translatable;
                    $field->type = $originalField->type;

                    if (isset($originalField->settings)) {
                        $field->settings = $originalField->settings;
                    }

                    Craft::$app->content->fieldContext = $form->getFieldContext();
                    Craft::$app->content->contentTable = $form->getContentTable();
                    // Save duplicate field
                    Craft::$app->fields->saveField($field);
                    array_push($fields, $field);

                    if ($field->required) {
                        array_push($requiredFields, $field->id);
                    }
                }

                foreach ($fields as $field) {
                    // Add our new field
                    if (isset($field) && $field->id != null) {
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

        if (isset($field) && isset($form)) {

            $fieldLayoutFields = FieldLayoutFieldRecord::findAll([
                'tabId' => $tabId, 'layoutId' => $form->fieldLayoutId
            ]);

            $sortOrder = count($fieldLayoutFields) + 1;

            $fieldRecord = new FieldLayoutFieldRecord();
            $fieldRecord->layoutId = $form->fieldLayoutId;
            $fieldRecord->tabId = $tabId;
            $fieldRecord->fieldId = $field->id;
            $fieldRecord->required = 0;
            $fieldRecord->sortOrder = $sortOrder;

            $response = $fieldRecord->save(false);
        }

        return $response;
    }

    /**
     * This service allows update a field to a current FieldLayoutFieldRecord
     *
     * @param FieldInterface $field
     * @param FormElement    $form
     * @param int            $tabId
     *
     * @return boolean
     */
    public function updateFieldToLayout($field, $form, $tabId): bool
    {
        $response = false;

        if (isset($field) && isset($form)) {
            $fieldRecord = FieldLayoutFieldRecord::findOne([
                'fieldId' => $field->id,
                'layoutId' => $form->fieldLayoutId
            ]);

            if ($fieldRecord) {
                $fieldRecord->tabId = $tabId;

                $response = $fieldRecord->save(false);
            } else {
                SproutForms::error('Unable to find the FieldLayoutFieldRecord');
            }
        }

        return $response;
    }

    public function getDefaultTabName()
    {
        return Craft::t('sprout-forms', 'Section 1');
    }

    /**
     * Loads the sprout modal field via ajax.
     *
     * @param      $form
     * @param null $field
     * @param null $tabId
     *
     * @return array
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getModalFieldTemplate($form, $field = null, $tabId = null)
    {
        $fieldsService = Craft::$app->getFields();
        $request = Craft::$app->getRequest();

        $data = [];
        $data['tabId'] = null;
        $data['field'] = $fieldsService->createField(SingleLine::class);

        if ($field) {
            $data['field'] = $field;
            $tabIdByPost = $request->getBodyParam('tabId');

            if (isset($tabIdByPost)) {
                $data['tabId'] = $tabIdByPost;
            } else if ($tabId != null) //edit field
            {
                $data['tabId'] = $tabId;
            }

            if ($field->id != null) {
                $data['fieldId'] = $field->id;
            }
        }

        $data['sections'] = $form->getFieldLayout()->getTabs();
        $data['formId'] = $form->id;
        $view = Craft::$app->getView();

        $html = $view->renderTemplate('sprout-forms/forms/_editFieldModal', $data);
        $js = $view->getBodyHtml();
        $css = $view->getHeadHtml();

        return [
            'html' => $html,
            'js' => $js,
            'css' => $css
        ];
    }

    /**
     * @param $type
     * @param $form
     *
     * @return \craft\base\FieldInterface
     * @throws \Throwable
     */
    public function createDefaultField($type, $form)
    {
        $intanceField = new $type;
        $fieldsService = Craft::$app->getFields();
        // get the field name and remove spaces
        $fieldName = preg_replace('/\s+/', '', $intanceField->displayName());
        $handleName = lcfirst($fieldName);

        $name = $this->getFieldAsNew('name', $fieldName);
        $handle = $this->getFieldAsNew('handle', $handleName);

        $field = $fieldsService->createField([
            'type' => $type,
            'name' => $name,
            'handle' => $handle,
            'instructions' => '',
            // @todo - add locales
            'translationMethod' => Field::TRANSLATION_METHOD_NONE,
        ]);

        // Set our field context
        Craft::$app->content->fieldContext = $form->getFieldContext();
        Craft::$app->content->contentTable = $form->getContentTable();

        $fieldsService->saveField($field);

        return $field;
    }

    /**
     * @param             $name
     * @param             $sortOrder
     * @param FormElement $form
     *
     * @return FieldLayoutTabRecord
     */
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
     * Renames tab of form layout
     *
     * @param string      $name
     * @param string      $oldName
     * @param FormElement $form
     *
     * @return boolean
     */
    public function renameTab($name, $oldName, FormElement $form)
    {
        $fieldLayout = $form->getFieldLayout();
        $tabs = $fieldLayout->getTabs();
        $response = false;

        foreach ($tabs as $tab) {
            if ($tab->name == $oldName) {
                $tabRecord = FieldLayoutTabRecord::findOne($tab->id);

                if ($tabRecord) {
                    $tabRecord->name = $name;
                    $response = $tabRecord->save(false);
                }
            }
        }

        return $response;
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
        $haystack = array_reverse($haystack, true);
        $haystack[$key] = $value;

        return array_reverse($haystack, true);
    }
}
