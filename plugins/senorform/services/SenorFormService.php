<?php
namespace Craft;

class SenorFormService extends BaseApplicationComponent
{
	protected $formRecord;
	
	private $_formsByFieldId;

    public function __construct($formRecord = null)
    {
    	$this->formRecord = $formRecord;
        if (is_null($this->formRecord)) 
        {
            $this->formRecord = SenorForm_FormRecord::model();
        }
    }
    
    /**
     * Gets a field record by its ID or creates a new one.
     *
     * @access private
     * @param int $fieldId
     * @return FieldRecord
     */
    private function _getFormRecordById($formId = null)
    {
    	if ($formId)
    	{
    		$formRecord = SenorForm_FormRecord::model()->findById($formId);
    		$formRecord->scenario = 'update';
    		
    		if (!$formRecord)
    		{
    			throw new Exception(Craft::t('No form exists with the ID “{id}”', array('id' => $formId)));
    		}
    	}
    	else
    	{
    		$formRecord = new SenorForm_FormRecord();
    	}
    
    	return $formRecord;
    }

    /**
     * Get all Fallbacks from the database.
     *
     * @return array
     */
    public function getAllForms()
    {
        $query = craft()->db->createCommand()
                             ->select('id, name, handle')
                             ->from('senorform_forms')
                             ->order('name')
                             ->queryAll();    

        return SenorForm_FormModel::populateModels($query);
    }

    /**
     * Return form by form id
     * 
     * @param int $formId
     * @return object form record
     */
    public function getFormById($formId)
    {   
        $formRecord = SenorForm_FormRecord::model()->findById($formId);

        if ($formRecord)
        {
            return SenorForm_FormModel::populateModel($formRecord);
        }
        else
        {
            return null;
        }
    }

    /**
     * Return form by form handle
     *
     * @param string $handle
     * @return object form record
     */
    public function getFormByHandle($handle)
    {   
    	$formRecord = SenorForm_FormRecord::model()->find(
            'handle=:handle', 
            array(':handle' => $handle)
        );
    
    	if ($formRecord)
    	{
    		return SenorForm_FormModel::populateModel($formRecord);
    	}
    	else
    	{
    		return null;    
    	}
    }
    
    /**
     * Return form given associated field id
     *
     * @param int $fieldId
     * @return NULL|object
     */
    public function getFormByFieldId($fieldId)
    {
    	if (!isset($this->_formsById) || !array_key_exists($fieldId, $this->_formsById))
    	{
    		$formRecord = SenorForm_FormRecord::model()
				    	->with(array(
				    	'field' => array(
				    			'select' => false,
				    			'joinType' => 'INNER JOIN',
				    			'condition' => 'field.id=' . $fieldId
				    			)
				    	))->find();

    		if ($formRecord)
    		{
    			$form = SenorForm_FormModel::populateModel($formRecord);
    			$this->_formsByFieldId[$fieldId] = $form;
    		}
    		else
    		{
    			return null;
    		}
    	}
    
    	return $this->_formsByFieldId[$fieldId];
    }

	/**
	 * Return all form fields given a form id
	 * 
	 * @param int $formId
	 * @return object
	 */
    public function getFields($formId)
    {
		return SenorForm_FieldRecord::model()->findAll(array('condition' => 'formId=' . $formId));
    }
    
    /**
     * Return all form fields given a form handle
     *
     * @param string $handle
     * @return object
     */
    public function getFieldsByFormHandle($handle)
    {
    	$form = SenorForm_FormRecord::model()->findAll('handle=:handle', array(':handle' => $handle));

    	if(isset($form[0]->id))
    	{
    		return SenorForm_FieldRecord::model()->findAll(array('condition' => 'formId=' . $form[0]->id));
    	}
    	return null;
    }
    
    /**
     * Returns all entries for all forms
     * 
     * @return array
     */
    public function getEntries($formId)
    {
    	return SenorForm_ContentRecord::model()
	    		->with('form')
	    		->findAll(array(
                    'order' => 't.dateCreated desc',
	    			'condition' => 'formId=' . $formId
                ));
    }
    
    public function getEntryById($id)
    {
    	$res = SenorForm_ContentRecord::model()
    			->with('form', 'form.field')
    			->findByPk($id);

    	foreach($res->form->field as $key => $field)
    	{
    		$json = json_decode($res->{$field->handle});
    		if($json && ! is_int($json))
    		{
    			$options_data = array();
    			foreach($json as $option_label => $option_value)
    			{
    				$options_data[] = $option_label; //. ': ' . $option_value;
    			}
    			$res->form->field[$key]->setContent($options_data);
    		}
    		else 
    		{
    			$res->form->field[$key]->setContent($res->{$field->handle});
    		}
    	}
    	return $res;
    }
    
    /**
     * Delete from
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteForm($id)
    {
    	
    	if( ! $formRecord = SenorForm_FormRecord::model()->with('field')->findById($id))
    	{
    		return false;
    	}
    	
    	if(count($formRecord->field) > 0)
    	{
    		return false;
    	}
    
    	// Delete
    	$affectedRows = craft()->db->createCommand()->delete('senorform_forms', array('id' => $id));
    
    	return (bool) $affectedRows;
    }
    
    /**
     * Delete entry
     *
     * @param int $id
     * @return boolean
     */
    public function deleteContent($id)
    {
    	 
    	if( ! $contentRecord = SenorForm_ContentRecord::model()->findById($id))
    	{
    		return false;
    	}
    
    	// Delete
    	$affectedRows = craft()->db->createCommand()->delete('senorform_content', array('id' => $id));
    
    	return (bool) $affectedRows;
    }
    
    /**
     * Saves a form.
     *
     * @param SenorForm_FormModel $form
     * @throws \Exception
     * @return bool
     */
    public function saveForm(SenorForm_FormModel $form)
    {
    	$formRecord = $this->_getFormRecordById($form->id);
    	$isNew = $formRecord->isNewRecord();
    
    	if (!$isNew)
    	{
    		$formRecord->oldHandle = $formRecord->handle;
    	}

    	$formRecord->name         = $form->name;
    	$formRecord->handle       = $form->handle;
    	if(isset($form->email_distribution_list))
    	{
    		$formRecord->email_distribution_list = $form->email_distribution_list;
    	}

    	if ($formRecord->validate())
    	{
    		$transaction = craft()->db->beginTransaction();
    		try
    		{
    			$formRecord->save(false);
    
    			// Now that we have a field ID, save it on the model
    			if (!$formRecord->id)
    			{
    				$form->id = $formRecord->id;
    			}
    
    			$transaction->commit();
    		}
    		catch (\Exception $e)
    		{
    			$transaction->rollBack();
    			throw $e;
    		}
    
    		return true;
    	}
    	else
    	{    		
    		$form->addErrors($formRecord->getErrors());
    		return false;
    	}
    }

    /**
     * Append or Strip the "formId#_" off of the field
     * name so we can maintain human readable field names on the front
     * end and allow it to appear as if there are multiple fields of the 
     * same name
     * 
     * @param  model $value Field model
     * @param  string $conversion 'human' or 'db'
     *         human will make the value human readable
     *         db will prepare the value for the database
     * @return string       Adjusted handle - human readable
     */
    public function adjustFieldName($fieldModel, $target = 'human')
    {
        // The namespace pattern for our field names
        $pattern = '/^formId\d+_/';

        if ($target == 'human')
        {
            // Remove our namespace so the user can use their chosen handle
            $handleRaw = $fieldModel->getAttribute('handle');
            $handle = preg_split($pattern, $handleRaw);

            return $handle[1];  
        }
        else
        {

        }
        

        
    }
    
}