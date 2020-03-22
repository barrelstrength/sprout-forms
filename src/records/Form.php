<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;
use craft\records\Element;
use yii\db\ActiveQueryInterface;


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
 * @property string    $submissionMethod
 * @property string    $errorDisplayMethod
 * @property string    $successMessage
 * @property string    $errorMessage
 * @property string    $submitButtonText
 * @property bool      $saveData
 * @property string    $formTemplate
 * @property bool      $enableCaptchas
 * @property string    $oldHandle
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
}