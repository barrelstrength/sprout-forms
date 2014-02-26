<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140225_090534_sproutForms_addFieldNotificationReplyTo extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{		
				$sproutFormsTable = $this->dbConnection->schema->getTable('{{sproutforms_forms}}');
		
				if ($sproutFormsTable)
				{
				    // add sproutforms_forms.notification_reply_to
					if ($sproutFormsTable->getColumn('notification_reply_to') == null)
					{
						Craft::log('Adding `notification_reply_to` column to the `sproutforms_forms` table.', LogLevel::Info, true);
		
						$this->addColumnAfter('sproutforms_forms', 'notification_reply_to', array(AttributeType::String, 'required' => false), 'email_distribution_list');
		
						Craft::log('Added `notification_reply_to` column to the `sproutforms_forms` table.', LogLevel::Info, true);
					}
					else
					{
						Craft::log('Tried to add a `notification_reply_to` column to the `sproutforms_forms` table, but there is already one there.', LogLevel::Warning);
					}
				}
				else
				{
					Craft::log('Could not find an `sproutforms_forms` table. Wut?', LogLevel::Error);
				}
		
		return true;
	}
}