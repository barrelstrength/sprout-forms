<?php
namespace Craft;

/**
 * Class SproutForms_ChartsController
 */
class SproutForms_ChartsController extends ElementIndexController
{
	/**
	 * Returns the data needed to display a Submissions chart.
	 *
	 * @return void
	 */
	public function actionGetEntriesData()
	{
		// Required for Dashboard widget, unnecessary for Entries Index view
		$formId = craft()->request->getPost('formId');

		$startDateParam = craft()->request->getRequiredPost('startDate');
		$endDateParam = craft()->request->getRequiredPost('endDate');

		$startDate = DateTime::createFromString($startDateParam, craft()->timezone);
		$endDate = DateTime::createFromString($endDateParam, craft()->timezone);
		$endDate->modify('+1 day');

		$intervalUnit = ChartHelper::getRunChartIntervalUnit($startDate, $endDate);

		// Prep the query
		$criteria = $this->getElementCriteria();
		$criteria->limit = null;

		// Don't use the search
		$criteria->search = null;

		$query = craft()->elements->buildElementsQuery($criteria)
			->select('COUNT(*) as value');

		if ($formId != 0)
		{
			$query->andWhere('forms.id = :formId',
					array(':formId' => $formId)
			);
		}

		// Get the chart data table
		$dataTable = ChartHelper::getRunChartDataFromQuery($query, $startDate, $endDate,
			'entries.dateCreated',
			array(
				'intervalUnit' => $intervalUnit,
				'valueLabel' => Craft::t('Submissions'),
				'valueType' => 'number',
			)
		);

		// Get the total submissions
		$total = 0;

		foreach($dataTable['rows'] as $row)
		{
			$total = $total + $row[1];
		}

		$this->returnJson(array(
			'dataTable' => $dataTable,
			'total' => $total,
			'totalHtml' => $total,

			'formats' => ChartHelper::getFormats(),
			'orientation' => craft()->locale->getOrientation(),
			'scale' => $intervalUnit,
			'localeDefinition' => array(),
		));
	}
}
