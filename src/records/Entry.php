<?php

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;
use craft\records\Element;

use barrelstrength\sproutforms\records\Form as FormRecord;
use barrelstrength\sproutforms\records\EntryStatus as EntryStatusRecord;

/**
 * Class Entry record.
 *
 * @property int                          $id
 * @property int                          $statusId
 * @property string                       $ipAddress
 * @property string                       $userAgent
 * @property \yii\db\ActiveQueryInterface $element
 * @property \yii\db\ActiveQueryInterface $entryStatuses
 * @property \yii\db\ActiveQueryInterface $form
 * @property string                       $formId
 */
class Entry extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_entries}}';
    }

    /**
     * Returns the entry’s element.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getElement(): ActiveQueryInterface
    {
        return $this->hasOne(Element::class, ['id' => 'id']);
    }

    /**
     * Returns the form's.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getForm(): ActiveQueryInterface
    {
        return $this->hasMany(FormRecord::class, ['formId' => 'id']);
    }

    /**
     * Returns the Entry Statuses.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getEntryStatuses(): ActiveQueryInterface
    {
        return $this->hasMany(EntryStatusRecord::class, ['statusId' => 'id']);
    }

}