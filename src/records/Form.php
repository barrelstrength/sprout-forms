<?php

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;
use craft\records\Element;


/**
 * Class Form record.
 *
 * @property int       $id
 * @property int       $groupId
 * @property int       $fieldLayoutId
 * @property string    $name
 * @property string    $handle
 * @property string    $titleFormat
 * @property bool      $displaySectionTitles
 * @property Element   $element
 * @property FormGroup $group
 * @property string    $redirectUri
 * @property string    $submitButtonText
 * @property bool      $saveData
 * @property string    $templateOverridesFolder
 * @property string    $oldHandle
 * @property bool      $enableFileAttachments
 */
class Form extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_forms}}';
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
}