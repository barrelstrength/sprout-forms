<?php
namespace Craft;

class m150629_000000_sproutForms_updateSproutEmailNotificationOptionsFormat extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		if (($table = $this->dbConnection->schema->getTable('{{sproutemail_campaigns_notifications}}')))
		{
			if ($table->getColumn('options') != null)
			{
				$notifications = craft()->db->createCommand()
					->select('id, eventId, options')
					->from('sproutemail_campaigns_notifications')
					->where('eventId=:eventId', array(':eventId' => 'sproutForms-saveEntry'))
					->queryAll();

				if ($count = count($notifications))
				{
					SproutFormsPlugin::log('Notifications found: ' . $count, LogLevel::Info, true);

					$newOptions = array();

					foreach ($notifications as $notification)
					{
						SproutFormsPlugin::log('Migrating Sprout Forms saveEntry notification', LogLevel::Info, true);

						$newOptions = $this->_updateSaveFormEntryOptions($notification['options']);

						craft()->db->createCommand()->update('sproutemail_campaigns_notifications', array(
							'options' => $newOptions
						), 'id= :id', array(':id' => $notification['id'])
						);

						SproutFormsPlugin::log('Migration of notification complete', LogLevel::Info, true);
					}
				}

				SproutFormsPlugin::log('No notifications found to migrate.', LogLevel::Info, true);
			}
			else
			{
				SproutFormsPlugin::log('Could not find the `options` column.', LogLevel::Info, true);
			}
		}
		else
		{
			SproutFormsPlugin::log('Could not find the `sproutemail_campaigns_notifications` table.', LogLevel::Info, true);
		}

		return true;
	}

	private function _updateSaveFormEntryOptions($options)
	{
		if (substr($options, 0, 1) === '[')
		{
			// Older versions of Sprout Forms just saved an
			// array of IDs ["3"] so we make it work
			$whenNew    = 1;
			$sectionIds = $options;
		}
		else
		{
			$oldOptions = JsonHelper::decode($options);

			$whenNew    = isset($oldOptions['entriesSaveEntryOnlyWhenNew']) ? $oldOptions['entriesSaveEntryOnlyWhenNew'] : '';
			$sectionIds = isset($oldOptions['entriesSaveEntrySectionIds']) ? $oldOptions['entriesSaveEntrySectionIds'] : '';
		}

		$newOptions = array(
			'sproutForms' => array(
				'saveEntry' => array(
					'whenNew' => $whenNew,
					'formIds' => $sectionIds
				)
			)
		);

		return JsonHelper::encode($newOptions);
	}
}