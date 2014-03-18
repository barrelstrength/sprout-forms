<?php
namespace Craft;

class SproutForms_FieldsController extends BaseController
{
    /**
     * Saves a field.
     * 
     * @return void
     */
    public function actionSaveField()
    {
        $this->requirePostRequest();
        
        $field               = new SproutForms_FieldModel();
        $field->formId       = craft()->request->getRequiredPost('formId');
        $field->id           = craft()->request->getPost('fieldId');
        $field->name         = craft()->request->getPost('name');
        $field->handle       = craft()->request->getPost('handle');
        $field->instructions = craft()->request->getPost('instructions');
        $field->translatable = (bool) craft()->request->getPost('translatable');
        $field->type         = craft()->request->getRequiredPost('type');
        
        $typeSettings = craft()->request->getPost('types');
        if (isset($typeSettings[$field->type]))
        {
            $field->settings = $typeSettings[$field->type];
        }
        
        $field->validation = ''; // reset
        if ($validation = craft()->request->getPost('validation'))
        {
            if ($validation == '*')
            {
                $field->validation = implode(',', craft()->sproutForms_field->getValidationOptions());
            }
            else
            {
                $field->validation = implode(',', $validation);
            }
        }
        
        if (craft()->sproutForms_field->saveField($field))
        {
            craft()->userSession->setNotice(Craft::t('Field saved.'));
            
            $this->redirectToPostedUrl(array(
                'fieldId' => $field->id,
                'formId' => $field->formId
            ));
        }
        else
        {
            craft()->userSession->setError(Craft::t('Couldn’t save field.'));
        }
        
        // Send the field back to the template
        craft()->urlManager->setRouteVariables(array(
            'field' => $field
        ));
    }
    
    /**
     * Deletes a field.
     * 
     * @return void
     */
    public function actionDeleteField()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();
        
        $fieldId = craft()->request->getRequiredPost('id');
        $success = craft()->sproutForms_field->deleteField($fieldId);
        $this->returnJson(array(
            'success' => $success
        ));
    }
    
    public function actionReorderFields()
    {
        craft()->userSession->requireAdmin();
        $this->requirePostRequest();
        $this->requireAjaxRequest();
        
        $fieldIds = JsonHelper::decode(craft()->request->getRequiredPost('ids'));
        craft()->sproutForms_field->reorderFields($fieldIds);
        
        $this->returnJson(array(
            'success' => true
        ));
    }
}