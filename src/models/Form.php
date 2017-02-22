<?php
namespace barrelstrength\sproutforms\models;

use Craft;
use craft\base\Model;
use craft\behaviors\FieldLayoutBehavior;
use craft\behaviors\FieldLayoutTrait;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form;

class Form extends Model
{
	use FieldLayoutTrait;

	private $_fields;

	public $totalEntries;

	public $numberOfFields;

	public $saveAsNew;

	/**
	 * @var int|null ID
	 */
	public $id;

	/**
	 * @var int
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
	public $savePayload;

	/**
	 * @var bool
	 */
	public $notificationEnabled;

	/**
	 * @var string
	 */
	public $notificationRecipients;

	/**
	 * @var string
	 */
	public $notificationSubject;

	/**
	 * @var string
	 */
	public $notificationSenderName;

	/**
	 * @var string
	 */
	public $notificationSenderEmail;

	/**
	 * @var string
	 */
	public $notificationReplyToEmail;

	/**
	 * @var bool
	 */
	public $enableTemplateOverrides;

	/**
	 * @var string
	 */
	public $templateOverridesFolder;

	/**
	 * @var bool
	 */
	public $enableFileAttachments;


	/**
	 * Use the translated section name as the string representation.
	 *
	 * @return string
	 */
	function __toString()
	{
		return SproutForms::t($this->name);
	}

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'fieldLayout' => [
				'class' => FieldLayoutBehavior::class,
				'elementType' => Form::class
			],
		];
	}

	/**
	 * Returns the entry’s CP edit URL.
	 *
	 * @return string
	 */
	public function getCpEditUrl(): string
	{
		return UrlHelper::cpUrl(
			'sprout-forms/forms/edit/'.$this->id
		);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name', 'handle'], 'required'],
			[['name', 'handle'], 'string', 'max' => 255],
			[
				['handle'],
				HandleValidator::class,
				'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']
			],
			[
				['name', 'handle'],
				UniqueValidator::class,
			],
		];
	}

	/**
	 * Returns the field context this element's content uses.
	 *
	 * @access protected
	 * @return string
	 */
	public function getFieldContext(): string
	{
		return 'sproutForms:' . $this->id;
	}

	/**
	 * Returns the name of the table this element's content is stored in.
	 *
	 * @return string
	 */
	public function getContentTable(): string
	{
		return SproutForms::$api->forms->getContentTableName($this);
	}
}