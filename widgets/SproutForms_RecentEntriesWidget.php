<?php
namespace Craft;

class SproutForms_RecentEntriesWidget extends BaseWidget
{
	protected $colspan = 2;

	public function getName()
	{
		$name = Craft::t('Recent Form Entries');

		// Concat form name if the user select a specific form
		if ($this->getSettings()->form != 0 && $this->getSettings()->form != null)
		{
			$form = sproutForms()->forms->getFormById($this->getSettings()->form);

			if ($form)
			{
				$name = Craft::t('Recent {formName} Entries', array(
					'formName' => $form->name
				));
			}
		}

		return $name;
	}

	public function getIconPath()
	{
		return craft()->path->getPluginsPath() . 'sproutforms/resources/icon.svg';
	}

	public function getBodyHtml()
	{
		$settings = $this->getSettings();
		// Get the SproutForms_Entry Element type criteria
		$criteria = craft()->elements->getCriteria('SproutForms_Entry');

		if ($settings->form != 0)
		{
			$criteria->formId = $settings->form;
		}
		$criteria->limit = $settings->limit;

		return craft()->templates->render('sproutforms/_widgets/recententries/body', array(
			'entries'  => $criteria->find(),
			'settings' => $settings
		));
	}

	public function getSettingsHtml()
	{
		$forms = array(0 => Craft::t('All forms'));

		$sproutForms = sproutForms()->forms->getAllForms();
		if ($sproutForms)
		{
			foreach ($sproutForms as $form)
			{
				$forms[$form->id] = $form->name;
			}
		}

		return craft()->templates->render('sproutforms/_widgets/recententries/settings', array(
			'settings'    => $this->getSettings(),
			'sproutForms' => $forms
		));
	}

	protected function defineSettings()
	{
		return array(
			'form'     => array(AttributeType::Number, 'required' => true),
			'limit'    => array(AttributeType::Number, 'min' => 0, 'default' => 10),
			'showDate' => array(AttributeType::String)
		);
	}
}
