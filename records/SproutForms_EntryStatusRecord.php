<?php
namespace Craft;

class SproutForms_EntryStatusRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutforms_entrystatuses';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'name'      => array(AttributeType::String, 'required' => true),
			'handle'    => array(AttributeType::Handle, 'required' => true),
			'color'     => array(AttributeType::Enum,
				'values'   => array('green', 'orange', 'red', 'blue',
													'yellow', 'pink', 'purple', 'turquoise',
													'light', 'grey', 'black'),
				'required' => true,
				'default'  => 'blue'
			),
			'sortOrder' => array(AttributeType::SortOrder),
			'isDefault' => array(AttributeType::Bool, 'default' => 0)
		);
	}
}