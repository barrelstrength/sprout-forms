<?php
namespace Craft;

class SproutForms_EntryModel extends BaseElementModel
{
	protected $elementType = 'SproutForms_Entry';

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(

			// @TODO - standardize how IDs are handled on front and back end
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
		return $this->_getForm()->getFieldLayout();
	}

	/**
	 * Returns the name of the table this element's content is stored in.
	 *
	 * @return string
	 */
	public function getContentTable()
	{
		return craft()->sproutForms_forms->getContentTableName($this->_getForm());
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
		return UrlHelper::getCpUrl('sproutforms/entries/edit/'. $this->id);
	}

	/**
	 * Gets Form Model associated with this entry
	 * 
	 * @return SproutForms_FormModel
	 */
	public function _getForm()
	{
		return craft()->sproutForms_forms->getFormById($this->formId);
	}
}