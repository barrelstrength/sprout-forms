<?php
namespace Craft;

class SproutForms_FormModel extends BaseElementModel
{   
	protected $elementType = 'SproutForms_Form';

	private $_fields;

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
		return array_merge(parent::defineAttributes(), array(
			'id'                       => AttributeType::Number,
			'groupId'	                 => AttributeType::Number,
			'fieldLayoutId'            => AttributeType::Number,
			'name'                     => AttributeType::String,
			'handle'                   => AttributeType::String,
			'titleFormat'              => AttributeType::String,
			'displaySectionTitles'     => array(AttributeType::Bool, 'default' => false),
			'redirectUri'              => AttributeType::String,
			'submitAction'             => AttributeType::String,
			'submitButtonText'         => AttributeType::String,
			'notificationRecipients'   => AttributeType::String,
			'notificationSubject'      => AttributeType::String,
			'notificationSenderName'   => AttributeType::String,
			'notificationSenderEmail'  => AttributeType::String,
			'notificationReplyToEmail' => AttributeType::String,

			'ownerId'                  => AttributeType::Number,
			'oldHandle'                => AttributeType::String,
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
		$url = UrlHelper::getCpUrl('sproutforms/forms/edit/'. $this->id);

		return $url;
	}

	/**
	 * Returns the name of the table this element's content is stored in.
	 *
	 * @return string
	 */
	public function getContentTable()
	{
		return craft()->sproutForms_forms->getContentTableName($this);
	}

	/**
	 * Returns the field context this element's content uses.
	 *
	 * @access protected
	 * @return string
	 */
	public function getFieldContext()
	{
		return 'sproutForms:'.$this->id;
	}

	/**
	 * Returns the fields associated with this form.
	 *
	 * @return array
	 */
	public function getFields()
	{
		if (!isset($this->_fields))
		{
			$this->_fields = array();

			$fieldLayoutFields = $this->getFieldLayout()->getFields();

			foreach ($fieldLayoutFields as $fieldLayoutField)
			{
				$field = $fieldLayoutField->getField();
				$field->required = $fieldLayoutField->required;
				$this->_fields[] = $field;
			}
		}

		return $this->_fields;
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
