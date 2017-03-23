<?php
namespace Craft;

class SproutForms_FormModel extends BaseElementModel
{
	protected $elementType = 'SproutForms_Form';

	private $_fields;

	public $totalEntries;

	public $numberOfFields;

	public $saveAsNew;

	/**
	 * Use the form handle as the string representation.
	 *
	 * @return string
	 */
	function __toString()
	{
		return Craft::t($this->name);
	}

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		$settings = craft()->plugins->getPlugin('sproutforms')->getSettings();

		$templateOverridesFolder = $settings['templateFolderOverride'];
		$enableTemplateOverrides = !empty($templateOverridesFolder);

		return array_merge(parent::defineAttributes(), array(
			'id'                       => AttributeType::Number,
			'groupId'                  => AttributeType::Number,
			'fieldLayoutId'            => AttributeType::Number,
			'name'                     => AttributeType::String,
			'handle'                   => AttributeType::String,
			'titleFormat'              => AttributeType::String,
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
			'ownerId'                  => AttributeType::Number,
			'oldHandle'                => AttributeType::String,
			'enableTemplateOverrides'  => array(AttributeType::Bool, 'default' => $enableTemplateOverrides),
			'templateOverridesFolder'  => array(AttributeType::String, 'default' => $templateOverridesFolder),
			'enableFileAttachments'    => array(AttributeType::Bool, 'default' => false),
		));
	}

	/**
	 * @return array
	 */
	public function behaviors()
	{
		return array(
			'fieldLayout' => new FieldLayoutBehavior($this->elementType),
		);
	}

	/**
	 * Returns the field layout used by this element.
	 *
	 * @return FieldLayoutModel|null
	 */
	public function getFieldLayout()
	{
		return $this->asa('fieldLayout')->getFieldLayout();
	}

	/**
	 * Returns the element's CP edit URL.
	 *
	 * @return string|false
	 */
	public function getCpEditUrl()
	{
		$url = UrlHelper::getCpUrl('sproutforms/forms/edit/' . $this->id);

		return $url;
	}

	/**
	 * Returns the name of the table this element's content is stored in.
	 *
	 * @return string
	 */
	public function getContentTable()
	{
		return sproutForms()->forms->getContentTableName($this);
	}

	/**
	 * Returns the field context this element's content uses.
	 *
	 * @access protected
	 * @return string
	 */
	public function getFieldContext()
	{
		return 'sproutForms:' . $this->id;
	}

	/**
	 * Returns the fields associated with this form.
	 *
	 * @return array
	 */
	public function getFields()
	{
		if (is_null($this->_fields))
		{
			$this->_fields = array();

			$fieldLayoutFields = $this->getFieldLayout()->getFields();

			foreach ($fieldLayoutFields as $fieldLayoutField)
			{
				$field = $fieldLayoutField->getField();

				$field->setAttribute('required', $fieldLayoutField->required);

				$this->_fields[$field->handle] = $field;
			}
		}

		return $this->_fields;
	}

	/**
	 * @param string $handle
	 *
	 * @return null|FieldModel
	 */
	public function getField($handle)
	{
		$fields = $this->getFields();

		if (is_string($handle) && !empty($handle))
		{
			return isset($fields[$handle]) ? $fields[$handle] : null;
		}
	}

	/**
	 * Sets the fields associated with this form.
	 *
	 * @param array $fields
	 */
	public function setFields($fields)
	{
		$this->_fields = $fields;
	}

	/**
	 * Returns whether this is a new component.
	 *
	 * @return bool
	 */
	public function isNew()
	{
		return (!$this->id || strncmp($this->id, 'new', 3) === 0);
	}
}
