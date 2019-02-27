<?php

namespace barrelstrength\sproutforms\elements;

use barrelstrength\sproutforms\base\FormTemplates;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\validators\TemplateOverridesValidator;
use Craft;
use craft\base\Element;
use craft\base\FieldInterface;
use craft\elements\db\ElementQueryInterface;
use yii\base\ErrorHandler;
use craft\db\Query;
use craft\helpers\UrlHelper;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use craft\behaviors\FieldLayoutBehavior;

use barrelstrength\sproutforms\elements\db\FormQuery;
use barrelstrength\sproutforms\records\Form as FormRecord;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\actions\Delete;
use yii\base\Exception;

/**
 * Form represents a form element.
 *
 * @property \barrelstrength\sproutforms\base\FormTemplates $formTemplate
 * @property array                                          $fields
 */
class Form extends Element
{
    // Properties
    // =========================================================================
    private $_fields;

    /**
     * @var int|null Group ID
     */
    public $groupId;

    /**
     * @var int|null name
     */
    public $name;
    public $handle;
    public $oldHandle;
    public $saveAsNew;
    public $fieldLayoutId;
    public $titleFormat;
    public $displaySectionTitles = false;
    public $redirectUri;
    public $submitAction;
    public $submitButtonText;
    public $saveData = false;
    public $templateOverridesFolder;
    public $enableFileAttachments = false;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => self::class
            ],
        ]);
    }

    /**
     * Returns the field context this element's content uses.
     *
     * @access protected
     * @return string
     */
    public function getFieldContext(): string
    {
        return 'sproutForms:'.$this->id;
    }

    /**
     * Returns the name of the table this element's content is stored in.
     *
     * @return string
     */
    public function getContentTable(): string
    {
        return SproutForms::$app->forms->getContentTableName($this);
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Sprout Forms');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'form';
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'sprout-forms/forms/edit/'.$this->id
        );
    }

    /**
     * Use the name as the string representation.
     *
     * @return string
     */
    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function __toString()
    {
        try {
            return (string)$this->name;
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
        }
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        $behaviors = $this->getBehaviors();
        $fieldLayout = $behaviors['fieldLayout'];

        return $fieldLayout->getFieldLayout();
    }

    /**
     * @inheritdoc
     *
     * @return FormQuery The newly created [[FormQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new FormQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout-forms', 'All Forms'),
            ]
        ];

        $groups = SproutForms::$app->groups->getAllFormGroups();

        foreach ($groups as $group) {
            $key = 'group:'.$group->id;

            $sources[] = [
                'key' => $key,
                'label' => Craft::t('sprout-forms', $group->name),
                'data' => ['id' => $group->id],
                'criteria' => ['groupId' => $group->id]
            ];
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        // Delete
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => Delete::class,
        ]);

        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['name', 'handle'];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'name' => Craft::t('sprout-forms', 'Form Name'),
            'elements.dateCreated' => Craft::t('sprout-forms', 'Date Created'),
            'elements.dateUpdated' => Craft::t('sprout-forms', 'Date Updated'),
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes['name'] = ['label' => Craft::t('sprout-forms', 'Name')];
        $attributes['handle'] = ['label' => Craft::t('sprout-forms', 'Handle')];
        $attributes['numberOfFields'] = ['label' => Craft::t('sprout-forms', 'Number of Fields')];
        $attributes['totalEntries'] = ['label' => Craft::t('sprout-forms', 'Total Entries')];

        return $attributes;
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['name', 'handle', 'numberOfFields', 'totalEntries'];
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {

            case 'handle':
                {
                    return '<code>'.$this->handle.'</code>';
                }
            case 'numberOfFields':
                {
                    $totalFields = (new Query())
                        ->select('COUNT(*)')
                        ->from('{{%fieldlayoutfields}}')
                        ->where(['layoutId' => $this->fieldLayoutId])
                        ->scalar();

                    return $totalFields;
                }
            case 'totalEntries':
                {
                    $totalEntries = (new Query())
                        ->select('COUNT(*)')
                        ->from('{{%sproutforms_entries}}')
                        ->where(['formId' => $this->id])
                        ->scalar();

                    return $totalEntries;
                }
        }

        return parent::tableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getEditorHtml(): string
    {
        $html = '';

        if ($this->getType()->hasTitleField) {
            $html = Craft::$app->getView()->renderTemplate('forms/_hud/titlefield',
                [
                    'entry' => $this
                ]
            );
        }

        $html .= parent::getEditorHtml();

        return $html;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        // Get the form record
        if (!$isNew) {
            $record = FormRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid Form ID: '.$this->id);
            }
        } else {
            $record = new FormRecord();
            $record->id = $this->id;
        }

        $record->fieldLayoutId = $this->fieldLayoutId;
        $record->name = $this->name;
        $record->handle = $this->handle;
        $record->titleFormat = $this->titleFormat;
        $record->displaySectionTitles = $this->displaySectionTitles;
        $record->groupId = $this->groupId;
        $record->redirectUri = $this->redirectUri;
        $record->submitAction = $this->submitAction;
        $record->saveData = $this->saveData;
        $record->submitButtonText = $this->submitButtonText;
        $record->templateOverridesFolder = $this->templateOverridesFolder;
        $record->enableFileAttachments = $this->enableFileAttachments;

        $record->save(false);

        parent::afterSave($isNew);
    }

    /**
     * Returns the fields associated with this form.
     *
     * @return array
     */
    public function getFields(): array
    {
        if ($this->_fields === null) {
            $this->_fields = [];

            $fields = $this->getFieldLayout()->getFields();

            foreach ($fields as $field) {
                $this->_fields[$field->handle] = $field;
            }
        }

        return $this->_fields;
    }

    /**
     * @param string $handle
     *
     * @return null|FieldInterface
     */
    public function getField($handle)
    {
        $fields = $this->getFields();

        if (is_string($handle) && !empty($handle)) {
            return $fields[$handle] ?? null;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 255],
            [
                ['handle'],
                HandleValidator::class,
                'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']
            ],

            [
                ['templateOverridesFolder'],
                TemplateOverridesValidator::class
            ],
            [['name', 'handle'], UniqueValidator::class, 'targetClass' => FormRecord::class]
        ];
    }

    /**
     * @param null $cssClasses
     *
     * @return array
     */
    public function getClassesOptions($cssClasses = null): array
    {
        $classesIds = [];
        $apiOptions = $this->getFormTemplate()->getCssClassDefaults();
        $options = [
            [
                'label' => Craft::t('sprout-forms', 'Select...'),
                'value' => ''
            ]
        ];

        foreach ($apiOptions as $key => $option) {
            $options[] = [
                'label' => $option,
                'value' => $key
            ];
            $classesIds[] = $key;
        }

        $options[] = [
            'optgroup' => Craft::t('sprout-forms', 'Custom CSS Classes')
        ];

        if (!in_array($cssClasses, $classesIds, true) && $cssClasses) {
            $options[] = [
                'label' => $cssClasses,
                'value' => $cssClasses
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout-forms', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }

    /**
     * Get the global template used by Sprout Forms
     *
     * @return FormTemplates
     */
    public function getFormTemplate(): FormTemplates
    {
        $defaultFormTemplates = new AccessibleTemplates();

        if ($this->templateOverridesFolder) {
            $templatePath = SproutForms::$app->forms->getFormTemplateById($this->templateOverridesFolder);
            if ($templatePath) {
                return $templatePath;
            }
        }

        return $defaultFormTemplates;
    }
}