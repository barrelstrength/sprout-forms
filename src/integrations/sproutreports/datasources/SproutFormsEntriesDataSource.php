<?php
namespace barrelstrength\sproutforms\integrations\sproutreports\datasources;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutcore\models\sproutreports\Report;
use Craft;
use barrelstrength\sproutcore\contracts\sproutreports\BaseDataSource;
use craft\db\Query;
use craft\helpers\DateTimeHelper;

/**
 * Class SproutFormsEntriesDataSource
 *
 * @package barrelstrength\sproutforms\integrations\sproutreports\datasources
 */
class SproutFormsEntriesDataSource extends BaseDataSource
{
	public function getName()
	{
		return SproutForms::t('Sprout Forms Entries');
	}

	/**
	 * @return string
	 */
	public function getPluginName()
	{
		$plugin = Craft::$app->getPlugins()->getPlugin('sproutForms');

		return $plugin->name;
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
		$startDate = null;
		$endDate   = null;
		$formId    = null;

		if (!empty($report->getOption('startDate')))
		{
			$startDateValue = $report->getOption('startDate');
			$startDateValue = (array) $startDateValue;

			$startDate = DateTimeHelper::toIso8601($startDateValue);

			$startDate = DateTimeHelper::toDateTime($startDate);
		}

		if (!empty($report->getOption('endDate')))
		{
			$endDateValue = $report->getOption('endDate');
			$endDateValue = (array) $endDateValue;

			$endDate = DateTimeHelper::toIso8601($endDateValue);

			$endDate = DateTimeHelper::toDateTime($endDate);
		}

		if (!empty($report->getOption('formId')))
		{
			$formId = $report->getOption('formId');
		}

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

			$formId = $options['formId'];
		}

		$results = array();

		if ($formId)
		{
			$form = SproutForms::$app->forms->getFormById($formId);

			if (!$form)
			{
				return null;
			}

			$contentTable = $form->contentTable;

			$query = new Query();

			$formQuery = $query
				->select("*")
				->from($contentTable . ' AS entries');

			if ($startDate && $endDate)
			{
				$formQuery->where("entries.dateCreated > :startDate", array(':startDate' => $startDate->format('Y-m-d H:i:s')));
				$formQuery->andWhere('entries.dateCreated < :endDate', array(':endDate' => $endDate->format('Y-m-d H:i:s')));
			}

			$results = $formQuery->all();
		}

		return $results;
	}

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	public function getOptionsHtml(array $options = array())
	{
		$forms = Form::find()->limit(null)->orderBy('name')->all();

		$formOptions = array();

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
				$options['startDate'] = DateTimeHelper::toDateTime($options['startDate']);
			}

			if (isset($options['endDate']))
			{
				$options['endDate'] = DateTimeHelper::toDateTime($options['endDate']);
			}

		}
		else
		{
			$options['startDate'] = null;
			$options['endDate']   = null;

			if ($this->report->getOption('startDate'))
			{
				$startDateValue = $this->report->getOption('startDate');
				$startDateValue = (array) $startDateValue;

				$startDate = DateTimeHelper::toIso8601($startDateValue);

				$options['startDate'] = DateTimeHelper::toDateTime($startDate);
			}

			if ($this->report->getOption('endDate'))
			{
				$endDateValue = $this->report->getOption('endDate');
				$endDateValue = (array) $endDateValue;

				$endDate = DateTimeHelper::toIso8601($endDateValue);

				$options['endDate'] = DateTimeHelper::toDateTime($endDate);
			}
		}

		return Craft::$app->getView()->renderTemplate('sproutforms/_reports/options/entries', array(
			'formOptions'      => $formOptions,
			'defaultStartDate' => new \DateTime($defaultStartDate),
			'defaultEndDate'   => new \DateTime($defaultEndDate),
			'options'          => $options
		));
	}
}
