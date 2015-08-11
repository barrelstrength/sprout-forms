<?php
namespace Craft;

class SproutForms_FormRecord extends BaseRecord
{
	private $_oldHandle;

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
			'groupId' => AttributeType::Number,
			'name' => array(
				AttributeType::String,
				'required' => true
			),
			'handle' => array(
				AttributeType::String,
				'required' => true
			),
			'titleFormat' => array(
				AttributeType::String,
				'required' => true
			),
			'displaySectionTitles' => array(AttributeType::Bool, 'default' => false),
			'redirectUri' => AttributeType::String,
			'submitAction' => AttributeType::String,
			'submitButtonText' => AttributeType::String,
			'notificationEnabled' => array(AttributeType::Bool, 'default' => false),
			'notificationRecipients' => AttributeType::String,
			'notificationSubject' => AttributeType::String,
			'notificationSenderName' => AttributeType::String,
			'notificationSenderEmail' => AttributeType::String,
			'notificationReplyToEmail' => AttributeType::String,
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
				'unique',
				'on' => 'insert'
			),
			array(
				'notificationRecipients, notificationSenderEmail, notificationReplyToEmail',
				'validateRecipients'
			),
			array(
				'notificationRecipients, notificationSubject, notificationSenderName, notificationSenderEmail, notificationReplyToEmail',
				'validateEnabledNotification'
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

		// @todo - standardize how email validation is handled throughout plugins.
		// Versions of this appear in multiple places.
		if (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email))
		{
			$this->addError($attribute, Craft::t('Please make sure all emails are valid.'));
			return false;
		}
	}

	/**
	 * Store the old handle.
	 */
	public function storeOldHandle()
	{
		$this->_oldHandle = $this->handle;
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
	 * Create a secuencial string for the "name" and "handle" fields if they are already taken
	 * @param string
	 * @param string
	 * return string
	*/
	private function getFieldAsNew($field, $value)
	{
		$newField = null;
		$i = 1;
		$band = true;
		do
		{
			$newField = $value.$i;
			$form = sproutForms()->forms->getFieldValue($field, $newField);
			if ( is_null($form) )
			{
				$band = false;
			}

			$i++;
		} while ( $band );

		return $newField;
	}
	/**
	 * Before Validate
	 *
	 */
	protected function beforeValidate()
	{
		// Validate the name and handle fields when the record is save as new
		if (isset($_POST["saveAsNew"]))
		{
			if($_POST['saveAsNew'])
			{
				if( sproutForms()->forms->getFieldValue('name', $this->name) )
				{
					$this->name = $this->getFieldAsNew('name', $this->name);
				}

				if( sproutForms()->forms->getFieldValue('handle', $this->handle) )
				{
					$this->handle = $this->getFieldAsNew('handle', $this->handle);
				}
			}
		}

		return true;
	}
}