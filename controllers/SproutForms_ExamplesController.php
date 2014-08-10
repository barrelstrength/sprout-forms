<?php
namespace Craft;

class SproutForms_ExamplesController extends BaseController
{
	private $_formId;

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
	 * Install example data
	 * 
	 * @return void
	 */
	private function _installExampleData()
	{
		try
		{
			// Create Example Forms
			// ------------------------------------------------------------
			
			$formSettings = array(
				array(
					'name' => 'Contact Form',
					'handle' => 'contact',
					'titleFormat' => "{dateCreated|date('Y-m-d')} – {fullName} – {message|slice(0,22)}..."
				),
				array(
					'name' => 'Example Form with all Simple Fields',
					'handle' => 'formWithAllFields',
					'titleFormat' => "{dateCreated|date('Y-m-d')}"
				)
			);

			$fieldSettings = array(
				'contact' => array(
					array(
						'name'     => 'Full Name',
						'handle'   => 'fullName',
						'type'     => 'PlainText',
						'required' => 1,
						'settings' => array(
							'placeholder' => '',
							'maxLength' => '',
							'multiline' => '',
							'initialRows' => 4,
						)
					),
					array(
						'name'     => 'Email',
						'handle'   => 'email',
						'type'     => 'PlainText',
						'required' => 1,
						'settings' => array(
							'placeholder' => '',
							'maxLength' => '',
							'multiline' => '',
							'initialRows' => 4,
						)
					),
					array(
						'name'     => 'Message',
						'handle'   => 'message',
						'type'     => 'PlainText',
						'required' => 1,
						'settings' => array(
							'placeholder' => '',
							'maxLength' => '',
							'multiline' => 1,
							'initialRows' => 4,
						)
					),
				),
				'formWithAllFields' => array(
					array(
						'name'     => 'Plain Text Field',
						'handle'   => 'plainText',
						'type'     => 'PlainText',
						'required' => 1,
						'settings' => array(
							'placeholder' => '',
							'maxLength' => '',
							'multiline' => 0,
							'initialRows' => 4,
						)
					),
					array(
						'name'     => 'Dropdown Field',
						'handle'   => 'dropdown',
						'type'     => 'Dropdown',
						'required' => 1,
						'settings' => array(
							'options' => array(
								array(
									'label' => 'Option 1',
									'value' => 'option1',
									'default' => ''
								),
								array(
									'label' => 'Option 2',
									'value' => 'option2',
									'default' => ''
								),
								array(
									'label' => 'Option 3',
									'value' => 'option3',
									'default' => ''
								)
							)
						)
					),
					array(
						'name'     => 'Number Field',
						'handle'   => 'number',
						'type'     => 'Number',
						'required' => 0,
						'settings' => array(
							'min' => 0,
							'max' => '',
							'decimals' => ''
						)
					),
					array(
						'name'     => 'Radio Buttons Field',
						'handle'   => 'radioButtons',
						'type'     => 'RadioButtons',
						'required' => 0,
						'settings' => array(
							'options' => array(
								array(
									'label' => 'Option 1',
									'value' => 'option1',
									'default' => ''
								),
								array(
									'label' => 'Option 2',
									'value' => 'option2',
									'default' => ''
								),
								array(
									'label' => 'Option 3',
									'value' => 'option3',
									'default' => ''
								)
							)
						)
					),
					array(
						'name'     => 'Checkboxes Field',
						'handle'   => 'checkboxes',
						'type'     => 'Checkboxes',
						'required' => 0,
						'settings' => array(
							'options' => array(
								array(
									'label' => 'Option 1',
									'value' => 'option1',
									'default' => ''
								),
								array(
									'label' => 'Option 2',
									'value' => 'option2',
									'default' => ''
								),
								array(
									'label' => 'Option 3',
									'value' => 'option3',
									'default' => ''
								)
							)
						)
					),
					array(
						'name'     => 'Multi-select Field',
						'handle'   => 'multiSelect',
						'type'     => 'MultiSelect',
						'required' => 0,
						'settings' => array(
							'options' => array(
								array(
									'label' => 'Option 1',
									'value' => 'option1',
									'default' => ''
								),
								array(
									'label' => 'Option 2',
									'value' => 'option2',
									'default' => ''
								),
								array(
									'label' => 'Option 3',
									'value' => 'option3',
									'default' => ''
								)
							)
						)
					),

					array(
						'name'     => 'Textarea Field',
						'handle'   => 'textarea',
						'type'     => 'PlainText',
						'required' => 0,
						'settings' => array(
							'placeholder' => '',
							'maxLength' => '',
							'multiline' => 1,
							'initialRows' => 4,
						)
					),
				),
			);

			// Create Forms and their Content Tables
			foreach ($formSettings as $settings) 
			{
				$form = new SproutForms_FormModel();
				
				// Assign our form settings
				$form->name        = $settings['name'];
				$form->handle      = $settings['handle'];
				$form->titleFormat = $settings['titleFormat'];

				// Create the Form
				craft()->sproutForms_forms->saveForm($form);

				$this->_formId = $form->id;

				// Add Fields to the Form
				foreach ($fieldSettings[$form->handle] as $settings) 
				{
					// Grab our form each time so we are sure to have the latest fieldLayoutId
					$form = craft()->sproutForms_forms->getFormById($this->_formId);

					$field = new FieldModel();
					$field->name        = $settings['name'];
					$field->handle      = $settings['handle'];
					$field->type        = $settings['type'];
					$field->required    = $settings['required'];
					$field->settings    = $settings['settings'];

					craft()->sproutForms_fields->saveField($form, $field);
				}
			}
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