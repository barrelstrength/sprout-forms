<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m141026_060000_sproutForms_createFormElementTypes extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$oldTable = 'sproutforms_forms_old';
		$newTable = 'sproutforms_forms';

		// Rename the old Form table
		if (craft()->db->tableExists($newTable))
		{
			craft()->db->createCommand()->renameTable($newTable, $oldTable);
			SproutFormsPlugin::log("`$newTable` table renamed `$oldTable`.", LogLevel::Info, true);

			// ------------------------------------------------------------
			// Create new Form Table
			
			SproutFormsPlugin::log("Creating the new `$newTable` table.", LogLevel::Info, true);

			// Create the craft_sproutforms_forms table
			craft()->db->createCommand()->createTable($newTable, array(
				'id'                       => array('column' => 'integer', 'required' => true, 'primaryKey' => true),
				'fieldLayoutId'            => array('column' => 'integer', 'required' => false),
				'groupId'                  => array('maxLength' => 11, 'decimals' => 0, 'unsigned' => false, 'length' => 10, 'column' => 'integer'),
				'name'                     => array('required' => true),
				'handle'                   => array('required' => true),
				'titleFormat'              => array('required' => true),
				'displaySectionTitles'     => array(),
				'redirectUri'              => array(),
				'submitAction'             => array(),
				'submitButtonText'         => array(),
				'notificationRecipients'   => array(),
				'notificationSubject'      => array(),
				'notificationSenderName'   => array(),
				'notificationSenderEmail'  => array(),
				'notificationReplyToEmail' => array(),
			), null, false);

			// Add foreign keys to craft_sproutforms_forms
			craft()->db->createCommand()->addForeignKey($newTable, 'id', 'elements', 'id', 'CASCADE', null);
			craft()->db->createCommand()->addForeignKey($newTable, 'fieldLayoutId', 'fieldlayouts', 'id', 'SET NULL', null);

			SproutFormsPlugin::log("New `$newTable` table created.", LogLevel::Info, true);


			// ------------------------------------------------------------
			// Clean up Foreign Keys

			craft()->db->createCommand()->dropForeignKey('sproutforms_fields', 'formId');
			SproutFormsPlugin::log("Removed formId Foreign Key Constraint from 'sproutforms_fields' table.", LogLevel::Info, true);

			craft()->db->createCommand()->dropForeignKey('sproutforms_entries', 'formId');
			SproutFormsPlugin::log("Removed renamed formId Foreign Key Constraint from 'sproutforms_entries' table.", LogLevel::Info, true);

			craft()->db->createCommand()->addForeignKey('sproutforms_entries', 'formId', 'sproutforms_forms', 'id', 'CASCADE', null);
			SproutFormsPlugin::log("Added back formId Foreign Key Constraint from 'sproutforms_entries' table.", LogLevel::Info, true);

		}

		// ------------------------------------------------------------
		// Loop through each form in the old table and migrate it to the new table
		
		SproutFormsPlugin::log("Gathering all forms from the `$oldTable` table.", LogLevel::Info, true);

		$oldForms = craft()->db->createCommand()
								    ->select('*')
								    ->from($oldTable)
								    ->queryAll();

		foreach ($oldForms as $oldForm) 
		{
			SproutFormsPlugin::log("Build SproutForms_FormModel for " . $oldForm['name'] . " Form", LogLevel::Info, true);

			$user = craft()->userSession->getUser();

			// Map any values from the old form to their 
			// new column names to save to the new form

			$newForm = new SproutForms_FormModel();

			$newForm->name = $oldForm['name'];
			$newForm->handle = $oldForm['handle'];
			$newForm->submitButtonText = $oldForm['submitButtonText'];
			$newForm->redirectUri = $oldForm['redirectUri'];
			$newForm->handle = $oldForm['handle'];
			$newForm->titleFormat = "Form submission on " . "{dateCreated|date('D, d M Y H:i:s')}";
			$newForm->displaySectionTitles = 0;
			$newForm->notificationRecipients = $oldForm['email_distribution_list'];
			$newForm->notificationSubject = $oldForm['notification_subject'];
			$newForm->notificationSenderName = craft()->getSiteName();
			$newForm->notificationSenderEmail = $user->email;
			$newForm->notificationReplyToEmail = $oldForm['notification_reply_to'];

			// Save the Form
			// Create a new content table			
			craft()->sproutForms_forms->saveForm($newForm);

			SproutFormsPlugin::log($newForm->name . " Form saved anew. Form ID: " . $newForm->id, LogLevel::Info, true);


			// Set our field context
			craft()->content->fieldContext = $newForm->getFieldContext();
			craft()->content->contentTable = $newForm->getContentTable();

			SproutFormsPlugin::log($newForm->name . " Form fieldContext: " . craft()->content->fieldContext, LogLevel::Info, true);
			SproutFormsPlugin::log($newForm->name . " Form contentTable: " . craft()->content->contentTable, LogLevel::Info, true);

			SproutFormsPlugin::log("Grab all fields for " . $newForm->name . " Form", LogLevel::Info, true);

			// Get the Form Fields
			$oldFormFields = craft()->db->createCommand()
								    ->select('*')
								    ->from('sproutforms_fields')
								    ->where('formId=:formId', array(':formId'=>$oldForm['id']))
								    ->queryAll();

			// Prepare a couple variables to help save our fields and layout
			$fieldLayout = array();
			$requiredFields = array();

			$fieldMap = array();

			foreach ($oldFormFields as $oldFormField) 
			{
				$newFieldHandle = str_replace("formId".$oldForm['id']."_", "", $oldFormField['handle']);

				// Determine if we have a Number field
				// @TODO - adding Number type breaks things
				// Might need to update teh Settings object to have the correct values min/max...
				// if ((strpos($oldFormField['validation'], 'numerical') !== FALSE))
				// {
				// 	$oldFormField['type'] = 'Number';
				// }
				// else
				// {
				// 	$oldFormField['type'] = $oldFormField['type'];
				// }

				// Build a field map of our old field handles and our new ones
				// so we can more easily match things up when inserting fields later
				$fieldMap[$oldFormField['handle']] = array(
					'type' => $oldFormField['type'],
					'newHandle' => $newFieldHandle
				);

				//------------------------------------------------------------

				SproutFormsPlugin::log("Build FieldModel for " . $oldFormField['name'] . " Field", LogLevel::Info, true);

				

				// SproutFormsPlugin::log("The Fieldtype " . $newFieldType . " for the "  . $oldFormField['name'] ." Field", LogLevel::Info, true);

				$newField = new FieldModel();
				$newField->name         = $oldFormField['name'];
				$newField->handle       = $newFieldHandle;
				$newField->instructions = $oldFormField['instructions'];
				$newField->type         = $oldFormField['type'];
				$newField->required     = (strpos($oldFormField['validation'], 'required') !== FALSE);
				$newField->settings     = $oldFormField['settings'];

				// Save our field
				craft()->fields->saveField($newField);
				
				SproutFormsPlugin::log($oldFormField['name'] . " Field saved.", LogLevel::Info, true);

				$fieldLayout['Form'][] = $newField->id;
				
				if ($newField->required) 
				{
					$requiredFields[] = $newField->id;
				}
			}

			// Set the field layout
			$fieldLayout = craft()->fields->assembleLayout($fieldLayout, $requiredFields);
			
			$fieldLayout->type = 'SproutForms_Form';
			$newForm->setFieldLayout($fieldLayout);

			// Save our form again with a layouts
			craft()->sproutForms_forms->saveForm($newForm);

			SproutFormsPlugin::log("Form saved again with fieldLayout", LogLevel::Info, true);

			// Migrate the Entries Content

			SproutFormsPlugin::log("Grab Form Entries for ". $oldForm['name'] . " Form", LogLevel::Info, true);

			// Get the Form Entries
			$oldFormEntries = craft()->db->createCommand()
								    ->select('*')
								    ->from('sproutforms_content')
								    ->where('formId=:formId', array(':formId'=>$oldForm['id']))
								    ->queryAll();
			
	    foreach ($oldFormEntries as $oldFormEntry) 
	    {
	    	SproutFormsPlugin::log("Build SproutForms_EntryModel for Form Entry ID ". $oldFormEntry['id'], LogLevel::Info, true);

	    	$newFormEntry = new SproutForms_EntryModel();

	    	$oldEntryServerData = json_decode($oldFormEntries[24]['serverData']);

	    	$newFormEntry->formId = $newForm->id;
				$newFormEntry->ipAddress = $oldEntryServerData->ipAddress;
				$newFormEntry->userAgent = $oldEntryServerData->userAgent;

	    	$newFormFields = array();

	    	// Loop through our field map
	    	foreach ($fieldMap as $oldHandle => $fieldInfo) 
	    	{	
	    		// If any columns in our current Form Entry match a 
	    		// field in our field map, add that field to be saved
	    		if ($oldFormEntry[$oldHandle]) 
	    		{
	    			$newFormFields[$fieldInfo['newHandle']] = $oldFormEntry[$oldHandle];
	    		}
	    	}

	    	$_POST['fields'] = $newFormFields;

	    	$fieldsLocation = 'fields';
	    	$newFormEntry->setContentFromPost($fieldsLocation);
	    	$newFormEntry->setContentPostLocation($fieldsLocation);

	    	SproutFormsPlugin::log("Try to Save Old Form Entry ID ". $oldFormEntry['id'], LogLevel::Info, true);

	    	craft()->sproutForms_entries->saveEntry($newFormEntry);

	    	SproutFormsPlugin::log("Save New Form Entry ID ". $newFormEntry->id, LogLevel::Info, true);
	    }


	    // Drop old field table
	    if (craft()->db->tableExists('sproutforms_fields'))
	    {
	    	SproutFormsPlugin::log("Dropping the 'sproutforms_fields' table.", LogLevel::Info, true);

	    	craft()->db->createCommand()->dropTable('sproutforms_fields');

	    	SproutFormsPlugin::log("'sproutforms_fields' table dropped.", LogLevel::Info, true);	
	    }

	    // Drop old entries table
	    if (craft()->db->tableExists('sproutforms_content'))
	    {
	    	SproutFormsPlugin::log("Dropping the 'sproutforms_content' table.", LogLevel::Info, true);

	    	craft()->db->createCommand()->dropTable('sproutforms_content');

	    	SproutFormsPlugin::log("'sproutforms_content' table dropped.", LogLevel::Info, true);	
	    }
	    
	    // Drop old forms table
	    if (craft()->db->tableExists($oldTable))
	    {
	    	SproutFormsPlugin::log("Dropping the old `$oldTable` table.", LogLevel::Info, true);

	    	craft()->db->createCommand('SET FOREIGN_KEY_CHECKS = 0;')->execute();
	    	
	    	// Need to drop this after we drop the fields table because fields has a fk
	    	craft()->db->createCommand()->dropTable($oldTable);

	    	craft()->db->createCommand('SET FOREIGN_KEY_CHECKS = 1;')->execute();

	    	SproutFormsPlugin::log("`$oldTable` table dropped.", LogLevel::Info, true);
	    }
		}

		return true;
	}
}