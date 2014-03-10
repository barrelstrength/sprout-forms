<?php
namespace Craft;

class SproutForms_FormModel extends BaseElementModel
{
	protected $elementType = 'SproutForms';
	
	/**
	 * Define attributes
	 * 
	 * @return array
	 */
	public function defineAttributes()
	{
		$model = array(
			'id'                      => AttributeType::Number,
			'name'                    => AttributeType::String,
			'handle'                  => AttributeType::String,
			'redirectUri'             => AttributeType::String,
			'submitButtonType'        => AttributeType::String,
			'submitButtonText'        => AttributeType::String,
			'email_distribution_list' => AttributeType::String,
			'notification_reply_to'   => AttributeType::String,
			'notification_subject'    => AttributeType::String,
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
			array('name, handle', 'required', 'on' => 'insert')
		);
	}
}