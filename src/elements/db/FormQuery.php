<?php

namespace barrelstrength\sproutforms\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

use barrelstrength\sproutforms\models\FormGroup;

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
    public $submitAction;

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
    public $templateOverridesFolder;

    /**
     * @var bool
     */
    public $enableFileAttachments;

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

    public function group($value)
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
    public function groupId($value)
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
    public function name($value)
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
    public function handle($value)
    {
        $this->handle = $value;

        return $this;
    }

    // Protected Methods
    // =========================================================================

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
            'sproutforms_forms.submitAction',
            'sproutforms_forms.saveData',
            'sproutforms_forms.submitButtonText',
            'sproutforms_forms.templateOverridesFolder',
            'sproutforms_forms.enableFileAttachments'
        ]);

        if ($this->totalEntries) {
            $this->query->addSelect('COUNT(entries.id) totalEntries');
            $this->query->leftJoin('sproutforms_entries entries', '[[entries.formId]] = [[sproutforms_forms.id]]');
        }
        if ($this->numberOfFields) {
            $this->query->addSelect('COUNT(fields.id) numberOfFields');
            $this->query->leftJoin('fieldlayoutfields fields', '[[fields.layoutId]] = [[sproutforms_forms.fieldLayoutId]]');
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

        return parent::beforePrepare();
    }
}
