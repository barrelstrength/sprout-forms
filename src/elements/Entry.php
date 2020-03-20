<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\elements;

use barrelstrength\sproutforms\base\Captcha;
use barrelstrength\sproutforms\elements\actions\MarkAsDefaultStatus;
use barrelstrength\sproutforms\elements\actions\MarkAsSpam;
use barrelstrength\sproutforms\elements\db\EntryQuery;
use barrelstrength\sproutforms\models\EntriesSpamLog;
use barrelstrength\sproutforms\models\EntryStatus;
use barrelstrength\sproutforms\records\Entry as EntryRecord;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\Element;
use craft\db\Query;
use craft\elements\actions\Delete;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\UrlHelper;
use craft\models\FieldLayout;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Entry represents a entry element.
 *
 * @property array|ActiveRecord[] $integrationLog
 * @property null|array           $conditionalLogicResults
 * @property null|array           $hiddenFields
 * @property bool                 $isSpam
 * @property array                $captchaErrors
 * @property array                $savedCaptchaErrors
 * @property array                $fields
 */
class Entry extends Element
{
    public $id;

    public $formId;

    public $formHandle;

    public $statusId;

    public $statusHandle;

    public $formGroupId;

    public $formName;

    public $ipAddress;

    public $referrer;

    public $userAgent;

    /** @var Captcha[] $captchas */
    protected $captchas = [];

    private $form;

    private $integrationLogs = [];

    /** @var array|null */
    private $conditionalResults;

    /** @var array|null */
    private $entryHiddenFields;

    /**
     * Returns the element type name.
     *
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Form Entry');
    }

    /**
     * @return string
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('sprout-forms', 'Form Entries');
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
     * Returns a list of statuses for this element type
     *
     * @return array
     */
    public static function statuses(): array
    {
        $statuses = SproutForms::$app->entryStatuses->getAllEntryStatuses();
        $statusArray = [];

        foreach ($statuses as $status) {
            $key = $status['handle'];
            $statusArray[$key] = [
                'label' => $status['name'],
                'color' => $status['color'],
            ];
        }

        return $statusArray;
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
            $saveData = SproutForms::$app->entries->isSaveDataEnabled($form);
            if ($saveData) {
                if ($form->groupId) {
                    if (!isset($prepSources[$form->groupId]['heading']) && isset($groups[$form->groupId])) {
                        $prepSources[$form->groupId]['heading'] = $groups[$form->groupId]->name;
                    }

                    $prepSources[$form->groupId]['forms'][$form->id] = [
                        'label' => $form->name,
                        'data' => ['formId' => $form->id],
                        'criteria' => [
                            'formId' => $form->id
                        ]
                    ];
                } else {
                    $noSources[$form->id] = [
                        'label' => $form->name,
                        'data' => ['formId' => $form->id],
                        'criteria' => [
                            'formId' => $form->id
                        ]
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
                    'formId' => $form['criteria']['formId']
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
                        'formId' => $form['criteria']['formId']
                    ]
                ];
            }
        }

        $settings = SproutForms::getInstance()->getSettings();

        $sources[] = [
            'heading' => Craft::t('sprout-forms', 'Misc')
        ];

        if ($settings->saveSpamToDatabase) {
            $sources[] = [
                'key' => 'sproutFormsWithSpam',
                'label' => 'Spam',
                'criteria' => [
                    'status' => EntryStatus::SPAM_STATUS_HANDLE
                ]
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
            'confirmationMessage' => Craft::t('sprout-forms', 'Are you sure you want to delete the selected entries?'),
            'successMessage' => Craft::t('sprout-forms', 'Entries deleted.'),
        ]);

        // Mark As Spam
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => MarkAsSpam::class
        ]);

        // Mark As Default Status
        $actions[] = Craft::$app->getElements()->createAction([
            'type' => MarkAsDefaultStatus::class
        ]);

        return $actions;
    }

    /**
     * Use the name as the string representation.
     *
     * @return string
     */
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
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'sprout-forms/entries/edit/'.$this->id
        );
    }


    public function __toString()
    {
        try {
            // @todo - For some reason the Title returns null possible Craft3 bug
            // @todo - Why do we need to call populateElementContent?
            Craft::$app->getContent()->populateElementContent($this);

            $string = (string)$this->id ?: static::class;

            return $this->title ?: $string;
        } catch (\Exception $e) {
            // return empty to avoid errors when form is deleted
            return '';
        }
    }

    /**
     * @return FieldLayout
     * @throws InvalidConfigException
     */
    public function getFieldLayout(): FieldLayout
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

        return SproutForms::$app->entryStatuses->getEntryStatusById($statusId)->handle;
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
     * @throws InvalidConfigException
     */
    public function getFields(): array
    {
        return $this->getForm()->getFields();
    }

    /**
     * Returns the form element associated with this entry
     * Due to soft delete, deleted forms leaves entries with not forms
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
     * @return array
     */
    public function getIntegrationLogs(): array
    {
        return $this->integrationLogs;
    }

    /**
     * @return array|ActiveRecord[]
     */
    public function getIntegrationLog(): array
    {
        return SproutForms::$app->integrations->getIntegrationLogsByEntryId($this->id);
    }

    /**
     * @param $conditionalResults
     */
    public function setConditionalLogicResults(array $conditionalResults)
    {
        $this->conditionalResults = $conditionalResults;
    }

    /**
     * @return array|null
     */
    public function getConditionalLogicResults()
    {
        return $this->conditionalResults;
    }

    /**
     * @param $fieldHandle
     *
     * @return bool
     */
    public function getIsFieldHiddenByRule($fieldHandle): bool
    {
        $hiddenFields = $this->getHiddenFields();

        if (in_array($fieldHandle, $hiddenFields, true)) {
            return true;
        }

        return false;
    }

    /**
     * @param $hiddenFields
     */
    public function setHiddenFields($hiddenFields)
    {
        $this->entryHiddenFields = $hiddenFields;
    }

    /**
     * @return array|null
     */
    public function getHiddenFields()
    {
        return $this->entryHiddenFields ?? [];
    }

    /**
     * @return bool
     */
    public function getIsSpam(): bool
    {
        $status = $this->getStatus();

        return $status === EntryStatus::SPAM_STATUS_HANDLE;
    }

    public function addCaptcha(Captcha $captcha)
    {
        $this->captchas[get_class($captcha)] = $captcha;
    }

    /**
     * @return Captcha[]
     */
    public function getCaptchas(): array
    {
        return $this->captchas;
    }

    /**
     * @return bool
     */
    public function hasCaptchaErrors(): bool
    {
        // When saving in the CP
        if ($this->captchas === null) {
            return false;
        }

        foreach ($this->captchas as $captcha) {
            if ($captcha->hasErrors()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getCaptchaErrors(): array
    {
        $errors = [];

        foreach ($this->captchas as $captcha) {
            if (count($captcha->getErrors())) {
                $errors['captchaErrors'][get_class($captcha)] = $captcha->getErrors('captchaErrors');
            }
        }

        return $errors;
    }

    public function getSavedCaptchaErrors(): array
    {
        $spamLogEntries = (new Query())
            ->select('*')
            ->from('{{%sproutforms_entries_spam_log}}')
            ->where(['entryId' => $this->id])
            ->all();

        $captchaErrors = [];

        foreach ($spamLogEntries as $spamLogEntry) {
            $captchaErrors[] = new EntriesSpamLog($spamLogEntry);
        }

        return $captchaErrors;
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['formId'], 'required'];

        return $rules;
    }
}
