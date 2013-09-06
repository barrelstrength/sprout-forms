<?php
namespace Craft;

/**
 * We extend the Craft FieldModel as we want to re-use as much as possible
 * 
 * @author zig
 *
 */
class SenorForm_FieldModel extends FieldModel
{  
	private $content = '';
	/**
	 * @access protected
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'id'           => AttributeType::Number,
			'formId'       => AttributeType::Number,
			'name'         => AttributeType::String,
			'handle'       => AttributeType::String,
			'instructions' => AttributeType::String,
			'type'         => AttributeType::String,
			'settings'	   => AttributeType::String,
			'translatable' => AttributeType::Bool,
			'validation'   => AttributeType::String,
		);
	}
}