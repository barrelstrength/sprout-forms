<?php
namespace Craft;

/**
 * Section model class
 *
 * Used for transporting section data throughout the system.
 */
class SproutForms_FormGroupModel extends BaseModel
{
	/**
	 * Use the translated section name as the string representation.
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
		return array(
			'id'            => AttributeType::Number,
			'name'          => AttributeType::String,
		);
	}
}