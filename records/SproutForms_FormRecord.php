<?php
namespace Craft;

class SproutForms_FormRecord extends BaseRecord
{
	private $_oldHandle;
	public $oldRecord;

	/**
	 * Init
	 */
	public function init()
	{
		parent::init();

		// Store the old handle in case it's ever requested.
		$this->attachEventHandler('onAfterFind', array($this, 'storeOldHandle'));
	}

	/**
	 * Return table name
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutforms_forms';
	}

	/**
	 * Define attributes
	 *
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'groupId'                  => AttributeType::Number,
			'name'                     => array(
				AttributeType::String,
				'required' => true
			),
			'handle'                   => array(
				AttributeType::String,
				'required' => true
			),
			'titleFormat'              => array(
				AttributeType::String,
				'required' => true
			),
			'displaySectionTitles'     => array(AttributeType::Bool, 'default' => false),
			'redirectUri'              => AttributeType::String,
			'submitAction'             => AttributeType::String,
			'submitButtonText'         => AttributeType::String,
			'saveData'                 => array(AttributeType::Bool, 'default' => false),
			'notificationEnabled'      => array(AttributeType::Bool, 'default' => false),
			'notificationRecipients'   => AttributeType::String,
			'notificationSubject'      => AttributeType::String,
			'notificationSenderName'   => AttributeType::String,
			'notificationSenderEmail'  => AttributeType::String,
			'notificationReplyToEmail' => AttributeType::String,
			'enableTemplateOverrides'  => array(AttributeType::Bool, 'default' => false),
			'templateOverridesFolder'  => array(AttributeType::String),
			'enableFileAttachments'    => array(AttributeType::Bool, 'default' => false),
		);
	}

	/**
	 * Define validation rules
	 *
	 * @return array
	 */
	public function rules()
	{
		return array(
			array(
				'name,handle',
				'required'
			),
			array(
				'name,handle',
				'unique'
			),
			array(
				'notificationRecipients, notificationSenderEmail, notificationReplyToEmail',
				'validateRecipients'
			),
			array(
				'notificationRecipients, notificationSubject, notificationSenderName, notificationSenderEmail, notificationReplyToEmail',
				'validateEnabledNotification'
			),
			array(
				'handle',
				'Craft\HandleValidator'
			),
		);
	}

	/**
	 * Define relationships
	 *
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'element'     => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
			'fieldLayout' => array(static::BELONGS_TO, 'FieldLayoutRecord', 'onDelete' => static::SET_NULL),
		);
	}

	/**
	 * Custom validator for email notifications
	 *
	 * @param string $attribute
	 *
	 * @return boolean
	 */
	public function validateEnabledNotification($attribute)
	{
		// If Notifications are enabled, make sure all Notification fields are set
		// @todo - update to provide specific validation for email fields and allow {objectSyntax}
		if ($this->notificationEnabled && ($this->{$attribute} == ""))
		{
			$this->addError($attribute, 'All notification fields are required when notifications are enabled.');

			return false;
		}
	}

	/**
	 * Custom validator for email distribution list
	 *
	 * @param string $attribute
	 *
	 * @return boolean
	 */
	public function validateRecipients($attribute)
	{
		if ($emails = explode(',', $this->{$attribute}))
		{
			foreach ($emails as $email)
			{
				if ($email)
				{
					$this->validateRecipient($attribute, $email);
				}
			}
		}

		return true;
	}

	/**
	 * Custom validator for email distribution list
	 *
	 * @param string $attribute
	 *
	 * @return boolean
	 */
	public function validateRecipient($attribute, $email)
	{
		$email = trim($email);

		// Allow twig syntax
		if (preg_match('/^{{?(.*?)}}?$/', $email))
		{
			return true;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$this->addError($attribute, Craft::t('Please make sure all emails are valid.'));

			return false;
		}

		return true;
	}

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

	/**
	 * Before Save
	 *
	 */
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
	}
}