<?php
namespace Craft;

class SproutForms_ContentRecord extends BaseRecord
{
	public $rules = array();
	
    public function getTableName()
    {
        return 'sproutforms_content';
    }
    
    public function defineAttributes()
    {
    	return array();
    }
    
    public function rules()
    {
    	return $this->rules;
    }

    /**
     * @return array
     */
    public function defineRelations()
    {
        return array(
           'form' => array(static::BELONGS_TO, 'SproutForms_FormRecord', 'required' => true)
        );
    }
    
    public function _setRules($fields = array())
    {
    	if( ! $this->formId)
    	{
    		return true;
    	}
    	
    	if( ! $field = SproutForms_FieldRecord::model()->findAll(array('condition' => 'formId=' . $this->formId)))
    	{
    		return true;
    	}
    	
    	$validation_rules = array();
    	foreach($field as $v)
    	{
    		if($v->validation)
    		{
    			if ( ! empty($fields) && ! key_exists($v->handle, $fields))
    			{
    				continue;
    			}
    			$validations = explode(',', $v->validation);
    			
    			foreach($validations as $v2)
    			{
    				$validation_rules[$v2][] = $v->handle;
    			}
    		}
    	}
    	
    	if( ! empty($validation_rules))
    	{
    		$c = 0;
    		foreach($validation_rules as $k=>$v)
    		{
    			$this->rules[$c][0] = implode(',', $v);
    			$this->rules[$c][1] = $k;
    			$c++;
    		}
    	}

    	return $this->rules;
    }
}
