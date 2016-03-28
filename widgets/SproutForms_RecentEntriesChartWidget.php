<?php
namespace Craft;

/**
 * Class SproutForms_RecentEntriesChartWidget
 */
class SproutForms_RecentEntriesChartWidget extends BaseWidget
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Recent Form Entries (Chart)');
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		$name = Craft::t('Recent Form Entries');

		// Concat form name if the user select a specific form
		if ($this->getSettings()->formId != 0 && $this->getSettings()->formId != null)
		{
			$form = sproutForms()->forms->getFormById($this->getSettings()->formId);

			if ($form)
			{
				$name = Craft::t('Recent {formName} Entries', array(
					'formName' => $form->name
				));
			}
		}

		return $name;
	}

	/**
	 * @return string
	 */
	public function getIconPath()
	{
		return craft()->path->getPluginsPath() . 'sproutforms/resources/icon.svg';
	}

	/**
	 * @return bool
	 */
	public function isSelectable()
	{
		return true;
	}

	/**
	 * @inheritDoc IWidget::getBodyHtml()
	 *
	 * @return string|false
	 */
	public function getBodyHtml()
	{
		$settings = $this->getSettings();

		$options['orientation'] = craft()->locale->getOrientation();
		$options['dateRange'] = $settings->dateRange;
		$options['formId'] = $settings->formId;

		craft()->templates->includeJsResource('sproutforms/js/SproutFormsRecentEntriesChartWidget.js');
		craft()->templates->includeJs('new Craft.SproutForms.RecentEntriesChartWidget('.$this->model->id.', '.JsonHelper::encode($options).');');

		return craft()->templates->render('sproutforms/_widgets/recententrieschart/body');
	}

	/**
	 * @return string
	 */
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

		return craft()->templates->render('sproutforms/_widgets/recententrieschart/settings', array(
			'settings'    => $this->getSettings(),
			'sproutForms' => $forms
		));
	}
	
	/**
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'formId'    => array(AttributeType::Number, 'required' => true),
			'dateRange' => AttributeType::String,
		);
	}
}
