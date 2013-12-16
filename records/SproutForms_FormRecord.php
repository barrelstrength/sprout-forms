<?php
namespace Craft;

class SproutForms_FormRecord extends BaseRecord
{
	public $oldHandle = '';
	
	/**
	 * Return table name
	 *
	 * @return string
	 */
    public function getTableName()
    {
        return 'sproutforms_forms';
    }

    /**
     * Define attributes
     *
     * @return array
     */
    public function defineAttributes()
    {
        return array(
            'name'          => array(AttributeType::String, 'required' => true),
            'handle'        => array(AttributeType::String, 'required' => true),
        	'email_distribution_list' => array(AttributeType::String),
        );
    }
    
    /**
     * Define validation rules
     *
     * @return array
     */
    public function rules()
    {
    	return array(
    			array('name,handle', 'required'),
    			array('name,handle', 'unique', 'on' => 'insert'),
    	);
    }
    
    /**
     * Define relationships
     * 
     * @return array
     */
    public function defineRelations()
    {
    	return array(
    			'field' => array(static::HAS_MANY, 'SproutForms_FieldRecord', 'formId')
    	);
    }
}
