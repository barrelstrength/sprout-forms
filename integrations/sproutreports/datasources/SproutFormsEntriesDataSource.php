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
	public function getResults(SproutReports_ReportModel &$report)
	{
		$startDate = DateTime::createFromString($report->getOption('startDate'));
		$endDate = DateTime::createFromString($report->getOption('endDate'));

		$formId = $report->getOption('formId');

		$form = craft()->sproutForms_forms->getFormById($formId);

		$contentTable = $form->contentTable;

		$query = craft()->db->createCommand()
			->select('*')
			->from($contentTable . ' AS entries')
			->where('entries.dateCreated > :startDate', array(':startDate' => $startDate))
			->andWhere('entries.dateCreated < :endDate', array(':endDate' => $endDate));

		return $query->queryAll();
	}

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	public function getOptionsHtml(array $options = array())
	{
		$criteria = craft()->elements->getCriteria('SproutForms_Form');
		$criteria->limit = null;
		$criteria->order = 'name';

		$forms = $criteria->find();

		// @todo - can we add an all forms options?
		// Selecting from multiple content tables may get tricky.
		//$formOptions[] = array(
		//	'label' => 'All Forms',
		//	'value' => '*'
		//);

		foreach ($forms as $form)
		{
			$formOptions[] = array(
				'label' => $form->name,
				'value' => $form->id
			);
		}

		$options['forms'] = $formOptions;

		$options['formId'] = $this->report->getOption('formId');

		// @todo Determine sensible default start and end date based on Order data
		$defaultStartDate = null;
		$defaultEndDate = null;

		$startDate = DateTime::createFromString($this->report->getOption('startDate'));
		$endDate = DateTime::createFromString($this->report->getOption('endDate'));

		$options['startDate'] = $startDate;
		$options['endDate'] = $endDate;

		$options['defaultStartDate'] = new DateTime($defaultStartDate);
		$options['defaultEndDate'] = new DateTime($defaultEndDate);

		return craft()->templates->render(
			'sproutforms/_reports/options/entries',
			compact('options')
		);
	}
}
