<?php

namespace barrelstrength\sproutforms\integrations\sproutreports\datasources;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutbase\app\reports\elements\Report;
use Craft;
use barrelstrength\sproutbase\app\reports\base\DataSource;
use craft\db\Query;
use craft\helpers\DateTimeHelper;

/**
 * Class EntriesDataSource
 *
 * @package barrelstrength\sproutforms\integrations\sproutreports\datasources
 */
class EntriesDataSource extends DataSource
{
    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-forms', 'Sprout Forms Entries');
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return Craft::t('sprout-forms', 'Query form entries');
    }

    public function getResults(Report $report, array $settings = [])
    {
        $startDate = null;
        $endDate = null;
        $formId = null;

        if (!empty($report->getSetting('startDate'))) {
            $startDateValue = $report->getSetting('startDate');
            $startDateValue = (array)$startDateValue;

            $startDate = DateTimeHelper::toIso8601($startDateValue);

            $startDate = DateTimeHelper::toDateTime($startDate);
        }

        if (!empty($report->getSetting('endDate'))) {
            $endDateValue = $report->getSetting('endDate');
            $endDateValue = (array)$endDateValue;

            $endDate = DateTimeHelper::toIso8601($endDateValue);

            $endDate = DateTimeHelper::toDateTime($endDate);
        }

        if (!empty($report->getSetting('formId'))) {
            $formId = $report->getSetting('formId');
        }

        if (count($settings)) {
            if (isset($settings['startDate'])) {
                $startDate = DateTimeHelper::toDateTime($settings['startDate']);
            }

            if (isset($settings['endDate'])) {
                $endDate = DateTimeHelper::toDateTime($settings['endDate']);
            }

            $formId = $settings['formId'];
        }

        $results = [];

        if ($formId) {
            $form = SproutForms::$app->forms->getFormById($formId);

            if (!$form) {
                return null;
            }

            $contentTable = $form->contentTable;

            $query = new Query();

            $formQuery = $query
                ->select('*')
                ->from($contentTable.' AS entries');

            if ($startDate && $endDate) {
                $formQuery->where('entries.dateCreated > :startDate', [':startDate' => $startDate->format('Y-m-d H:i:s')]);
                $formQuery->andWhere('entries.dateCreated < :endDate', [':endDate' => $endDate->format('Y-m-d H:i:s')]);
            }

            $results = $formQuery->all();

            if ($results) {
                foreach ($results as $key => $result) {
                    unset($result['elementId']);
                    unset($result['siteId']);
                    unset($result['uid']);

                    $results[$key] = $result;
                }
            }
        }

        return $results;
    }

    /**
     * @param array $settings
     *
     * @return null|string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml(array $settings = [])
    {
        $forms = Form::find()->limit(null)->orderBy('name')->all();

        if (empty($settings)) {
            $settings = (array)$this->report->getSettings();
        }

        $formOptions = [];

        foreach ($forms as $form) {
            $formOptions[] = [
                'label' => $form->name,
                'value' => $form->id
            ];
        }

        // @todo Determine sensible default start and end date based on Order data
        $defaultStartDate = null;
        $defaultEndDate = null;

        if (count($settings)) {
            if (isset($settings['startDate'])) {
                $startDateValue = (array)$settings['startDate'];

                $settings['startDate'] = DateTimeHelper::toDateTime($startDateValue);
            }

            if (isset($settings['endDate'])) {
                $endDateValue = (array)$settings['endDate'];

                $settings['endDate'] = DateTimeHelper::toDateTime($endDateValue);
            }
        }

        return Craft::$app->getView()->renderTemplate('sprout-forms/_integrations/sproutreports/datasources/EntriesDataSource/settings', [
            'formOptions' => $formOptions,
            'defaultStartDate' => new \DateTime($defaultStartDate),
            'defaultEndDate' => new \DateTime($defaultEndDate),
            'options' => $settings
        ]);
    }

    /**
     * @inheritdoc
     */
    public function prepSettings(array $settings)
    {
        // Convert date strings to DateTime
        $settings['startDate'] = DateTimeHelper::toDateTime($settings['startDate']) ?? null;
        $settings['endDate'] = DateTimeHelper::toDateTime($settings['endDate']) ?? null;

        return $settings;
    }
}
