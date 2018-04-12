<?php

namespace barrelstrength\sproutforms\elements\db;

use Craft;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

use barrelstrength\sproutforms\SproutForms;

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

    /**
     * Sets the [[statusId]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function statusId($value)
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
    public function formId($value)
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
    public function formHandle($value)
    {
        $this->formHandle = $value;

        return $this;
    }

    /**
     * Sets the [[formName]] property.
     *
     * @param int
     *
     * @return static self reference
     */
    public function formName($value)
    {
        $this->formName = $value;

        return $this;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sproutforms_entries');

        // Figure out which content table to use
        $this->contentTable = null;

        if ($this->id && $this->formId) {
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
            'sproutforms_forms.groupId as formGroupId'
        ]);

        $this->query->innerJoin('{{%sproutforms_entrystatuses}} sproutforms_entrystatuses', '[[sproutforms_entrystatuses.id]] = [[sproutforms_entries.statusId]]');
        $this->query->innerJoin('{{%sproutforms_forms}} sproutforms_forms', '[[sproutforms_forms.id]] = [[sproutforms_entries.formId]]');

        $this->joinContentTableAndAddContentSelects($this);

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam(
                'sproutforms_entries.id', $this->id)
            );
        }

        if ($this->formId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sproutforms_entries.formId', $this->formId)
            );
        }

        if ($this->statusId) {
            $this->subQuery->andWhere(Db::parseParam(
                'sproutforms_entries.statusId', $this->statusId)
            );
        }

        if ($this->formHandle) {
            $this->query->andWhere(Db::parseParam(
                'sproutforms_forms.handle', $this->formHandle)
            );
        }

        if ($this->formName) {
            $this->query->andWhere(Db::parseParam(
                'sproutforms_forms.name', $this->formName)
            );
        }

        if (!$this->orderBy) {
            $this->orderBy = ['sproutforms_entries.dateCreated' => SORT_DESC];
        }

        return parent::beforePrepare();
    }

    /**
     * Updates the query command, criteria, and select fields when a source is available
     *
     * @param EntryQuery $entryQuery
     */
    protected function joinContentTableAndAddContentSelects(
        EntryQuery $entryQuery
    ) {
        // Do we have a source selected in the sidebar?
        // If so, we have a form id and we can use that to fetch the content table
        if ($entryQuery->formId || $entryQuery->formHandle) {
            $form = null;

            if ($entryQuery->formId) {
                $form = SproutForms::$app->forms->getFormById($entryQuery->formId);
            } else {
                if ($entryQuery->formHandle) {
                    $form = SproutForms::$app->forms->getFormByHandle($entryQuery->formHandle);
                }
            }

            if ($form) {
                $fields = $form->getFields();
                $fieldPrefix = Craft::$app->content->fieldColumnPrefix;

                $select = $entryQuery->query->select;
                $select[] = "{$form->handle}.title";

                // Added support for filtering any sproutform content table
                foreach ($fields as $key => $field) {
                    if ($field->hasContentColumn()) {
                        $select[] = "{$form->handle}.{$fieldPrefix}{$field->handle} as {$field->handle}";
                        $handle = $field->handle;

                        if (isset($entryQuery->$handle)) {
                            $entryQuery->subQuery->andWhere(Db::parseParam(
                                $form->handle.'.'.$fieldPrefix.$field->handle,
                                $entryQuery->$handle)
                            );
                        }
                    }
                }

                $entryQuery->query->innerJoin(
                    $form->getContentTable().' as '.$form->handle,
                    '[['.$form->handle.'.elementId]] = [[subquery.elementsId]]'
                );

                $entryQuery->query->select = $select;
            }
        }
    }
}
