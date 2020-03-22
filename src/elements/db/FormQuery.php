<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\elements\db;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\models\FormGroup;
use barrelstrength\sproutforms\SproutForms;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class FormQuery extends ElementQuery
{

    /**
     * @var int|int[]|null The tag group ID(s) that the resulting forms must be in.
     */
    public $groupId;

    /**
     * @var int
     */
    public $fieldLayoutId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $handle;

    /**
     * @var string
     */
    public $oldHandle;

    /**
     * @var string
     */
    public $titleFormat;

    /**
     * @var bool
     */
    public $displaySectionTitles;

    /**
     * @var string
     */
    public $redirectUri;

    /**
     * @var string
     */
    public $submissionMethod;

    /**
     * @var string
     */
    public $errorDisplayMethod;

    /**
     * @var string
     */
    public $successMessage;

    /**
     * @var string
     */
    public $errorMessage;

    /**
     * @var string
     */
    public $submitButtonText;

    /**
     * @var bool
     */
    public $saveData;

    /**
     * @var string
     */
    public $formTemplate;

    /**
     * @var bool
     */
    public $enableCaptchas;

    /**
     * @var int
     */
    public $totalEntries;

    /**
     * @var int
     */
    public $numberOfFields;

    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'sproutforms_forms.name';
        }

        parent::__construct($elementType, $config);
    }

    public function group($value): FormQuery
    {
        if ($value instanceof FormGroup) {
            $this->groupId = $value->id;
        } else if ($value !== null) {
            $this->groupId = (new Query())
                ->select(['id'])
                ->from(['{{%sproutforms_formgroups}}'])
                ->where(Db::parseParam('name', $value))
                ->column();
        } else {
            $this->groupId = null;
        }

        return $this;
    }

    /**
     * Sets the [[groupId]] property.
     *
     * @param int|int[]|null $value The property value
     *
     * @return static self reference
     */
    public function groupId($value): FormQuery
    {
        $this->groupId = $value;

        return $this;
    }

    /**
     * Sets the [[name]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function name($value): FormQuery
    {
        $this->name = $value;

        return $this;
    }

    /**
     * Sets the [[handle]] property.
     *
     * @param string|string[]|null $value The property value
     *
     * @return static self reference
     */
    public function handle($value): FormQuery
    {
        $this->handle = $value;

        return $this;
    }

    /**
     * @param $value
     *
     * @return FormQuery
     */
    public function fieldLayoutId($value): FormQuery
    {
        $this->fieldLayoutId = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        // See if 'group' was set to an invalid handle
        if ($this->groupId === []) {
            return false;
        }

        $this->joinElementTable('sproutforms_forms');

        $this->query->select([
            'sproutforms_forms.groupId',
            'sproutforms_forms.id',
            'sproutforms_forms.fieldLayoutId',
            'sproutforms_forms.groupId',
            'sproutforms_forms.name',
            'sproutforms_forms.handle',
            'sproutforms_forms.titleFormat',
            'sproutforms_forms.displaySectionTitles',
            'sproutforms_forms.redirectUri',
            'sproutforms_forms.saveData',
            'sproutforms_forms.submissionMethod',
            'sproutforms_forms.errorDisplayMethod',
            'sproutforms_forms.successMessage',
            'sproutforms_forms.errorMessage',
            'sproutforms_forms.submitButtonText',
            'sproutforms_forms.formTemplate',
            'sproutforms_forms.enableCaptchas'
        ]);

        if ($this->totalEntries) {
            $this->query->addSelect('COUNT(entries.id) totalEntries');
            $this->query->leftJoin('sproutforms_entries entries', '[[entries.formId]] = [[sproutforms_forms.id]]');
        }
        if ($this->numberOfFields) {
            $this->query->addSelect('COUNT(fields.id) numberOfFields');
            $this->query->leftJoin('fieldlayoutfields fields', '[[fields.layoutId]] = [[sproutforms_forms.fieldLayoutId]]');
        }

        if ($this->fieldLayoutId) {
            $this->subQuery->andWhere(Db::parseParam('sproutforms_forms.fieldLayoutId', $this->fieldLayoutId));
        }

        if ($this->groupId) {
            $this->subQuery->andWhere(Db::parseParam('sproutforms_forms.groupId', $this->groupId));
        }

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('sproutforms_forms.handle', $this->handle));
        }

        if ($this->name) {
            $this->subQuery->andWhere(Db::parseParam('sproutforms_forms.name', $this->name));
        }

        $isPro = SproutBase::$app->settings->isEdition('sprout-forms', SproutForms::EDITION_PRO);

        // Limit Sprout Forms Lite to a single form
        if (!$isPro) {
            $this->query->limit(1);
        }

        return parent::beforePrepare();
    }
}
