<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\events\RegisterFieldsEvent;
use barrelstrength\sproutforms\fields\formfields\Address;
use barrelstrength\sproutforms\fields\formfields\Categories;
use barrelstrength\sproutforms\fields\formfields\Checkboxes;
use barrelstrength\sproutforms\fields\formfields\CustomHtml;
use barrelstrength\sproutforms\fields\formfields\Date;
use barrelstrength\sproutforms\fields\formfields\Dropdown;
use barrelstrength\sproutforms\fields\formfields\Email;
use barrelstrength\sproutforms\fields\formfields\EmailDropdown;
use barrelstrength\sproutforms\fields\formfields\Entries;
use barrelstrength\sproutforms\fields\formfields\FileUpload;
use barrelstrength\sproutforms\fields\formfields\Hidden;
use barrelstrength\sproutforms\fields\formfields\Invisible;
use barrelstrength\sproutforms\fields\formfields\MultipleChoice;
use barrelstrength\sproutforms\fields\formfields\MultiSelect;
use barrelstrength\sproutforms\fields\formfields\Name;
use barrelstrength\sproutforms\fields\formfields\Number;
use barrelstrength\sproutforms\fields\formfields\OptIn;
use barrelstrength\sproutforms\fields\formfields\Paragraph;
use barrelstrength\sproutforms\fields\formfields\Phone;
use barrelstrength\sproutforms\fields\formfields\PrivateNotes;
use barrelstrength\sproutforms\fields\formfields\RegularExpression;
use barrelstrength\sproutforms\fields\formfields\SectionHeading;
use barrelstrength\sproutforms\fields\formfields\SingleLine;
use barrelstrength\sproutforms\fields\formfields\Tags;
use barrelstrength\sproutforms\fields\formfields\Url;
use barrelstrength\sproutforms\fields\formfields\Users;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\Field;
use craft\base\FieldInterface;
use craft\db\Query;
use craft\db\Table;
use craft\errors\ElementNotFoundException;
use craft\fieldlayoutelements\CustomField;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\records\Field as FieldRecord;
use craft\records\FieldLayoutField as FieldLayoutFieldRecord;
use craft\records\FieldLayoutTab as FieldLayoutTabRecord;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;

/**
 * @property mixed $defaultTabName
 * @property array $registeredFieldsByGroup
 */
class Fields extends Component
{
    /**
     * @event RegisterFieldsEvent The event that is triggered when registering the fields available.
     */
    const EVENT_REGISTER_FIELDS = 'registerFieldsEvent';

    /**
     * @var FormField[]
     */
    protected $registeredFields;

    /**
     * @param $fieldIds
     *
     * @return bool
     * @throws Exception
     */
    public function reorderFields($fieldIds): bool
    {
        $transaction = Craft::$app->db->getTransaction() === null ? Craft::$app->db->beginTransaction() : null;

        try {
            foreach ($fieldIds as $fieldOrder => $fieldId) {
                $fieldLayoutFieldRecord = $this->getFieldLayoutFieldRecordByFieldId($fieldId);
                $fieldLayoutFieldRecord->sortOrder = $fieldOrder + 1;
                $fieldLayoutFieldRecord->save();
            }

            if ($transaction !== null) {
                $transaction->commit();
            }
        } catch (Exception $e) {

            if ($transaction !== null) {
                $transaction->rollBack();
            }

            throw $e;
        }

        return true;
    }

