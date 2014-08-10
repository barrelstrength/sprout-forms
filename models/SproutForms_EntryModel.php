<?php
namespace Craft;

class SproutForms_EntryModel extends BaseElementModel
{
	protected $elementType = 'SproutForms_Entry';

	/**
	 * Use the element's title as its string representation.
	 *
	 * @return string
	 */
	function __toString()
	{
		// @TODO - this works but for some reason, removing __toString
		// which calls $this->getContent->title does not work.
		$entry = craft()->sproutForms_entries->getEntryById($this->id);
		return $entry->getContent()->title;
	}

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(
				
			// @todo - standardize how IDs are handled on front and back end
			'id'        => AttributeType::Number,
			'entryId'   => AttributeType::Number,

			'formId'    => AttributeType::Number,
			'formName'  => AttributeType::Number,
			'ipAddress' => AttributeType::String,
			'userAgent' => AttributeType::Mixed,
		));
	}

	/*
	 * Returns the field layout used by this element.
	 *
	 * @return FieldLayoutModel|null
	 */
	public function getFieldLayout()
	{
		$form = craft()->sproutForms_forms->getFormById($this->formId);
		return $form->getFieldLayout();
	}

	/**
	 * Returns the name of the table this element's content is stored in.
	 *
	 * @return string
	 */
	public function getContentTable()
	{
		$form = craft()->sproutForms_forms->getFormById($this->formId);
		return craft()->sproutForms_forms->getContentTableName($form);
	}

	/**
	 * Returns the field context this element's content uses.
	 *
	 * @access protected
	 * @return string
	 */
	public function getFieldContext()
	{
		return 'sproutForms:'.$this->formId;
	}

	/**
	 * Returns the fields associated with this form.
	 *
	 * @return array
	 */
	public function getFields()
	{
		$form = craft()->sproutForms_forms->getFormById($this->formId);

		if (!isset($form->_fields))
		{
			$form->_fields = array();

			$fieldLayoutFields = $form->getFieldLayout()->getFields();

			foreach ($fieldLayoutFields as $fieldLayoutField)
			{
				$field = $fieldLayoutField->getField();
				$field->required = $fieldLayoutField->required;
				$form->_fields[] = $field;
			}
		}

		return $form->_fields;
	}

	/**
	 * Returns the element's CP edit URL.
	 *
	 * @return string|false
	 */
	public function getCpEditUrl()
	{
		$url = UrlHelper::getCpUrl('sproutforms/entries/edit/'. $this->id);

		return $url;
	}
}