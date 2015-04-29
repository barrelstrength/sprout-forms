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
		return Craft::t('Sprout Forms Save Entry');
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
		return array(
			'entriesSaveEntrySectionIds'  => craft()->request->getPost('entriesSaveEntrySectionIds'),
			'entriesSaveEntryOnlyWhenNew' => craft()->request->getPost('entriesSaveEntryOnlyWhenNew'),
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
		$isNewEntry  = isset($params['isNewEntry']) && $params['isNewEntry'];
		$onlyWhenNew = isset($options['entriesSaveEntryOnlyWhenNew']) && $options['entriesSaveEntryOnlyWhenNew'];

		// If any section ids were checked
		// Make sure the entry belongs in one of them
		if (!empty($options['entriesSaveEntrySectionIds']) && count($options['entriesSaveEntrySectionIds']))
		{
			if (!in_array($entry->getForm()->id, $options['entriesSaveEntrySectionIds']))
			{
				return false;
			}
		}

		// If only new entries was checked
		// Make sure the entry is new
		if (!$onlyWhenNew || ($onlyWhenNew && $isNewEntry))
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

		if (isset($this->options['entriesSaveEntrySectionIds']) && count($this->options['entriesSaveEntrySectionIds']))
		{
			$ids = $this->options['entriesSaveEntrySectionIds'];

			if (is_array($ids) && count($ids))
			{
				$id = array_shift($ids);

				$criteria->formId = $id;
			}
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
