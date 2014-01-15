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
            'redirectUri'   => array(AttributeType::String),
            'submitButtonType' => array(AttributeType::String),
            'submitButtonText' => array(AttributeType::String),
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
    			array('email_distribution_list', 'validateDistributionList')
    	);
    }
    
    /**
     * Custom validator for email distribution list
     * 
     * @param string $attribute
     * @return boolean
     */
    public function validateDistributionList($attribute)
    {
    	if( $emails = explode(',', $this->email_distribution_list))
    	{    		
    		foreach($emails as $email)
    		{
    			if( ! $email) continue;
    			if( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email))
    			{
    				$this->addError($attribute, 'Please make sure all emails are valid.');
    				return false;
    			}
    		}
    	}
    	return true;
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
