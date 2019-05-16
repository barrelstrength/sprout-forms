<?php

namespace barrelstrength\sproutforms\integrations\sproutreports\datasources;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutbasereports\elements\Report;
use Craft;
use barrelstrength\sproutbasereports\base\DataSource;
use craft\db\Query;
use craft\fields\data\MultiOptionsFieldData;
use craft\helpers\DateTimeHelper;
use barrelstrength\sproutforms\elements\Entry;
use craft\elements\db\ElementQueryInterface;

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
    public function getName(): string
    {
        return Craft::t('sprout-forms', 'Sprout Forms Entries');
    }

    /**
     * @return null|string
     */
    public function getDescription(): string
    {
        return Craft::t('sprout-forms', 'Query form entries');
    }

    /**
     * @inheritdoc
     *
     * @throws \Exception
     */
    public function getResults(Report $report, array $settings = []): array
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

        $rows = [];
        if ($formId) {
            $form = SproutForms::$app->forms->getFormById($formId);

            if (!$form) {
                return null;
            }

            $contentTable = $form->contentTable;

            $query = new Query();

            $formQuery = $query
                ->select('*')
                ->from($contentTable.' AS formcontenttable')
                ->innerJoin('{{%elements}}', '[[formcontenttable.elementId]] = [[elements.id]]')
                ->where(['elements.dateDeleted' => null]);

            if ($startDate && $endDate) {
                $formQuery->andWhere('[[formcontenttable.dateCreated]] > :startDate', [
                    ':startDate' => $startDate->format('Y-m-d H:i:s')
                ]);
                $formQuery->andWhere('[[formcontenttable.dateCreated]] < :endDate', [
                    ':endDate' => $endDate->format('Y-m-d H:i:s')
                ]);
            }

            $results = $formQuery->all();

            if ($results) {
                foreach ($results as $key => $result) {

                    $elementId = $result['elementId'];
                    $rows[$key]['id']          = $result['id'];
                    $rows[$key]['elementId']   = $elementId;
                    $rows[$key]['siteId']      = $result['siteId'];
                    $rows[$key]['title']       = $result['title'];
                    $rows[$key]['dateCreated'] = $result['dateCreated'];
                    $rows[$key]['dateUpdated'] = $result['dateUpdated'];

                    $entry = Craft::$app->getElements()->getElementById($elementId, Entry::class);

                    if ($entry === null) {
                        $fields = [];
                    } else {
                        $fields = $entry->getFieldValues();
                    }

                    if (count($fields) > 0) {
                        foreach ($fields as $handle => $field) {
                            if ($field instanceof ElementQueryInterface) {

                                $entries = $field->all();
                                $titles = [];
                                if (!empty($entries)) {
                                    foreach ($entries as $entry) {
                                        $titles[] = '"'.$entry->title.'"';
                                    }
                                }
                                $value = '';

                                if (!empty($titles)) {
                                    $value = implode(', ', $titles);
                                }
                            } else if ($field instanceof MultiOptionsFieldData) {
                                $options = $field->getOptions();

                                $selectedOptions = [];
                                foreach ($options AS $option)
                                {
                                    if ($option->selected) {
                                        $selectedOptions[] = '"'.$option->selected.'"';
                                    }
                                }

                                $value = '';

                                if (count($selectedOptions)) {
                                    $value = implode(', ', $selectedOptions);
                                }
                            } else {
                                $value = $field;
                            }

                            $fieldHandleKey = 'field_' . $handle;
                            $rows[$key][$fieldHandleKey] = $value;
                        }
                    }
                }
            }
        }

        return $rows;
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml(array $settings = [])
    {
        /** @var Form[] $forms */
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
     *
     * @throws \Exception
     */
    public function prepSettings(array $settings)
    {
        // Convert date strings to DateTime
        $settings['startDate'] = DateTimeHelper::toDateTime($settings['startDate']) ?? null;
        $settings['endDate'] = DateTimeHelper::toDateTime($settings['endDate']) ?? null;

        return $settings;
    }
}
