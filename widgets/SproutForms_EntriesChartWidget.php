<?php
namespace Craft;

/**
 * Class SproutForms_EntriesChartWidget
 */
class SproutForms_EntriesChartWidget extends BaseWidget
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Sprout Forms Entries Chart');
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return Craft::t('Recent Form Entries');
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
		//$settings = $this->getSettings();
		//
		//$groupId = $settings->userGroupId;
		//$userGroup = craft()->userGroups->getGroupById($groupId);

		//$options = $settings->getAttributes();

		$options['dateRange']   = 'd30';
		$options['orientation'] = craft()->locale->getOrientation();

		craft()->templates->includeJsResource('sproutforms/js/SproutFormsEntriesChartWidget.js');
		craft()->templates->includeJs(
			'new Craft.SproutForms.EntriesChartWidget(' . $this->model->id . ', ' . JsonHelper::encode($options) . ');'
		);

		return '<div></div>';
	}

	/**
	 * @return string
	 */
	public function getSettingsHtml()
	{
		//return craft()->templates->render('_components/widgets/NewUsers/settings', array(
		//	'settings' => $this->getSettings()
		//));
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'formId'    => AttributeType::Number,
			'dateRange' => AttributeType::String,
		);
	}
}
