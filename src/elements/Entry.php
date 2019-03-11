<?php

namespace barrelstrength\sproutforms\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use craft\elements\actions\Delete;

use barrelstrength\sproutforms\elements\db\EntryQuery;
use barrelstrength\sproutforms\records\Entry as EntryRecord;
use barrelstrength\sproutforms\SproutForms;
use yii\base\Exception;

/**
 * Entry represents a entry element.
 *
 * @property array $payloadFields
 * @property array $fields
 */
class Entry extends Element
{
    // Properties
    // =========================================================================
    private $form;

    public $id;
    public $formId;
    public $formHandle;
    public $statusId;
    public $formGroupId;
    public $formName;
    public $ipAddress;
    public $userAgent;
    /**
     * @var string
     */
    public $statusHandle;

    public function init()
    {
        parent::init();
        $this->setScenario(self::SCENARIO_LIVE);
    }

    /**
     * Returns the field context this element's content uses.
     *
     * @access protected
     * @return string
     */
    public function getFieldContext(): string
    {
        return 'sproutForms:'.$this->formId;
    }

    /**
     * Returns the name of the table this element's content is stored in.
     *
     * @return string
     */
    public function getContentTable(): string
    {
        $form = $this->getForm();

        if ($form) {
            return SproutForms::$app->forms->getContentTableName($this->getForm());
        }

        return '';
    }

    /**
     * Returns the element type name.
     *
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Sprout Forms Entries');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'entries';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'sprout-forms/entries/edit/'.$this->id
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
            // @todo - For some reason the Title returns null possible Craft3 bug
            // @todo - Why do we need to call populateElementContent?
            Craft::$app->getContent()->populateElementContent($this);

            return $this->title ?: ((string)$this->id ?: static::class);
        } catch (\Exception $e) {
            // return empty to avoid errors when form is deleted
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        return $this->getForm()->getFieldLayout();
    }

    /**
     *
     * @return string|null
     */
    public function getStatus()
    {
        $statusId = $this->statusId;

        return SproutForms::$app->entries->getEntryStatusById($statusId)->color;
    }

    /**
     * Returns a list of statuses for this element type
     *
     * @return array
     */
    public static function statuses(): array
    {
        $statuses = SproutForms::$app->entries->getAllEntryStatuses();
        $statusArray = [];

        foreach ($statuses as $status) {
            $key = $status['handle'].' '.$status['color'];
            $statusArray[$key] = $status['name'];
        }

        return $statusArray;
    }

    /**
     * Returns an array of key/value pairs to send along in payload forwarding requests
     *
     * @return array
     */
    public function getPayloadFields(): array
    {
        $fields = [];

        $content = $this->getAttributes();

        foreach ($content as $field => $value) {
            $fields[$field] = $value;
        }

        return $fields;
    }

    /**
     * @inheritdoc
     * @return EntryQuery The newly created [[EntryQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new EntryQuery(static::class);
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout-forms', 'All Entries'),
            ]
        ];

        $sources[] = [
            'heading' => Craft::t('sprout-forms', 'Forms')
        ];

        // Prepare the data for our sources sidebar
        $groups = SproutForms::$app->groups->getAllFormGroups('id');
        $forms = SproutForms::$app->forms->getAllForms();

        $noSources = [];
        $prepSources = [];

        foreach ($forms as $form) {
            $saveData = SproutForms::$app->entries->isDataSaved($form);
            if ($saveData) {
                if ($form->groupId) {
                    if (!isset($prepSources[$form->groupId]['heading']) && isset($groups[$form->groupId])) {
                        $prepSources[$form->groupId]['heading'] = $groups[$form->groupId]->name;
                    }

                    $prepSources[$form->groupId]['forms'][$form->id] = [
                        'label' => $form->name,
                        'data' => ['formId' => $form->id],
                        'criteria' => ['formId' => $form->id]
                    ];
                } else {
                    $noSources[$form->id] = [
                        'label' => $form->name,
                        'data' => ['formId' => $form->id],
                        'criteria' => ['formId' => $form->id]
                    ];
                }
            }
        }

        // Build our sources for forms with no group
        foreach ($noSources as $form) {
            $key = 'form:'.$form['data']['formId'];
            $sources[] = [
                'key' => $key,
                'label' => $form['label'],
                'data' => [
                    'formId' => $form['data']['formId'],
                ],
                'criteria' => [
                    'formId' => $form['criteria']['formId'],
                ]
            ];
        }

        // Build our sources sidebar for forms in groups
        foreach ($prepSources as $source) {
            if (isset($source['heading'])) {
                $sources[] = [
                    'heading' => $source['heading']
                ];
            }

            foreach ($source['forms'] as $form) {
                $key = 'form:'.$form['data']['formId'];
                $sources[] = [
                    'key' => $key,
                    'label' => $form['label'],
                    'data' => [
                        'formId' => $form['data']['formId'],
                    ],
                    'criteria' => [
                        'formId' => $form['criteria']['formId'],
                    ]
                ];
            }
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
            'confirmationMessage' => Craft::t('sprout-forms', 'Are you sure you want to delete the selected entries?'),
            'successMessage' => Craft::t('sprout-forms', 'Entries deleted.'),
        ]);

        return $actions;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['id', 'title', 'formName'];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'sproutforms_entries.dateCreated' => Craft::t('sprout-forms', 'Date Created'),
            // @todo - fix error where formName is not a column on subquery
            //'formName'               => Craft::t('sprout-forms','Form Name'),
            'sproutforms_entries.dateUpdated' => Craft::t('sprout-forms', 'Date Updated'),
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes['title'] = ['label' => Craft::t('sprout-forms', 'Title')];
        $attributes['formName'] = ['label' => Craft::t('sprout-forms', 'Form Name')];
        $attributes['dateCreated'] = ['label' => Craft::t('sprout-forms', 'Date Created')];
        $attributes['dateUpdated'] = ['label' => Craft::t('sprout-forms', 'Date Updated')];

        foreach (Craft::$app->elementIndexes->getAvailableTableAttributes(Form::class) as $key => $field) {
            $customFields = explode(':', $key);
            if (count($customFields) > 1) {
                $fieldId = $customFields[1];
                $attributes['field:'.$fieldId] = ['label' => $field['label']];
            }
        }

        return $attributes;
    }

    /**
     * @param string $source
     *
     * @return array
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['title', 'formName', 'dateCreated', 'dateUpdated'];
    }

    /**
     * @param bool $isNew
     *
     * @throws Exception
     */
    public function afterSave(bool $isNew)
    {
        // Get the entry record
        if (!$isNew) {
            $record = EntryRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid Entry ID: '.$this->id);
            }
        } else {
            $record = new EntryRecord();
            $record->id = $this->id;
        }

        $record->ipAddress = $this->ipAddress;
        $record->formId = $this->formId;
        $record->statusId = $this->statusId;
        $record->userAgent = $this->userAgent;

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
        return $this->getForm()->getFields();
    }

    /**
     * Returns the form element associated with this entry
     *
     * @return Form|null
     */
    public function getForm()
    {
        if ($this->form === null) {
            $this->form = SproutForms::$app->forms->getFormById($this->formId);
        }

        return $this->form;
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [['formId'], 'required'];

        return $rules;
    }
}