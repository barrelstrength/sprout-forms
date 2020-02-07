<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\controllers;

use barrelstrength\sproutforms\elements\db\EntryQuery;
use Craft;
use craft\controllers\ElementIndexesController;
use craft\helpers\ChartHelper;
use craft\helpers\DateTimeHelper;
use Exception;
use yii\base\Response;
use yii\web\BadRequestHttpException;

/**
 * Class ChartsController
 */
class ChartsController extends ElementIndexesController
{
    /**
     * Returns the data needed to display a Submissions chart.
     *
     * @return Response
     * @throws \yii\base\Exception
     * @throws BadRequestHttpException
     * @throws Exception
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

        /** @var EntryQuery $query */
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
        $dataTable = ChartHelper::getRunChartDataFromQuery($query, $startDate, $endDate, 'sproutforms_entries.dateCreated', 'count', '*', [
            'intervalUnit' => $intervalUnit,
            'valueLabel' => Craft::t('sprout-forms', 'Submissions'),
            'valueType' => 'number',
        ]);

        // Get the total submissions
        $total = 0;

        foreach ($dataTable['rows'] as $row) {
            $total += $row[1];
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
