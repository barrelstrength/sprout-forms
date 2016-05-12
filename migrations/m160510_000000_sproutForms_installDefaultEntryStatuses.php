<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160510_000000_sproutForms_installDefaultEntryStatuses extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutforms_entrystatuses';

		if (craft()->db->tableExists($tableName))
		{
			$defaultEntryStatuses = array(
				0 => array(
					'name'      => 'Unread',
					'handle'    => 'unread',
					'color'     => 'blue',
					'sortOrder' => 1,
					'isDefault' => 1
				),
				1 => array(
					'name'      => 'Read',
					'handle'    => 'read',
					'color'     => 'grey',
					'sortOrder' => 2,
					'isDefault' => 0
				)
			);

			foreach ($defaultEntryStatuses as $entryStatus)
			{
				craft()->db->createCommand()->insert($tableName, array(
					'name'      => $entryStatus['name'],
					'handle'    => $entryStatus['handle'],
					'color'     => $entryStatus['color'],
					'sortOrder' => $entryStatus['sortOrder'],
					'isDefault' => $entryStatus['isDefault']
				));
			}

			Craft::log('Installed default entry statuses.', LogLevel::Info, true);
		}

		return true;
	}
}