    /**
     * This service allows duplicate fields from Layout
     *
     * @param Form $form
     * @param      $postFieldLayout
     *
     * @return FieldLayout|null
     * @throws Throwable
     */
    public function getDuplicateLayout(Form $form, FieldLayout $postFieldLayout)
    {
        if (!$form || !$postFieldLayout) {
            return null;
        }

        /** @var FieldLayoutTab[] $tabs */
        $oldTabs = $postFieldLayout->getTabs();
        $tabs = [];
        $fields = [];

        foreach ($oldTabs as $oldTab) {
            /** @var Field[] $fieldLayoutFields */
            $fieldLayoutFields = $oldTab->getFields();
            $tabFields = [];

            foreach ($fieldLayoutFields as $fieldLayoutField) {

                /** @var Field $field */
                $field = Craft::$app->getFields()->createField([
                    'type' => get_class($fieldLayoutField),
                    'name' => $fieldLayoutField->name,
                    'handle' => $fieldLayoutField->handle,
                    'instructions' => $fieldLayoutField->instructions,
                    'required' => $fieldLayoutField->required,
                    'settings' => $fieldLayoutField->getSettings()
                ]);

                Craft::$app->content->fieldContext = $form->getFieldContext();
                Craft::$app->content->contentTable = $form->getContentTable();

                // Save duplicate field
                Craft::$app->fields->saveField($field);

                $fields[] = $field;
                $tabFields[] = $field;
            }

            $newTab = new FieldLayoutTab();
            $newTab->name = urldecode($oldTab->name);
            $newTab->sortOrder = 0;
            $newTab->setFields($tabFields);

            $tabs[] = $newTab;
        }

        $fieldLayout = new FieldLayout();
        $fieldLayout->type = FormElement::class;
        $fieldLayout->setTabs($tabs);
        $fieldLayout->setFields($fields);

        return $fieldLayout;
    }

