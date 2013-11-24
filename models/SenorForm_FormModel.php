<?php
namespace Craft;

class SenorForm_FormModel extends BaseModel
{
    public function defineAttributes()
    {
        $model = array(
            'id'            => AttributeType::Number,
            'name'          => AttributeType::String,
            'handle'        => AttributeType::String,
        	'email_distribution_list' => AttributeType::String
        );

        return $model;
    }

    public function rules()
    {
        return array(
            array('name, handle', 'required', 'on' => 'insert')
        );
    }
}