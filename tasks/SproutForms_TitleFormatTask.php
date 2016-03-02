<?php
namespace Craft;

/**
 * Craft SproutForms_TitleFormatTask task
 */
class SproutForms_TitleFormatTask extends BaseTask
{
	private $_contentRows;
	private $_newFormat;
	private $_contentTable;

	/**
	 * Defines the settings.
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineSettings()
	{
		//content table and array
		return array(
			'contentRows'  => AttributeType::Mixed,
			'contentTable' => AttributeType::String,
			'newFormat'    => AttributeType::String,
		);
	}

	/**
	 * Returns the default description for this task.
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return Craft::t('Updating form entry titles');
	}

	/**
	 * Gets the total number of steps for this task.
	 *
	 * @return int
	 */
	public function getTotalSteps()
	{
		$settings            = $this->getSettings();
		$this->_contentRows  = $settings->contentRows;
		$this->_newFormat    = $settings->newFormat;
		$this->_contentTable = $settings->contentTable;

		return count($this->_contentRows);
	}

	/**
	 * Runs a task step.
	 *
	 * @param int $step
	 *
	 * @return bool
	 */
	public function runStep($step)
	{
		$contentRow = $this->_contentRows[$step];

		//Call the update process
		$response = sproutForms()->entries->updateTitleFormat($contentRow, $this->_newFormat, $this->_contentTable);

		if (!$response)
		{
			SproutFormsPlugin::log('SproutForms has failed to update the title format for ' . $this->_contentTable . ' Id:' . $contentId, LogLevel::Error);
		}

		return true;
	}
}