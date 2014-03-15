<?php
namespace Craft;

class SproutFormsVariable
{
    /**
     * Errors for public side validation
     * 
     * @var array
     */
    public static $errors;
    
    /**
     * Plugin Name
     * Make your plugin name available as a variable 
     * in your templates as {{ craft.YourPlugin.name }}
     * 
     * @return string
     */
    public function getName()
    {
        $plugin = craft()->plugins->getPlugin('sproutforms');
        return $plugin->getName();
    }
    
    /**
     * Get plugin version
     * 
     * @return string
     */
    public function getVersion()
    {
        $plugin = craft()->plugins->getPlugin('sproutforms');
        return $plugin->getVersion();
    }
    
    /**
     * Get a specific form. If no form is found, returns null
     *
     * @param  int   $id
     * @return mixed
     */
    public function getFormById($formId)
    {
        return craft()->sproutForms->getFormById($formId);
    }
    
    /**
     * Get a form field given field id
     * 
     * @param int $fieldId
     * @return obj
     */
    public function getFieldById($fieldId)
    {
        $fieldModel = craft()->sproutForms_field->getFieldById($fieldId);
        
        // Remove our namespace so the user can use their chosen handle
        $handle = craft()->sproutForms->adjustFieldName($fieldModel, 'human');
        
        if (isset($handle)) {
            $fieldModel->handle = $handle;
        }
        
        $available_validations  = craft()->sproutForms_field->getValidationOptions();
        $field_validations      = explode(',', $fieldModel->validation);
        $fieldModel->validation = $field_validations;
        
        // if all available validations are in current validation, we'll set 'all' selected
        if (array_intersect($available_validations, $field_validations) == $available_validations) {
            $fieldModel->validation = array();
        }
        
        return $fieldModel;
    }
    
    /**
     * Get a form given associated field id
     *
     * @param int $fieldId
     * @return obj
     */
    public function getFormByFieldId($params)
    {
        if (!isset($params['fieldId'])) {
            return null;
        }
        
        $form = craft()->sproutForms->getFormByFieldId($params['fieldId']);
        
        if (isset($params['idOnly']) && $params['idOnly'] == true) {
            return $form->id;
        }
        
        return $form;
    }
    
    /** 
     * Get form fields for specified form
     * 
     * @param int $formId
     * @return array
     */
    public function getFields($formId)
    {
        $fields = craft()->sproutForms->getFields($formId);
        
        foreach ($fields as $key => $value) {
            if ($handle = craft()->sproutForms->adjustFieldName($value, 'human')) {
                $fields[$key]['handle'] = $handle;
            }
        }
        
        return $fields;
    }
    
    /**
     * Return field entry for display in entries table
     * 
     * @param int $formId
     * @param int $field
     * @param obj $entry SproutForms_ContentEntry
     */
    public function getFieldEntry($formId, $field, $entry)
    {
        $field_name = "formId{$formId}_{$field->handle}";
        $res        = $entry->$field_name ? $entry->$field_name : '';
        $json       = json_decode($res);
        if ($json && !is_int($json)) {
            $options_data = array();
            foreach ($json as $option_label => $option_value) {
                // display label instead of value for readability
                if (isset($field->settings['options']) && is_array($field->settings['options'])) {
                    foreach ($field->settings['options'] as $option) {
                        if (isset($option['value']) && in_array($option['value'], $json)) {
                            $json[array_search($option['value'], $json)] = $option['value'];
                            // $json[array_search($option['value'], $json)] = $option['label']; // uncomment if you'd rather display the label
                        }
                    }
                }
            }
            return implode(',', $json);
        }
        
        // display label instead of value for readability
        if (isset($field->settings['options']) && is_array($field->settings['options'])) {
            foreach ($field->settings['options'] as $option) {
                if (isset($option['value']) && $option['value'] == $res) {
                    $res = $option['value'];
                    // $res = $option['label']; // uncomment if you'd rather display the label
                }
            }
        }
        return str_replace(' ', '&nbsp;', $res);
    }
    
