<?php
namespace Craft;

class SenorForm_FormRecord extends BaseRecord
{
	public $oldHandle = '';
	
    public function getTableName()
    {
        return 'senorform_forms';
    }

    public function defineAttributes()
    {
        return array(
            'name'          => array(AttributeType::String, 'required' => true),
            'handle'        => array(AttributeType::String, 'required' => true),
        	'email_distribution_list' => array(AttributeType::String),
        );
    }
    
    public function rules()
    {
    	return array(
    			array('name,handle', 'required'),
    			array('name,handle', 'unique', 'on' => 'insert'),
    	);
    }
    
    /**
     * @return array
     */
    public function defineRelations()
    {
    	return array(
    			'field' => array(static::HAS_MANY, 'SenorForm_FieldRecord', 'formId')
    	);
    }
}
