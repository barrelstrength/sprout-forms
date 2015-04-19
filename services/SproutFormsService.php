<?php
namespace Craft;

/**
 * Class SproutFormsService
 *
 * @package Craft
 * --
 * @property SproutForms_EntriesService  $entries
 * @property SproutForms_FieldsService   $fields
 * @property SproutForms_FormsService    $forms
 * @property SproutForms_GroupsService   $groups
 * @property SproutForms_SettingsService $settings
 */
class SproutFormsService extends BaseApplicationComponent
{
	public $entries;
	public $fields;
	public $forms;
	public $groups;
	public $settings;

	public function init()
	{
		parent::init();

		$this->entries  = Craft::app()->getComponent('sproutForms_entries');
		$this->fields   = Craft::app()->getComponent('sproutForms_fields');
		$this->forms    = Craft::app()->getComponent('sproutForms_forms');
		$this->groups   = Craft::app()->getComponent('sproutForms_groups');
		$this->settings = Craft::app()->getComponent('sproutForms_settings');
	}

	/**
	 * Returns a config value from general.php for the sproutForms array
	 *
	 * @param string     $name
	 * @param mixed|null $default
	 *
	 * @return null
	 */
	public function getConfig($name, $default = null)
	{
		$configs = craft()->config->get('sproutForms');

		return is_array($configs) && isset($configs[$name]) ? $configs[$name] : $default;
	}

	/**
	 * @param Event|SproutForms_OnBeforeSaveEntryEvent $event
	 *
	 * @throws \CException
	 */
	public function onBeforeSaveEntry(SproutForms_OnBeforeSaveEntryEvent $event)
	{
		$this->raiseEvent('onBeforeSaveEntry', $event);
	}

	/**
	 * @param Event|SproutForms_OnSaveEntryEvent $event
	 *
	 * @throws \CException
	 */
	public function onSaveEntry(SproutForms_OnSaveEntryEvent $event)
	{
		$this->raiseEvent('onSaveEntry', $event);
	}

	/**
	 * Handles event to attach files to email if properly configured
	 *
	 * @param Event $event
	 */
	public function handleOnBeforeSendEmail(Event $event)
	{
		$attachmentConfig = 'enableFileAttachments';

		// We only act if...
		// 1. This is a side effect of submitting a form
		// 2. File attachments are enabled for Sprout Forms
		if (isset($event->params['variables']['sproutFormsEntry']) && $this->getConfig($attachmentConfig))
		{
			$entry = $event->params['variables']['sproutFormsEntry'];

			/**
			 * @var $field FieldModel
			 */
			foreach ($entry->form->getFields() as $field)
			{
				$type = $field->getFieldType();

				if (get_class($type) === 'Craft\\AssetsFieldType')
				{
					/**
					 * @var $criteria ElementCriteriaModel
					 */
					$criteria = $entry->{$field->handle};

					if ($criteria instanceof ElementCriteriaModel)
					{
						$assets = $criteria->find();

						$this->attachAssetFilesToEmailModel($event->params['emailModel'], $assets);
					}
				}
			}
		}

		if (isset($event->params['variables']['sproutFormsEntry']) && !$this->getConfig($attachmentConfig))
		{
			$this->log('File attachments are currently not enabled for Sprout Forms.');
		}
	}

	/**
	 * @param mixed $message
	 * @param array $vars
	 */
	public function log($message, array $vars = array())
	{
		if (is_string($message))
		{
			$message = Craft::t($message, $vars);
		}
		else
		{
			$message = print_r($message, true);
		}

		SproutFormsPlugin::log($message, LogLevel::Info);
	}

	/**
	 * @param EmailModel       $email
	 * @param AssetFileModel[] $assets
	 */
	protected function attachAssetFilesToEmailModel(EmailModel $email, array $assets)
	{
		foreach ($assets as $asset)
		{
			$name = $asset->filename;
			$path = $this->getAssetFilePath($asset);

			$email->addAttachment($path, $name);
		}
	}

	/**
	 * @param AssetFileModel $asset
	 *
	 * @return string
	 */
	protected function getAssetFilePath(AssetFileModel $asset)
	{
		return $asset->getSource()->getSourceType()->getBasePath().$asset->getFolder()->path.$asset->filename;
	}
}