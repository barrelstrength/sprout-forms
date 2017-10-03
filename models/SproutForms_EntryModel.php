<?php
namespace Craft;

/**
 * Class SproutForms_EntryModel
 *
 * @package Craft
 *
 * @property    int                   $id
 * @property    int                   $formId
 * @property    string                $formName
 * @property    string                $ipAddress
 * @property    string                $userAgent
 * @property    SproutForms_FormModel $form            The related form model for this element model
 */
class SproutForms_EntryModel extends BaseElementModel
{
	/**
	 * @var string
	 */
	protected $elementType = 'SproutForms_Entry';

	/**
	 * @var array
	 */
	protected $formFields;

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array_merge(
			parent::defineAttributes(),
			array(
				'id'        => AttributeType::Number,
				'form'      => AttributeType::Mixed,
				'formId'    => AttributeType::Number,
				'statusId'  => AttributeType::Number,
				'formName'  => AttributeType::Number,
				'ipAddress' => AttributeType::String,
				'userAgent' => AttributeType::Mixed,
			)
		);
	}

	/**
	 * Returns the field layout used by this element
	 *
	 * @return FieldLayoutModel|null
	 */
	public function getFieldLayout()
	{
		return $this->getForm()->getFieldLayout();
	}

	/**
	 * Returns the name of the content table this entry is associated with
	 *
	 * @return string
	 */
	public function getContentTable()
	{
		return sproutForms()->forms->getContentTableName($this->getForm());
	}

	/**
	 * Returns the field context this element uses
	 *
	 * @return string
	 */
	public function getFieldContext()
	{
		return 'sproutForms:' . $this->formId;
	}

	/**
	 * Returns the fields associated with this form.
	 *
	 * @return array
	 */
	public function getFields()
	{
		return $this->getForm()->getFields();
	}

	/**
	 * Returns the content title for this entry
	 *
	 * @return mixed|string
	 */
	public function getTitle()
	{
		return $this->getContent()->title;
	}

	/**
	 * Returns the form model associated with this entry
	 *
	 * @return SproutForms_FormModel
	 */
	public function getForm()
	{
		if (!isset($this->form))
		{
			$this->form = sproutForms()->forms->getFormById($this->formId);
		}

		return $this->form;
	}

	/**
	 * @return false|string
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::getCpUrl('sproutforms/entries/edit/' . $this->id);
	}

	/**
	 * @inheritDoc BaseElementModel::getStatus()
	 *
	 * @return string|null
	 */
	public function getStatus()
	{
		$statusId = $this->statusId;

		$status = sproutForms()->entries->getEntryStatusById($statusId);

		return $status->color;
	}

	/**
	 * Returns an array of key/value pairs to send along in payload forwarding requests
	 *
	 * @return array
	 */
	public function getPayloadFields()
	{
		$fields = array();
		$ignore = array(
			'id',
			'slug',
			'title',
			'handle',
			'locale',
			'element',
			'elementId',
		);

		$content = $this->getContent()->getAttributes();

		foreach ($content as $field => $value)
		{
			if (!in_array($field, $ignore))
			{
				$fields[$field] = $value;
			}
		}

		return $fields;
	}
}
