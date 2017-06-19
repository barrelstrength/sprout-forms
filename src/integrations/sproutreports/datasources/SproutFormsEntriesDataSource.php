<?php
namespace barrelstrength\sproutforms\integrations\sproutreports\datasources;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutreports\models\Report;
use Craft;
use barrelstrength\sproutcore\integrations\sproutreports\contracts\BaseDataSource;
use craft\db\Query;
use craft\helpers\DateTimeHelper;

/**
 * Class SproutFormsEntriesDataSource
 *
 * @package Craft
 */
class SproutFormsEntriesDataSource extends BaseDataSource
{
	public function getName()
	{
		return SproutForms::t('Sprout Forms Entries');
	}

	/**
	 * @return null|string
	 */
	public function getDescription()
	{
		return SproutForms::t('Query form entries');
	}

	public function getResults(Report &$report, $options = array())
	{
		$startDate = DateTimeHelper::toDateTime($report->getOption('startDate'));
		$endDate   = DateTimeHelper::toDateTime($report->getOption('endDate'));

		if (count($options))
		{
			if (isset($options['startDate']))
			{
				$startDate = DateTimeHelper::toDateTime($options['startDate']);
			}

			if (isset($options['endDate']))
			{
				$endDate = DateTimeHelper::toDateTime($options['endDate']);
			}
		}

		$formId = $report->getOption('formId');

		$form = SproutForms::$app->forms->getFormById($formId);

		if (!$form)
		{
			return null;
		}

		$contentTable = $form->contentTable;

		$query = new Query();

		$formQuery = $query
			->select('*')
			->from($contentTable . ' AS entries');

		if ($startDate && $endDate)
		{
			$formQuery->where('entries.dateCreated > :startDate', array(':startDate' => $startDate->format('Y-m-d H:i:s')));
			$formQuery->andWhere('entries.dateCreated < :endDate', array(':endDate' => $endDate->format('Y-m-d H:i:s')));
		}

		return $formQuery->all();
	}

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	public function getOptionsHtml(array $options = array())
	{
		$forms = Form::find()->limit(null)->orderBy('name');

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
