<?php
namespace Craft;

class SproutForms_ContactFormModel extends BaseModel
{
	/**
	 * Define attributes
	 * 
	 * @return array
	 */
	public function defineAttributes()
	{

		$model = array(
			'name'		=> AttributeType::String,
			'email'		=> AttributeType::String,
			'message'	=> AttributeType::String
		);

		return $model;

	}

	/**
	 * Define validation rules
	 * 
	 * @return array
	 */
	public function rules()
	{
		return array(
			array('name, email, message', 'required')
		);
	}
}