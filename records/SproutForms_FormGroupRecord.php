<?php
namespace Craft;

class SproutForms_FormGroupRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutforms_formgroups';
	}

	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'name' => array(AttributeType::Name, 'required' => true)
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'sproutforms_forms' => array(static::HAS_MANY, 'SproutForms_FormRecord', 'id'),
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('name'), 'unique' => true)
		);
	}

	/**
	 * @return array
	 */
	public function scopes()
	{
		return array(
			'ordered' => array('order' => 'name'),
		);
	}
}