    /**
     * Return fields for display
     * 
     * @param string $form_handle
     * @return array
     */
    public function getFormFields($form_handle)
    {
        craft()->path->setTemplatesPath(craft()->path->getCpTemplatesPath());
        
        $fields = array();
        
        if (!isset(self::$errors)) {
            self::$errors = craft()->user->getFlash('errors');
        }
        
        if ($formFields = craft()->sproutForms->getFieldsByFormHandle($form_handle)) {
            foreach ($formFields as $key => $fieldInfo) {
                // Remove our namespace so the user can use their chosen handle
                $handle = craft()->sproutForms->adjustFieldName($fieldInfo, 'human');
                
                $fields[$handle] = $this->_getFieldOutput($fieldInfo);
            }
        }
        
        $fields['errors'] = $this->_getErrors($formFields);
        
        craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());
        return $fields;
    }
    
    /**
     * Return individual field output
     * 
     * @param SproutForms_FieldRecord $fieldInfo
     * @return array
     */
    private function _getFieldOutput($fieldInfo)
    {
        // Remove our namespace so the user can use their chosen handle
        $handle = craft()->sproutForms->adjustFieldName($fieldInfo, 'human');
        
        // get the field type instance
        $fieldType = craft()->fields->getFieldType($fieldInfo->type);
        $fieldType->setSettings($fieldInfo->settings);
        
        require_once(dirname(__FILE__) . '/../etc/SproutForms_HtmlDisplay.php');
        $fieldModel = new SproutForms_HtmlDisplay();
        
        $fieldModel->input        = $fieldType->getInputHtml($handle, craft()->request->getPost($fieldInfo->handle));
        $fieldModel->handle       = $handle;
        $fieldModel->type         = strtolower($fieldInfo->type);
        $fieldModel->settings     = $fieldInfo->settings;
        $fieldModel->validation   = explode(',', $fieldInfo->validation);
        $fieldModel->required     = in_array('required', $fieldModel->validation) ? true : false;
        $fieldModel->instructions = $fieldInfo->instructions;
        $fieldModel->hint         = isset($fieldInfo->settings['hint']) ? $fieldInfo->settings['hint'] : '';
        $fieldModel->label        = $fieldInfo->name;
        $fieldModel->error        = isset(self::$errors[$handle]) && self::$errors[$handle] ? '<div class="field-error">' . implode('<br/>', self::$errors[$handle]) . '</div>' : '';
        
        // distinguish between input type="text" and textarea
        if ($fieldModel->type == 'plaintext') {
            if ($fieldType->getSettings()->multiline) // textfield
                {
                $fieldModel->type = 'textarea';
            }
        }
        
        return $fieldModel;
    }
    
    /**
     * Return array of errors
     * 
     * @param array $formFields
     * @return array
     */
    private function _getErrors($formFields)
    {
        $fieldErrors = array(
            'all' => array()
        );
        foreach ($formFields as $field) {
            if (isset(self::$errors[craft()->sproutForms->adjustFieldName($field, 'human')]) && self::$errors[craft()->sproutForms->adjustFieldName($field, 'human')]) {
                $fieldErrors[craft()->sproutForms->adjustFieldName($field, 'human')] = self::$errors[craft()->sproutForms->adjustFieldName($field, 'human')];
            } else {
                $fieldErrors[craft()->sproutForms->adjustFieldName($field, 'human')] = '';
            }
        }
        
        if (!empty($fieldErrors)) {
            foreach ($fieldErrors as $field => $errors) {
                if ($errors) {
                    foreach ($errors as $error) {
                        $fieldErrors['all'][] = $error;
                    }
                }
            }
        }
        
        return $fieldErrors;
    }
    
    public function getValidationOptions()
    {
        return craft()->sproutForms_field->getValidationOptions();
    }
    
    /**
     * Get all forms
     * 
     * @return array
     */
    public function getAllForms()
    {
        return craft()->sproutForms->getAllForms();
    }
    
    /**
     * Returns all entries for all forms
     * 
     * @param int form id
     * @return array
     */
    public function getAllEntries($formId)
    {
        return craft()->sproutForms->getEntries($formId);
    }
    
    /**
     * Get entry
     * 
     * @param int $id
     */
    public function getEntryById($id)
    {
        return craft()->sproutForms->getEntryById($id);
    }
    
    /**
     * Returns all installed fieldtypes.
     *
     * @return array
     */
    public function getAllFieldTypes()
    {
        $include    = array(
            'Checkboxes',
            'Dropdown',
            'MultiSelect',
            'PlainText',
            'RadioButtons'
        );
        $fieldTypes = craft()->fields->getAllFieldTypes();
        foreach ($fieldTypes as $k => $v) {
            if (!in_array($k, $include)) {
                unset($fieldTypes[$k]);
            }
        }
        return FieldTypeVariable::populateVariables($fieldTypes);
    }
    
    /**
     * Returns a complete form for display in template
     * 
     * @param string $form_handle
     * @return string
     */
    public function displayForm($form_handle, $customSettings = null)
    {
        if (!$formFields = $this->getFormFields($form_handle)) {
            return '';
        }
        
        craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/');
        
        $fields = array();
        
        foreach ($formFields as $key => $field) {
            if ($key == 'errors')
                continue;
            $fields[] = craft()->templates->render('_templates/field', array(
                'field' => $field
            ));
        }
        
        $form = craft()->templates->render('_templates/form', array(
            'form' => craft()->sproutForms->getFormByHandle($form_handle),
            'fields' => implode('', $fields),
            'customSettings' => $customSettings,
            'errors' => $formFields['errors']
        ));
        
        craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());
        
        echo $form;
    }
    
    /**
     * Returns a complete field for display in template
     *
     * @param string $form_handle
     * @return string
     */
    public function displayField($form_field_handle)
    {
        craft()->path->setTemplatesPath(craft()->path->getCpTemplatesPath());
        
        if (!isset(self::$errors)) {
            self::$errors = craft()->user->getFlash('errors');
        }
        
        list($form_handle, $field_handle) = explode('.', $form_field_handle);
        
        if (!$form_handle || !$field_handle)
            return '';
        if (!$field = craft()->sproutForms_field->getFieldByFormFieldHandle($form_handle, $field_handle))
            return '';
        
        $field = $this->_getFieldOutput($field);
        craft()->path->setTemplatesPath(craft()->path->getPluginsPath() . 'sproutforms/templates/');
        $fieldOutput = craft()->templates->render('_templates/field', array(
            'field' => $field
        ));
        
        craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());
        
        echo $fieldOutput;
    }
    
    /**
     * Helper function for debugging inside twig templates
     * 
     * @param mixed $msg
     * @return void
     */
    public function dump($msg)
    {
        Craft::dump($msg);
        die();
    }
}