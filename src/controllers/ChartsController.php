<?php

namespace barrelstrength\sproutforms\controllers;

use Craft;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\elements\Form as FormElement;
use craft\controllers\ElementIndexesController;
use craft\db\Query;
use craft\helpers\ChartHelper;
use craft\helpers\DateTimeHelper;
use yii\web\NotFoundHttpException;
use yii\base\Response;

/**
 * Class ChartsController
 */
class ChartsController extends ElementIndexesController
{
    /**
     * Returns the data needed to display a Submissions chart.
     *
     * @return void
     */
    public function actionGetEntriesData(): Response
    {

        // Required for Dashboard widget, unnecessary for Entries Index view
        $formId = Craft::$app->request->getBodyParam('formId');

        $startDateParam = Craft::$app->request->getRequiredBodyParam('startDate');
        $endDateParam = Craft::$app->request->getRequiredBodyParam('endDate');

        $startDate = DateTimeHelper::toDateTime($startDateParam);
        $endDate = DateTimeHelper::toDateTime($endDateParam);
        $endDate->modify('+1 day');

        $intervalUnit = ChartHelper::getRunChartIntervalUnit($startDate, $endDate);

        // Prep the query
        $query = $this->getElementQuery();
        $query->limit = null;

        // Don't use the search
        #$query->search = null;

        $query->select(['COUNT(*) as [[value]]']);

        if ($formId != 0) {
            $query->andWhere('forms.id = :formId',
                [':formId' => $formId]
            );
        }

        // Get the chart data table
        $dataTable = ChartHelper::getRunChartDataFromQuery($query, $startDate, $endDate,
            'sproutforms_entries.dateCreated',
            [
                'intervalUnit' => $intervalUnit,
                'valueLabel' => SproutForms::t('Submissions'),
                'valueType' => 'number',
            ]
        );

        // Get the total submissions
        $total = 0;

        foreach ($dataTable['rows'] as $row) {
            $total = $total + $row[1];
        }

        return $this->asJson([
            'dataTable' => $dataTable,
            'total' => $total,
            'totalHtml' => $total,

            'formats' => ChartHelper::formats(),
            'orientation' => Craft::$app->locale->getOrientation(),
            'scale' => $intervalUnit,
            'localeDefinition' => [],
        ]);
    }
}
