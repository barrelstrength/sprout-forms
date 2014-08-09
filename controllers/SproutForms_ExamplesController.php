<?php
namespace Craft;

class SproutForms_ExamplesController extends BaseController
{
	/**
	 * Install examples
	 * 
	 * @return void
	 */
	public function actionInstall()
	{
		$this->_installExampleTemplates();
		$this->_installExampleData();
		
		craft()->userSession->setNotice(Craft::t('Examples successfully installed.'));
		$this->redirect('sproutforms');
	}
	
	/**
	 * Install templates
	 * 
	 * @return void
	 */
	private function _installExampleTemplates()
	{
		try
		{
			$fileHelper = new \CFileHelper();
			@mkdir(craft()->path->getSiteTemplatesPath() . 'sproutforms');
			$fileHelper->copyDirectory(craft()->path->getPluginsPath() . 'sproutforms/templates/_special/examples/templates', craft()->path->getSiteTemplatesPath() . 'sproutforms');
		}
		catch (\Exception $e)
		{
			$this->_handleError($e);
		}
	}
	
	/**
	 * Install data
	 * 
	 * @return void
	 */
	private function _installExampleData()
	{
		try
		{
			// $sql = file_get_contents(craft()->path->getPluginsPath() . 'sproutforms/_special/examples/data.sql');
			// craft()->db->createCommand($sql)->execute();
			
			// Create Forms and their Content Tables
			$contactForm = new SproutForms_FormModel();

			// Shared attributes
			$contactForm->name       = 'Contact Form';
			$contactForm->handle     = 'contact';
			$contactForm->titleFormat = "{dateCreated|date('Ymd')}";

			craft()->sproutForms_forms->saveForm($contactForm);

			$fullNameField = new FieldModel();
			$fullNameField->name = 'Full Name';
			$fullNameField->handle = 'fullName';
			// $field->instructions = craft()->request->getPost('instructions');
			// $field->required     = craft()->request->getPost('required');

			$fullNameField->type = 'PlainText';
			$fullNameField->settings = array(
				'placeholder' => '',
				'maxLength' => '',
				'multiline' => '',
				'initialRows' => 4,
			);

			$messageField = new FieldModel();
			$messageField->name = 'Message';
			$messageField->handle = 'message';
			$messageField->type = 'PlainText';
			$messageField->settings = array(
				'placeholder' => '',
				'maxLength' => '',
				'multiline' => 1,
				'initialRows' => 4,
			);

			craft()->sproutForms_fields->saveField($contactForm, $fullNameField);
			craft()->sproutForms_fields->saveField($contactForm, $messageField);

			// ------------------------------------------------------------
			
			$allFieldsForm = new SproutForms_FormModel();

			// Shared attributes
			$allFieldsForm->name       = 'Example Form with all Simple Fields';
			$allFieldsForm->handle     = 'formWithAllFields';
			$allFieldsForm->titleFormat = "{dateCreated|date('Ymd')}";

			craft()->sproutForms_forms->saveForm($allFieldsForm);

		}
		catch (\Exception $e)
		{
			$this->_handleError($e);
		}
	}
	
	/**
	 * Handle installation errors
	 * 
	 * @param Exception $exception
	 * @return void
	 */
	private function _handleError($exception)
	{
		craft()->userSession->setError(Craft::t('Unable to install the examples.'));
			$this->redirect('sproutforms/examples');
	}
}