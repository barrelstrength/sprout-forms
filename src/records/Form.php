<?php
namespace barrelstrength\sproutforms\records;

use Craft;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;
use craft\records\Element;


/**
 * Class Form record.
 *
 * @property int         $id
 * @property int         $groupId
 * @property int         $fieldLayoutId
 * @property string      $name
 * @property string      $handle
 * @property string      $titleFormat
 * @property bool        $displaySectionTitles
 * @property Element     $element
 * @property FormGroup   $group
 * @property string      $redirectUri
 * @property string      $submitAction
 * @property string      $submitButtonText
 * @property bool        $savePayload
 * @property bool        $notificationEnabled
 * @property string      $notificationRecipients
 * @property string      $notificationSubject
 * @property string      $notificationSender
 * @property string      $notificationSenderEmail
 * @property string      $notificationReplyToEmail
 * @property bool        $enableTemplateOverrides
 * @property string      $templateOverridesFolder
 * @property bool        $enableFileAttachments
 * @property FieldLayout $fieldLayout
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

	public function getGroup(): ActiveQueryInterface
	{
		return $this->hasOne(FormGroup::class, ['id' => 'groupId']);
	}*/

	/**
	 * Store the old handle.
	 */
	public function storeOldHandle()
	{
		$this->_oldHandle = $this->handle;
		$this->oldRecord  = clone $this;
	}

	/**
	 * Returns the old handle.
	 *
	 * @return string
	 */
	public function getOldHandle()
	{
		return $this->_oldHandle;
	}

}