<?php
namespace barrelstrength\sproutforms\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use yii\base\ErrorHandler;
use craft\db\Query;
use craft\helpers\UrlHelper;
use yii\base\InvalidConfigException;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;

use craft\behaviors\FieldLayoutBehavior;
use craft\behaviors\FieldLayoutTrait;

use barrelstrength\sproutforms\elements\db\FormQuery;
use barrelstrength\sproutforms\records\Form as FormRecord;
use barrelstrength\sproutforms\SproutForms;

/**
 * Form represents a form element.
 */
class Form extends Element
{
	use FieldLayoutTrait;

	// Properties
	// =========================================================================
	/**
	 * @var int|null Group ID
	 */
	public $name;

	/**
	 * @var int|null New parent ID
	 */
	public $handle;

	public $oldHandle;
	public $saveAsNew;
	public $fieldLayoutId;
	public $titleFormat;
	public $displaySectionTitles;
	public $redirectUri;
	public $submitAction;
	public $submitButtonText;
	public $savePayload;
	public $notificationEnabled;
	public $notificationRecipients;
	public $notificationSubject;
	public $notificationSenderName;
	public $notificationSenderEmail;
	public $notificationReplyToEmail;
	public $enableTemplateOverrides;
	public $templateOverridesFolder;
	public $enableFileAttachments;


	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return [
			'fieldLayout' => [
				'class' => FieldLayoutBehavior::class,
				'elementType' => self::class
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

	/**
	 * @inheritdoc
	 */
	public static function displayName(): string
	{
		return Craft::t('app', 'Sprout Forms');
	}

	/**
	 * @inheritdoc
	 */
	public static function refHandle()
	{
		return 'form';
	}

	/**
	 * @inheritdoc
	 */
	public static function hasContent(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public static function hasTitles(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public static function isLocalized(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::cpUrl(
			'sprout-forms/forms/edit/'.$this->id
		);
	}

	/**
	 * Use the name as the string representation.
	 *
	 * @return string
	 */
	/** @noinspection PhpInconsistentReturnPointsInspection */
	public function __toString()
	{
		try {
			return $this->name;
		} catch (\Exception $e) {
			ErrorHandler::convertExceptionToError($e);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getFieldLayout()
	{
		$behaviors   = $this->getBehaviors();
		$fieldLayout = $behaviors['fieldLayout'];

		return $fieldLayout->getFieldLayout();
	}

	/**
	 * @inheritdoc
	 *
	 * @return FormQuery The newly created [[FormQuery]] instance.
	 */
	public static function find(): ElementQueryInterface
	{
		return new FormQuery(get_called_class());
	}

	// Properties
	// =========================================================================

	/**
	 * @var int|null Group ID
	 */
	public $groupId;

	/**
	 * @inheritdoc
	 */
	protected static function defineSources(string $context = null): array
	{
		$sources = [
			[
			'key'   => '*',
			'label' => SproutForms::t('All Forms'),
			]
		];

		$groups = SproutForms::$api->groups->getAllFormGroups();

		foreach ($groups as $group)
		{
			$key = 'group:' . $group->id;

			$sources[$key] = [
				'label'    => SproutForms::t($group->name),
				'data'     => ['id' => $group->id],
				'criteria' => ['groupId' => $group->id]
			];
		}

		return $sources;
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineSearchableAttributes(): array
	{
		return ['name', 'handle'];
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineSortOptions(): array
	{
		$attributes = [
			'name' => Craft::t('app', 'Form Name'),
			'elements.dateCreated' => Craft::t('app', 'Date Created'),
			'elements.dateUpdated' => Craft::t('app', 'Date Updated'),
		];

		return $attributes;
	}

	/**
	 * @inheritdoc
	 */
	protected static function defineTableAttributes(): array
	{
		$attributes['name']           = ['label' => Craft::t('app', 'Name')];
		$attributes['handle']         = ['label' => Craft::t('app', 'Handle')];
		$attributes['numberOfFields'] = ['label' => Craft::t('app', 'Number of Fields')];
		$attributes['totalEntries']   = ['label' => Craft::t('app', 'Total Entries')];

		return $attributes;
	}

	protected static function defineDefaultTableAttributes(string $source): array
	{
		$attributes = ['name', 'handle', 'numberOfFields', 'totalEntries'];

		return $attributes;
	}

	/**
	 * @inheritdoc
	 */
	protected function tableAttributeHtml(string $attribute): string
	{
		switch ($attribute) {

			case 'handle':
			{
				return '<code>' . $this->handle . '</code>';
			}
			case 'numberOfFields':
			{
				$totalFields = (new Query())
					->select('COUNT(*)')
					->from('{{%fieldlayoutfields}}')
					->where(['layoutId' => $this->fieldLayoutId])
					->scalar();

				return $totalFields;
			}
			case 'totalEntries':
			{
				$totalEntries = (new Query())
					->select('COUNT(*)')
					->from('{{%sproutforms_entries}}')
					->where(['formId' => $this->id])
					->scalar();

				return $totalEntries;
			}
		}

		return parent::tableAttributeHtml($attribute);
	}

	/**
	 * @inheritdoc
	 */
	public function getEditorHtml(): string
	{
		$html = '';

		if ($this->getType()->hasTitleField)
		{
			$html = Craft::$app->getView()->renderTemplate('_cp/fields/titlefield',
				[
					'entry' => $this
				]
			);
		}

		$html .= parent::getEditorHtml();

		return $html;
	}

	/**
	 * @inheritdoc
	 * @throws Exception if reasons
	 */
	public function afterSave(bool $isNew)
	{
		// Get the tag record
		if (!$isNew)
		{
			$record = FormRecord::findOne($this->id);

			if (!$record)
			{
				throw new Exception('Invalid Form ID: '.$this->id);
			}
		} else
		{
			$record = new FormRecord();
			$record->id = $this->id;
		}

		$record->fieldLayoutId            = $this->fieldLayoutId;
		$record->name                     = $this->name;
		$record->handle                   = $this->handle;
		$record->titleFormat              = $this->titleFormat;
		$record->displaySectionTitles     = $this->displaySectionTitles;
		$record->groupId                  = $this->groupId;
		$record->redirectUri              = $this->redirectUri;
		$record->submitAction             = $this->submitAction;
		$record->savePayload              = $this->savePayload;
		$record->submitButtonText         = $this->submitButtonText;
		$record->notificationEnabled      = $this->notificationEnabled;
		$record->notificationRecipients   = $this->notificationRecipients;
		$record->notificationSubject      = $this->notificationSubject;
		$record->notificationSenderName   = $this->notificationSenderName;
		$record->notificationSenderEmail  = $this->notificationSenderEmail;
		$record->notificationReplyToEmail = $this->notificationReplyToEmail;
		$record->enableTemplateOverrides  = $this->enableTemplateOverrides;
		$record->templateOverridesFolder  = $this->templateOverridesFolder;
		$record->enableFileAttachments    = $this->enableFileAttachments;

		$record->save(false);

		parent::afterSave($isNew);
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name', 'handle'], 'required'],
			[['name', 'handle'], 'string', 'max' => 10],
			[
				['handle'],
				HandleValidator::class,
				'reservedWords' => ['id', 'dateCreated', 'dateUpdated', 'uid', 'title']
			],
			[['name', 'handle'], UniqueValidator::class, 'targetClass' => FormRecord::class],
		];
		/*$rules = parent::rules();
		$rules[] = [['fieldLayoutId'], 'number', 'integerOnly' => true];
		$rules[] = [['handle', 'name', 'titleFormat', 'numberOfFields', 'totalEntries'], 'safe'];

		return $rules;*/
	}
}