    /**
     * @return FormField[]|array
     */
    public function getRegisteredFields(): array
    {
        if (null === $this->registeredFields) {
            $this->registeredFields = [];

            // Our fields are registered in the SproutForms main class
            $event = new RegisterFieldsEvent([
                'fields' => []
            ]);

            $this->trigger(self::EVENT_REGISTER_FIELDS, $event);

            $fields = $event->fields;

            /**
             * @var FormField $instance
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
    public function getRegisteredFieldsByGroup(): array
    {
        $standardLabel = Craft::t('sprout-forms', 'Standard Fields');
        $specialLabel = Craft::t('sprout-forms', 'Special Fields');
        $relationsLabel = Craft::t('sprout-forms', 'Relations Fields');

        // Standard
        $groupedFields[$standardLabel][] = SingleLine::class;
        $groupedFields[$standardLabel][] = Paragraph::class;
        $groupedFields[$standardLabel][] = MultipleChoice::class;
        $groupedFields[$standardLabel][] = Dropdown::class;
        $groupedFields[$standardLabel][] = Checkboxes::class;
        $groupedFields[$standardLabel][] = Number::class;
        $groupedFields[$standardLabel][] = FileUpload::class;
        $groupedFields[$standardLabel][] = SectionHeading::class;

        // Special
        $groupedFields[$specialLabel][] = Name::class;
        $groupedFields[$specialLabel][] = OptIn::class;
        $groupedFields[$specialLabel][] = Email::class;
        $groupedFields[$specialLabel][] = EmailDropdown::class;
        $groupedFields[$specialLabel][] = Url::class;
        $groupedFields[$specialLabel][] = Phone::class;
        $groupedFields[$specialLabel][] = Address::class;
        $groupedFields[$specialLabel][] = Date::class;
        $groupedFields[$specialLabel][] = CustomHtml::class;
        $groupedFields[$specialLabel][] = PrivateNotes::class;
        $groupedFields[$specialLabel][] = MultiSelect::class;
        $groupedFields[$specialLabel][] = RegularExpression::class;
        $groupedFields[$specialLabel][] = Hidden::class;
        $groupedFields[$specialLabel][] = Invisible::class;


        // Relations
        $groupedFields[$relationsLabel][] = Categories::class;
        $groupedFields[$relationsLabel][] = Entries::class;
        $groupedFields[$relationsLabel][] = Tags::class;
        $groupedFields[$relationsLabel][] = Users::class;

        return $groupedFields;
    }

    /**
     * @param $type
     *
     * @return FormField|null
     */
    public function getRegisteredField($type)
    {
        $fields = $this->getRegisteredFields();

        foreach ($fields as $field) {
            if ($field->getType() == $type) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Returns the value of a given field
     *
     * @param string $field
     * @param string $value
     *
     * @return FieldRecord|null
     */
    public function getFieldValue($field, $value)
    {
        return FieldRecord::findOne([
            $field => $value
        ]);
    }

    /**
     * This service allows add a field to a current FieldLayoutFieldRecord
     *
     * @param Field       $field
     * @param FormElement $form
     * @param int         $tabId
     * @param int         $nextId the next field Id
     * @param bool        $required whether the field should be required
     *
     * @return boolean
     */
    public function addFieldToLayout($field, $form, $tabId, $nextId = null, bool $required = false): bool
    {
        $layout = $form->getFieldLayout();
        /** @var FieldLayoutTab|null $tab */
        $tab = ArrayHelper::firstWhere($layout->getTabs(), 'id', $tabId);

        if (!$tab) {
            Craft::warning("Invalid field layout tab ID: $tabId", __METHOD__);
            return false;
        }

        $fieldElement = new CustomField($field, [
            'required' => $required,
        ]);

        $placed = false;

        if ($nextId) {
            foreach ($tab->elements as $i => $element) {
                if ($element instanceof CustomField && $element->getField()->id == $nextId) {
                    array_splice($tab->elements, $i, 0, [$fieldElement]);
                    $placed = true;
                    break;
                }
            }
        }

        if (!$placed) {
            $tab->elements[] = $fieldElement;
        }

        return Craft::$app->getFields()->saveLayout($layout);
    }

    /**
     * This service allows update a field to a current FieldLayoutFieldRecord
     *
     * @param Field       $field
     * @param FormElement $form
     * @param int         $tabId
     * @param bool        $required whether the field should be required
     *
     * @return boolean
     */
    public function updateFieldToLayout($field, $form, $tabId, bool $required = false): bool
    {
        $layout = $form->getFieldLayout();

        // Find and update/remove the current field element
        foreach ($layout->getTabs() as $tab) {
            foreach ($tab->elements as $i => $element) {
                if ($element instanceof CustomField && $element->getField()->id == $field->id) {
                    if ($tab->id == $tabId) {
                        // The field is already where it needs to be. Just update its `required` setting and save.
                        $element->required = $required;
                        return Craft::$app->getFields()->saveLayout($layout);
                    }

                    // It's in the wrong tab so remove it
                    unset($tab->elements[$i]);
                    break 2;
                }
            }
        }

        // Append the field to the expected tab
        return $this->addFieldToLayout($field, $form, $tabId, null, $required);
    }

    /**
     * Loads the sprout modal field via ajax.
     *
     * @param FormElement $form
     * @param FormField   $field
     * @param null        $tabId
     *
     * @return array
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function getModalFieldTemplate(Form $form, $field = null, $tabId = null): array
    {
        $fieldsService = Craft::$app->getFields();
        $request = Craft::$app->getRequest();

        $data = [];
        $data['tabId'] = null;
        $data['field'] = $fieldsService->createField(SingleLine::class);

        if ($field) {
            $data['field'] = $field;
            $tabIdByPost = $request->getBodyParam('tabId');

            if ($tabIdByPost !== null) {
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
        $data['form'] = $form;
        $data['fieldClass'] = $data['field'] ? get_class($data['field']) : null;
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
     * @return FieldInterface
     * @throws Throwable
     */
    public function createDefaultField($type, Form $form): FieldInterface
    {
        /** @var FieldInterface $instanceField */
        $instanceField = new $type;
        $fieldsService = Craft::$app->getFields();
        // get the field name and remove spaces
        $fieldName = preg_replace('/\s+/', '', $instanceField::displayName());
        // strip all non-alphanumeric characters
        $fieldName = preg_replace('/[^A-Za-z0-9 ]/', '', $fieldName);
        $handleName = StringHelper::toCamelCase(lcfirst($fieldName));

        $fieldHandles = (new Query())
            ->select(['handle'])
            ->from(['{{%fields}}'])
            ->where(['context' => 'sproutForms:'.$form->id])
            ->column();

        if ($appendValue = $this->newFieldAppendValue($fieldHandles)) {
            $handleName .= $appendValue;
        }

        $field = $fieldsService->createField([
            'type' => $type,
            'name' => $fieldName,
            'handle' => $handleName,
            'instructions' => '',
            // @todo - test locales/sites behavior
            'translationMethod' => Field::TRANSLATION_METHOD_NONE,
        ]);

        // Set our field context
        Craft::$app->content->fieldContext = $form->getFieldContext();
        Craft::$app->content->contentTable = $form->getContentTable();

        $fieldsService->saveField($field);

        return $field;
    }

    /**
     * @param             $formId
     * @param             $name
     *
     * @return FieldLayoutTabRecord
     * @throws InvalidConfigException
     * @throws ElementNotFoundException
     */
    public function createNewTab($formId, $name): FieldLayoutTabRecord
    {
        $form = SproutForms::$app->forms->getFormById($formId);

        if (!$form) {
            throw new ElementNotFoundException('No Form exists with id '.$form->id);
        }

        $fieldLayout = $form->getFieldLayout();

        $maxSortOrder = (new Query())
            ->select('sortOrder')
            ->from(Table::FIELDLAYOUTTABS)
            ->where([
                'layoutId' => $fieldLayout->id
            ])
            ->orderBy('sortOrder desc')
            ->scalar();

        // Place after other tabs
        $sortOrder = (int)$maxSortOrder + 1;

        $tabRecord = new FieldLayoutTabRecord();
        $tabRecord->name = strip_tags($name);
        $tabRecord->sortOrder = $sortOrder;
        $tabRecord->layoutId = $fieldLayout->id;

        $tabRecord->save();

        return $tabRecord;
    }

    /**
     * Renames tab of form layout
     *
     * @param $tabId
     * @param $newName
     *
     * @return bool
     */
    public function renameTab($tabId, $newName): bool
    {
        $response = false;

        $tabRecord = FieldLayoutTabRecord::findOne($tabId);

        if ($tabRecord) {
            $tabRecord->name = $newName;
            $response = $tabRecord->save(false);
        }

        return $response;
    }

    /**
     * @param FormElement          $form
     * @param FieldLayoutTabRecord $tabRecord
     *
     * @return bool
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteTab(Form $form, FieldLayoutTabRecord $tabRecord): bool
    {
        $fieldLayout = $form->getFieldLayout();

        if (count($fieldLayout->getTabs()) <= 1) {
            $tabRecord->addError('fieldLayoutId', Craft::t('sprout-forms', 'Unable to delete page. One page required.'));

            return false;
        }

        $tabRecord->delete();

        if ($tabRecord->hasErrors()) {
            return false;
        }

        return true;
    }

    public function getFieldLayoutTabs($layoutId): array
    {
        $results = (new Query())
            ->select('*')
            ->from(Table::FIELDLAYOUTTABS)
            ->where([
                'layoutId' => $layoutId
            ])
            ->orderBy('sortOrder asc')
            ->all();

        return $results;
    }

//    public function what() {
//        $fieldLayout = Craft::$app->fields->assembleLayout($postedFieldLayout, $requiredFields);
//        $fieldLayout->type = FormElement::class;
//
//        // Set the tab to the form
//        $form->setFieldLayout($fieldLayout);
//    }

    /**
     * Prepends a key/value pair to an array
     *
     * @param array  $haystack
     * @param string $key
     * @param mixed  $value
     *
     * @return array
     * @see array_unshift()
     *
     */
    public function prependKeyValue(array $haystack, $key, $value): array
    {
        $haystack = array_reverse($haystack, true);
        $haystack[$key] = $value;

        return array_reverse($haystack, true);
    }

    /**
     * @param int $fieldId
     *
     * @return FieldLayoutFieldRecord
     * @throws Exception
     */
    protected function getFieldLayoutFieldRecordByFieldId($fieldId = null): FieldLayoutFieldRecord
    {
        if ($fieldId) {
            /** @var FieldLayoutFieldRecord $fieldLayoutFieldRecord */
            $fieldLayoutFieldRecord = FieldLayoutFieldRecord::find()
                ->where('fieldId=:fieldId', [
                    ':fieldId' => $fieldId
                ]);

            if (!$fieldLayoutFieldRecord) {
                throw new Exception('No field exists with the ID '.$fieldId);
            }

            return $fieldLayoutFieldRecord;
        }

        return new FieldLayoutFieldRecord();
    }

    private function newFieldAppendValue($handles): ?int
    {
        $defaultHandleIncrementValues = array_map(static function($handle) {
            preg_match('/(\d*)$/', $handle, $matches);

            if (!isset($matches[0])) {
                return null;
            }

            return (int)$matches[0];
        }, $handles);

        // If we don't have any handles ending in numbers but we did find forms
        // that began with 'form', we can assume the only formHandle is the default
        if (empty($defaultHandleIncrementValues)) {
            return count($handles) ? 1 : null;
        }

        return max($defaultHandleIncrementValues) + 1;
    }
}
