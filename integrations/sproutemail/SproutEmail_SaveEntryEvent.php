<?php
namespace Craft;

class SproutEmail_SaveEntryEvent extends SproutEmailBaseEvent
{
	public function getName()
	{
		return 'sproutForms.saveEntry';
	}

	public function getTitle()
	{
		return 'Save Sprout Forms Entry';
	}

	public function getDescription()
	{
		return 'Triggered when an sprout forms entry is saved.';
	}

	public function getOptionsHtml($context = array())
	{
		$forms = craft()->sproutForms_forms->getAllForms();

		$context['entryForms'] = null;

		if ($forms)
		{
			foreach ($forms as $form)
			{
				$context['entryForms'][$form->id] = $form->name;
			}
		}

		return craft()->templates->render('sproutforms/_events/saveEntry', $context);
	}

	public function prepareOptions()
	{
		return craft()->request->getPost('entryFormIds');
	}

	public function validateOptions($options, SproutForms_EntryModel $entry)
	{
		return in_array($entry->formId, $options);
	}

	public function prepareParams(Event $event)
	{
		return array('value' => $event->params['entry'], 'isNewEntry' => $event->params['isNewEntry']);
	}

	public function prepareValue($value)
	{
		return array('entryFormIds' => $value);
	}
}
