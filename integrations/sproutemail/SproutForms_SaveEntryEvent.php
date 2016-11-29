<?php
namespace Craft;

/**
 * Class SproutForms_SaveEntryEvent
 *
 * @package Craft
 */
class SproutForms_SaveEntryEvent extends SproutEmailBaseEvent
{
	public function getName()
	{
		return 'sproutForms.saveEntry';
	}

	public function getTitle()
	{
		return Craft::t('When a Sprout Forms entry is saved');
	}

	public function getDescription()
	{
		return Craft::t('Triggered when a form entry is saved.');
	}

	public function getOptionsHtml($context = array())
	{
		if (!isset($context['availableForms']))
		{
			$context['availableForms'] = $this->getAllForms();
		}

		return craft()->templates->render('sproutforms/_events/saveEntry', $context);
	}

	public function prepareOptions()
	{
		$rules = craft()->request->getPost('rules.sproutForms');

		return array(
			'sproutForms' => $rules,
		);
	}

	/**
	 * Returns whether or not the entry meets the criteria necessary to trigger the event
	 *
	 * @param mixed                  $options
	 * @param SproutForms_EntryModel $entry
	 * @param array                  $params
	 *
	 * @return bool
	 */
	public function validateOptions($options, SproutForms_EntryModel $entry, array $params = array())
	{
		$isNewEntry = isset($params['isNewEntry']) && $params['isNewEntry'];
		$whenNew    = isset($options['sproutForms']['saveEntry']['whenNew']) &&
			$options['sproutForms']['saveEntry']['whenNew'];

		// If any section ids were checked
		// Make sure the entry belongs in one of them
		if (!empty($options['sproutForms']['saveEntry']['formIds']) &&
			count($options['sproutForms']['saveEntry']['formIds'])
		)
		{
			if (!in_array($entry->getForm()->id, $options['sproutForms']['saveEntry']['formIds']))
			{
				return false;
			}
		}

		// If only new entries was checked
		// Make sure the entry is new
		if (!$whenNew || ($whenNew && $isNewEntry))
		{
			return true;
		}

		return false;
	}

	public function prepareParams(Event $event)
	{
		return array('value' => $event->params['entry'], 'isNewEntry' => $event->params['isNewEntry']);
	}

	public function prepareValue($value)
	{
		return $value;
	}

	/**
	 * @throws Exception
	 *
	 * @return BaseElementModel|null
	 */
	public function getMockedParams()
	{
		$criteria = craft()->elements->getCriteria('SproutForms_Entry');

		$formIds = isset($this->options['sproutForms']['saveEntry']['formIds']) ?
			$this->options['sproutForms']['saveEntry']['formIds'] : array();

		if (is_array($formIds) && count($formIds))
		{
			$formId = array_shift($formIds);

			$criteria->formId = $formId;
		}

		return $criteria->first();
	}

	/**
	 * Returns an array of forms suitable for use in checkbox field
	 *
	 * @return array
	 */
	protected function getAllForms()
	{
		$result  = sproutForms()->forms->getAllForms();
		$options = array();

		foreach ($result as $key => $forms)
		{
			array_push(
				$options, array(
					'label' => $forms->name,
					'value' => $forms->id
				)
			);
		}

		return $options;
	}
}
