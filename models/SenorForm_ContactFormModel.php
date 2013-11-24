<?php
namespace Craft;

class SenorForm_ContactFormModel extends BaseModel
{
    
    public function defineAttributes()
    {

        $model = array(
            'name'		=> AttributeType::String,
            'email'		=> AttributeType::String,
            'message'	=> AttributeType::String
        );

        return $model;

    }

    public function rules()
    {
        return array(
            array('name, email, message', 'required')
        );
    }
}