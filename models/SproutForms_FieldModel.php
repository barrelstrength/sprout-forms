<?php
namespace Craft;

class SproutForms_FieldModel extends FieldModel
{  
	private $content = '';
	
	/**
	 * Define attributes
	 * 
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