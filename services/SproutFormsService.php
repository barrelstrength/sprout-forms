<?php
namespace Craft;

/**
 * Class SproutFormsService
 *
 * @package Craft
 * --
 * @property SproutForms_EntriesService        $entries
 * @property SproutForms_FieldsService         $fields
 * @property SproutForms_FormsService          $forms
 * @property SproutForms_GroupsService         $groups
 * @property SproutForms_SettingsService       $settings
 * @property SproutForms_FrontEndFieldsService $frontEndFields
 */
class SproutFormsService extends BaseApplicationComponent
{
	public $entries;
	public $fields;
	public $forms;
	public $groups;
	public $settings;
	public $frontEndFields;

	public function init()
	{
		parent::init();

		$this->entries        = Craft::app()->getComponent('sproutForms_entries');
		$this->fields         = Craft::app()->getComponent('sproutForms_fields');
		$this->forms          = Craft::app()->getComponent('sproutForms_forms');
		$this->groups         = Craft::app()->getComponent('sproutForms_groups');
		$this->settings       = Craft::app()->getComponent('sproutForms_settings');
		$this->frontEndFields = Craft::app()->getComponent('sproutForms_frontEndFields');
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
	 * @param Event|SproutForms_OnBeforePopulateEntryEvent $event
	 *
	 * @throws \CException
	 */
	public function onBeforePopulateEntry(SproutForms_OnBeforePopulateEntryEvent $event)
	{
		$this->raiseEvent('onBeforePopulateEntry', $event);
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
		$variables             = $event->params['variables'];
		$enableFileAttachments = isset($variables['enableFileAttachments']) ? $variables['enableFileAttachments'] : null;
		$variables['externalPaths'] = array();

		// We only act if...
		// 1. This is a side effect of submitting a form
		// 2. File attachments are enabled for Sprout Forms
		// This will be called for each recipient, let's update the email model just for the first time
		if (isset($variables['sproutFormsEntry']) && $enableFileAttachments)
		{
			$entry = $variables['sproutFormsEntry'];
            $email = $event->params['emailModel'];
            // Reset attachments
            $email->attachments = [];

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

						$this->attachAssetFilesToEmailModel($email, $assets, $variables);
                        $event->params['emailModel'] = $email;
						$event->params['variables'] = $variables;
					}
				}
			}
		}

		if (isset($variables['sproutFormsEntry']) && !$enableFileAttachments)
		{
			$this->log('File attachments are currently not enabled for Sprout Forms.');
		}
	}

	/**
	 * Delete any files created with Externals Assets Sources
	 *
	 * @param Event $event
	 */
	public function handleOnSendEmail(Event $event)
	{
		$variables = $event->params['variables'];

		if (isset($variables['externalPaths']))
		{
			foreach ($variables['externalPaths'] as $path)
			{
				if (file_exists($path))
				{
					unlink($path);
				}
			}
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
	 * @param array $variables from event
	 */
	protected function attachAssetFilesToEmailModel(EmailModel &$email, array $assets, &$variables = null)
	{
		foreach ($assets as $asset)
		{
			$name = $asset->filename;
			$type = $asset->getSource()->getSourceType();
			$path = null;

			if ($type->isSourceLocal())
			{
				$path = $this->getAssetFilePath($asset);
			}
			else
			{
				// External Asset sources
				$path = $type->getLocalCopy($asset);
				// let's save the path to delete it after sent
				array_push($variables['externalPaths'], $path);
			}

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
		return $asset->getSource()->getSourceType()->getBasePath() . $asset->getFolder()->path . $asset->filename;
	}

	/**
	 * Returns whether or not the templates directory is writable
	 *
	 * @return bool
	 */
	public function canCreateExamples()
	{
		return is_writable(craft()->path->getSiteTemplatesPath());
	}

	/**
	 * Return wether or not the example template already exist
	 *
	 * @return bool
	 */
	public function hasExamples()
	{
		$path = craft()->path->getSiteTemplatesPath() . 'sproutforms';

		if (file_exists($path))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param $subject
	 *
	 * @return string
	 */
	public function encodeSubjectLine($subject)
	{
		return '=?UTF-8?B?' . base64_encode($subject) . '?=';
	}
}