<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m141026_020000_sproutForms_renameOldFormsTableAndCreateNewFormsTable extends BaseMigration
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
				'titleFormat'              => array(
					'required' => true,
					'default'  => "{dateCreated|date('D, d M Y H:i:s')}"
				),
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
		}

		return true;
	}
}