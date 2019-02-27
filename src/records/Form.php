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
 * @property string    $submitAction
 * @property string    $submitButtonText
 * @property bool      $saveData
 * @property string    $templateOverridesFolder
 * @property string    $oldHandle
 * @property bool      $enableFileAttachments
 */
class Form extends ActiveRecord
{
    private $_oldHandle;
    private $oldRecord;

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

    /**
     * Returns the form’s group.
     *
     * @return ActiveQueryInterface The relational query object.
     *
     * public function getGroup(): ActiveQueryInterface
     * {
     * return $this->hasOne(FormGroup::class, ['id' => 'groupId']);
     * }*/

    /**
     * Store the old handle.
     */
    public function afterFind()
    {
        $this->_oldHandle = $this->handle;
        $this->oldRecord = clone $this;
    }

    /**
     * Returns the old handle.
     *
     * @return string
     */
    public function getOldHandle(): string
    {
        return $this->_oldHandle;
    }

    /**
     * Before Save
     *
     */
    // @todo - add before save method
    /*
    public function beforeSave()
    {
        // Check if the titleFormat is updated
        if (!$this->isNewRecord())
        {
            if ($this->titleFormat != $this->oldRecord->titleFormat)
            {
                $contentTable = 'sproutformscontent_' . trim(strtolower($this->handle));
                $entries      = sproutForms()->entries->getContentEntries($contentTable);
                // Call the update task
                craft()->tasks->createTask('SproutForms_TitleFormat', null,
                    array(
                        'contentRows'  => $entries,
                        'newFormat'    => $this->titleFormat,
                        'contentTable' => $contentTable
                    )
                );
            }
        }

        return true;
    }*/

}