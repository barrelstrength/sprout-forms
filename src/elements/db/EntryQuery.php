<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\elements\db;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use yii\base\InvalidConfigException;

class EntryQuery extends ElementQuery
{
    /**
     * @var int
     */
    public $statusId;

    /**
     * @var string
     */
    public $ipAddress;

    /**
     * @var string
     */
    public $userAgent;

    /**
     * @var int
     */
    public $formId;

    /**
     * @var string
     */
    public $formHandle;

    /**
     * @var string
     */
    public $formName;

    /**
     * @var int
     */
    public $formGroupId;

    public $status = [];

    /**
     * @inheritdoc
     */
    public function __construct($elementType, array $config = [])
    {
        // Default orderBy
        if (!isset($config['orderBy'])) {
            $config['orderBy'] = 'sproutforms_entries.id';
        }

        parent::__construct($elementType, $config);
    }

    /**
     * Sets the [[statusId]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function statusId($value): EntryQuery
    {
        $this->statusId = $value;

        return $this;
    }

    /**
     * Sets the [[formId]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function formId($value): EntryQuery
    {
        $this->formId = $value;

        return $this;
    }

    /**
     * Sets the [[formHandle]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function formHandle($value): EntryQuery
    {
        $this->formHandle = $value;
        $form = SproutForms::$app->forms->getFormByHandle($value);
        // To add support to filtering we need to have the formId set.
        if ($form) {
            $this->formId = $form->id;
        }

        return $this;
    }

    /**
     * Sets the [[formName]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function formName($value): EntryQuery
    {
        $this->formName = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sproutforms_entries');

        // Figure out which content table to use
        $this->contentTable = null;
        if (!$this->formId && $this->id) {
            $formIds = (new Query())
                ->select(['formId'])
                ->distinct()
                ->from(['{{%sproutforms_entries}}'])
                ->where(Db::parseParam('id', $this->id))
                ->column();

            $this->formId = count($formIds) === 1 ? $formIds[0] : $formIds;
        }

        if ($this->formId && is_numeric($this->formId)) {
            /** @var Form $form */
            $form = SproutForms::$app->forms->getFormById($this->formId);

            if ($form) {
                $this->contentTable = $form->getContentTable();
            }
        }

        $this->query->select([
            'sproutforms_entries.statusId',
            'sproutforms_entries.formId',
            'sproutforms_entries.ipAddress',
            'sproutforms_entries.userAgent',
            'sproutforms_entries.dateCreated',
            'sproutforms_entries.dateUpdated',
            'sproutforms_entries.uid',
            'sproutforms_forms.name as formName',
            'sproutforms_forms.handle as formHandle',
            'sproutforms_forms.groupId as formGroupId',
            'sproutforms_entrystatuses.handle as statusHandle'
        ]);

        $this->query->innerJoin('{{%sproutforms_forms}} sproutforms_forms', '[[sproutforms_forms.id]] = [[sproutforms_entries.formId]]');
        $this->query->innerJoin('{{%sproutforms_entrystatuses}} sproutforms_entrystatuses', '[[sproutforms_entrystatuses.id]] = [[sproutforms_entries.statusId]]');

        $this->query->andWhere(Db::parseParam(
            '[[sproutforms_forms.saveData]]', true
        ));

        $this->subQuery->innerJoin('{{%sproutforms_entrystatuses}} sproutforms_entrystatuses', '[[sproutforms_entrystatuses.id]] = [[sproutforms_entries.statusId]]');

        if ($this->formId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sproutforms_entries.formId', $this->formId
            ));
        }

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam(
                'sproutforms_entries.id', $this->id
            ));
        }

        if ($this->statusId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sproutforms_entries.statusId', $this->statusId
            ));
        }

        if ($this->formHandle) {
            $this->query->andWhere(Db::parseParam(
                'sproutforms_forms.handle', $this->formHandle
            ));
        }

        if ($this->formName) {
            $this->query->andWhere(Db::parseParam(
                'sproutforms_forms.name', $this->formName
            ));
        }

        return parent::beforePrepare();
    }

    /**
     * @inheritDoc
     */
    protected function statusCondition(string $status)
    {
        return Db::parseParam('sproutforms_entrystatuses.handle', $status);
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigException
     */
    protected function customFields(): array
    {
        // This method won't get called if $this->formId isn't set to a single int
        /** @var Form $form */
        $form = SproutForms::$app->forms->getFormById($this->formId);

        return $form->getFields();
    }
}
