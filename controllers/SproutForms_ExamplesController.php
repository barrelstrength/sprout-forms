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
		$this->redirect(UrlHelper::getCpUrl() . '/sproutforms');
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
			SproutFormsPlugin::log($e->getMessage());

			craft()->userSession->setError(Craft::t('Unable to install the examples.'));

			$this->redirect('sproutforms/settings/examples');
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
					'name'                 => 'Contact Form',
					'handle'               => 'contact',
					'titleFormat'          => "{dateCreated|date('Y-m-d')} – {fullName} – {message|slice(0,22)}...",
					'redirectUri'          => 'sproutforms/examples/contact-form?message=thank-you',
					'displaySectionTitles' => false
				),
				array(
					'name'                 => 'Basic Fields Form',
					'handle'               => 'basic',
					'titleFormat'          => "{plainText} – {dropdown}{% if object.textarea %} – {{ object.textarea|slice(0,15) }}{% endif %}",
					'redirectUri'          => 'sproutforms/examples/basic-fields?message=thank-you',
					'displaySectionTitles' => true
				),
				// array(
				// 	'name' => 'All Craft Fields',
				// 	'handle' => 'advanced',
				// 	'titleFormat' => "{dateCreated|date('Y-m-d')}"
				// )
			);

			$fieldSettings = array(
				'contact' => array(
					'Default' => array(
						array(
							'name'     => 'Full Name',
							'handle'   => 'fullName',
							'type'     => 'PlainText',
							'required' => 1,
							'settings' => array(
								'placeholder' => '',
								'maxLength'   => '',
								'multiline'   => '',
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
								'maxLength'   => '',
								'multiline'   => '',
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
								'maxLength'   => '',
								'multiline'   => 1,
								'initialRows' => 4,
							)
						)
					)
				),
				'basic'   => array(
					'Section One' => array(
						array(
							'name'     => 'Plain Text Field',
							'handle'   => 'plainText',
							'type'     => 'PlainText',
							'required' => 1,
							'settings' => array(
								'placeholder' => '',
								'maxLength'   => '',
								'multiline'   => 0,
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
										'label'   => 'Option 1',
										'value'   => 'option1',
										'default' => ''
									),
									array(
										'label'   => 'Option 2',
										'value'   => 'option2',
										'default' => ''
									),
									array(
										'label'   => 'Option 3',
										'value'   => 'option3',
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
								'min'      => 0,
								'max'      => '',
								'decimals' => ''
							)
						)
					),
					'Section Two' => array(
						array(
							'name'     => 'Radio Buttons Field',
							'handle'   => 'radioButtons',
							'type'     => 'RadioButtons',
							'required' => 0,
							'settings' => array(
								'options' => array(
									array(
										'label'   => 'Option 1',
										'value'   => 'option1',
										'default' => ''
									),
									array(
										'label'   => 'Option 2',
										'value'   => 'option2',
										'default' => ''
									),
									array(
										'label'   => 'Option 3',
										'value'   => 'option3',
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
										'label'   => 'Option 1',
										'value'   => 'option1',
										'default' => ''
									),
									array(
										'label'   => 'Option 2',
										'value'   => 'option2',
										'default' => ''
									),
									array(
										'label'   => 'Option 3',
										'value'   => 'option3',
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
										'label'   => 'Option 1',
										'value'   => 'option1',
										'default' => ''
									),
									array(
										'label'   => 'Option 2',
										'value'   => 'option2',
										'default' => ''
									),
									array(
										'label'   => 'Option 3',
										'value'   => 'option3',
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
								'maxLength'   => '',
								'multiline'   => 1,
								'initialRows' => 4,
							)
						)
					)
				),
			);

			// Create Forms and their Content Tables
			foreach ($formSettings as $settings)
			{
				$form = new SproutForms_FormModel();

				// Assign our form settings
				$form->name                 = $settings['name'];
				$form->handle               = $settings['handle'];
				$form->titleFormat          = $settings['titleFormat'];
				$form->redirectUri          = $settings['redirectUri'];
				$form->displaySectionTitles = $settings['displaySectionTitles'];

				// Create the Form
				sproutForms()->forms->saveForm($form);

				// Set our field context
				craft()->content->fieldContext = $form->getFieldContext();
				craft()->content->contentTable = $form->getContentTable();

				//------------------------------------------------------------

				// Do we have a new field that doesn't exist yet?  
				// If so, save it and grab the id.

				$fieldLayout    = array();
				$requiredFields = array();

				$tabs = $fieldSettings[$form->handle];

				foreach ($tabs as $tabName => $newFields)
				{
					foreach ($newFields as $newField)
					{
						$field           = new FieldModel();
						$field->name     = $newField['name'];
						$field->handle   = $newField['handle'];
						$field->type     = $newField['type'];
						$field->required = $newField['required'];
						$field->settings = $newField['settings'];

						// Save our field
						craft()->fields->saveField($field);

						$fieldLayout[$tabName][] = $field->id;

						if ($field->required)
						{
							$requiredFields[] = $field->id;
						}
					}
				}

				// Set the field layout
				$fieldLayout = craft()->fields->assembleLayout($fieldLayout, $requiredFields);

				$fieldLayout->type = 'SproutForms_Form';
				$form->setFieldLayout($fieldLayout);

				// Save our form again with a layouts
				sproutForms()->forms->saveForm($form);
			}
		}
		catch (\Exception $e)
		{
			SproutFormsPlugin::log($e->getMessage());

			craft()->userSession->setError(Craft::t('Unable to install the examples.'));

			$this->redirect('sproutforms/settings/examples');
		}
	}
}