<?php
namespace Craft;

class SproutForms_SettingsController extends BaseController
{

	public function actionSaveForm() {
        
        $this->requirePostRequest();

        $form = new SproutForms_FormModel;
        
        // Set our variables
        $form->name     = craft()->request->getPost('formName');
        $form->handle   = craft()->request->getPost('formHandle');

        // If we have an id, update the existing form, otherwise, lets create a new form
        if ($form->id)
        {
            // $entryRecord = EntryRecord::model()->with('element', 'entryTagEntries')->findById($entry->id);

            // if (!$entryRecord)
            // {
            //     throw new Exception(Craft::t('No entry exists with the ID “{id}”', array('id' => $entry->id)));
            // }

            // $elementRecord = $entryRecord->element;
        }
        else
        {
            $formRecord = new SproutForms_FormRecord();

            // @TODO - Support Form as an ElementType
            // $elementRecord = new ElementRecord();
            // $elementRecord->type = ElementType::Form;
        }

        $formRecord->name       = $form->name;
        $formRecord->handle     = $form->handle;
        
        $formRecord->validate();
        $form->addErrors($formRecord->getErrors());


        if (!$form->hasErrors())
        {               
            craft()->db->createCommand()
                ->insert('sproutforms_forms', $form->getAttributes());

        	craft()->userSession->setNotice(Craft::t('Form settings saved.'));
   			$this->redirectToPostedUrl();
        }
        else
        {
            craft()->userSession->setError(Craft::t('Couldn’t save form settings.'));
        	craft()->urlManager->setRouteVariables(array(
                'form' => $form
            ));
        }
		
        // SproutForms_FormModel::populateModel($form);

        // $form['name'] = craft()->request->getPost('formName');
        // $form['handle'] = craft()->request->getPost('formHandle');

        

        // craft()->db->createCommand()->insert(
        //     'sproutforms_forms',
        //     array(
        //         'name' 		=> craft()->request->getPost('formName'), 
        //         'handle'	=> craft()->request->getPost('formHandle')
        //     ),
        //     'name=:name',
        //     array(
        //         ':name'=>'MoreInfo'
        //     )
        // );

    }

}