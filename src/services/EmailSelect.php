<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use craft\base\Field;
use yii\base\Component;
use craft\db\Query;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\integrations\sproutforms\fields\EmailSelect;

class EmailSelect extends Component
{
	/**
	 * @param      $options
	 * @param null $value
	 *
	 * @return mixed
	 */
	public function obfuscateEmailAddresses($options, $value = null)
	{
		foreach ($options as $key => $option)
		{
			$options[$key]['value'] = $key;

			if ($option['value'] == $value)
			{
				$options[$key]['selected'] = 1;
			}
			else
			{
				$options[$key]['selected'] = 0;
			}
		}

		return $options;
	}

	/**
	 * @param       $formId
	 * @param array $submittedFields
	 *
	 * @return bool
	 */
	public function unobfuscateEmailAddresses($formId, $submittedFields = array())
	{
		if (!is_numeric($formId))
		{
			return false;
		}

		$fieldContext = 'sproutForms:' . $formId;

		// Get all Email Select Fields for this form
		$emailSelectFieldHandles = (new Query())
			->select('handle')
			->from('{{%fields}}')
			->where(['context' => $fieldContext, 'type' => EmailSelect::class])
			->all();

		$oldContext = Craft::$app->content->fieldContext;

		Craft::$app->content->fieldContext = $fieldContext;

		foreach ($emailSelectFieldHandles as $key => $handle)
		{
			if (isset($submittedFields[$handle]))
			{
				// Get our field settings, which include the map of
				// email addresses to their indexes
				$field   = Craft::$app->fields->getFieldByHandle($handle);
				$options = $field->settings['options'];

				// Get the obfuscated email index from our post request
				$index      = $submittedFields[$handle];
				$emailValue = $options[$index]['value'];

				// Update the Email Select value in our post request from
				// the Email Index value to the Email Address
				$_POST['fields'][$handle] = $emailValue;
			}
		}

		Craft::$app->content->fieldContext = $oldContext;
	}

	/**
	 * Handles event to unobfuscate email addresses in a Sprout Forms submission
	 *
	 * @param Event $event
	 */
	public function handleUnobfuscateEmailAddresses(Event $event)
	{
		if (Craft::$app->request->getIsCpRequest())
		{
			return;
		}

		$formId          = $event->params['form']->id;
		$submittedFields = Craft::$app->request->getBodyParam('fields');

		// Unobfuscate email address in $_POST request
		$this->unobfuscateEmailAddresses($formId, $submittedFields);
	}
}
