<?php
namespace Craft;

/**
 * Class SproutFormsEntriesDataSource
 *
 * @package Craft
 */
class SproutFormsEntriesDataSource extends SproutReportsBaseDataSource
{
	public function getName()
	{
		return Craft::t('Sprout Forms Entries');
	}

	/**
	 * @return null|string
	 */
	public function getDescription()
	{
		return Craft::t('Query form entries');
	}

	/**
	 * @param SproutReports_ReportModel $report
	 *
	 * @return \CDbDataReader
	 */
	public function getResults(SproutReports_ReportModel &$report, $options = array())
	{
		$startDate = DateTime::createFromString($report->getOption('startDate'), craft()->timezone);
		$endDate   = DateTime::createFromString($report->getOption('endDate'), craft()->timezone);

		if (count($options))
		{
			if (isset($options['startDate']))
			{
				$startDate = DateTime::createFromString($options['startDate'], craft()->timezone);
			}

			if (isset($options['endDate']))
			{
				$endDate = DateTime::createFromString($options['endDate'], craft()->timezone);
			}
		}

		$formId = $report->getOption('formId');

		$form = craft()->sproutForms_forms->getFormById($formId);

		if (!$form)
		{
			return null;
		}

		$contentTable = $form->contentTable;

		$query = craft()->db->createCommand()
			->select('*')
			->from($contentTable . ' AS entries');

		if ($startDate && $endDate)
		{
			$query->where('entries.dateCreated > :startDate', array(':startDate' => $startDate->mySqlDateTime()));
			$query->andWhere('entries.dateCreated < :endDate', array(':endDate' => $endDate->mySqlDateTime()));
		}

		return $query->queryAll();
	}

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	public function getOptionsHtml(array $options = array())
	{
		$criteria        = craft()->elements->getCriteria('SproutForms_Form');
		$criteria->limit = null;
		$criteria->order = 'name';

		$forms = $criteria->find();

		foreach ($forms as $form)
		{
			$formOptions[] = array(
				'label' => $form->name,
				'value' => $form->id
			);
		}

		// @todo Determine sensible default start and end date based on Order data
		$defaultStartDate = null;
		$defaultEndDate   = null;

		if (count($options))
		{
			if (isset($options['startDate']))
			{
				$options['startDate'] = DateTime::createFromString($options['startDate'], craft()->timezone);
			}

			if (isset($options['endDate']))
			{
				$options['endDate'] = DateTime::createFromString($options['endDate'], craft()->timezone);
			}
		}
		else
		{
			$options = $this->report->getOptions();

			$options['startDate'] = DateTime::createFromString($this->report->getOption('startDate'), craft()->timezone);
			$options['endDate']   = DateTime::createFromString($this->report->getOption('endDate'), craft()->timezone);
		}

		return craft()->templates->render('sproutforms/_reports/options/entries', array(
			'formOptions'      => $formOptions,
			'defaultStartDate' => new DateTime($defaultStartDate),
			'defaultEndDate'   => new DateTime($defaultEndDate),
			'options'          => $options
		));
	}
}
