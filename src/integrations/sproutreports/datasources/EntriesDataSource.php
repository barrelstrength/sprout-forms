<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\integrations\sproutreports\datasources;

use barrelstrength\sproutbasereports\base\DataSource;
use barrelstrength\sproutbasereports\elements\Report;
use barrelstrength\sproutbasereports\SproutBaseReports;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\db\Query;
use craft\elements\db\ElementQueryInterface;
use craft\fields\data\MultiOptionsFieldData;
use craft\helpers\DateTimeHelper;
use DateTime;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class EntriesDataSource
 *
 * @package barrelstrength\sproutforms\integrations\sproutreports\datasources
 *
 * @property null|string $description
 */
class EntriesDataSource extends DataSource
{
    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Entries (Sprout Forms)');
    }

    /**
     * @return null|string
     */
    public function getDescription(): string
    {
        return Craft::t('sprout-forms', 'Query form entries');
    }

    /**
     * @inheritDoc
     */
    public function getViewContext(): string
    {
        return 'sprout-forms';
    }

    /**
     * @inheritDoc
     */
    public function getViewContextLabel(): string
    {
        return 'Forms Reports';
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getResults(Report $report, array $settings = []): array
    {
        $startDate = null;
        $endDate = null;
        $formId = null;

        $startEndDate = $report->getStartEndDate();
        $startDate = $startEndDate->getStartDate();
        $endDate = $startEndDate->getEndDate();

        $rows = [];

        $formId = $report->getSetting('formId');
        $form = SproutForms::$app->forms->getFormById($formId);

        $entryStatusIds = $report->getSetting('entryStatusIds');

        if (!$form) {
            return null;
        }

        $contentTable = $form->contentTable;

        $query = new Query();

        $formQuery = $query
            ->select([
                '[[elements.id]] AS "elementId"',
                '[[elements_sites.siteId]] AS "siteId"',
                '[[formcontenttable.title]] AS "title"',
                '[[entrystatuses.name]] AS "entryStatusName"',
                '[[entries.ipAddress]] AS "ipAddress"',
                '[[entries.referrer]] AS "referrer"',
                '[[entries.userAgent]] AS "userAgent"',
                '[[entries.dateCreated]] AS "dateCreated"',
                '[[entries.dateUpdated]] AS "dateUpdated"'
            ])
            ->from($contentTable.' AS formcontenttable')
            ->innerJoin('{{%elements}} elements', '[[formcontenttable.elementId]] = [[elements.id]]')
            ->innerJoin('{{%elements_sites}} elements_sites', '[[elements_sites.elementId]] = [[elements.id]]')
            ->innerJoin('{{%sproutforms_entries}} entries', '[[entries.id]] = [[elements.id]]')
            ->innerJoin('{{%sproutforms_entrystatuses}} entrystatuses', '[[entries.statusId]] = [[entrystatuses.id]]')
            ->where(['elements.dateDeleted' => null]);

        if ($startDate && $endDate) {
            $formQuery->andWhere('[[formcontenttable.dateCreated]] > :startDate', [
                ':startDate' => $startDate->format('Y-m-d H:i:s')
            ]);
            $formQuery->andWhere('[[formcontenttable.dateCreated]] < :endDate', [
                ':endDate' => $endDate->format('Y-m-d H:i:s')
            ]);
        }

        if (count($entryStatusIds)) {
            $formQuery->andWhere(['entries.statusId' => $entryStatusIds]);
        }

        $results = $formQuery->all();

        if (!$results) {
            return $rows;
        }

        foreach ($results as $key => $result) {
            $elementId = $result['elementId'];
            $rows[$key]['elementId'] = $elementId;
            $rows[$key]['siteId'] = $result['siteId'];
            $rows[$key]['title'] = $result['title'];
            $rows[$key]['status'] = $result['entryStatusName'];
            $rows[$key]['ipAddress'] = $result['ipAddress'];
            $rows[$key]['referrer'] = $result['referrer'];
            $rows[$key]['userAgent'] = $result['userAgent'];
            $rows[$key]['dateCreated'] = $result['dateCreated'];
            $rows[$key]['dateUpdated'] = $result['dateUpdated'];

            $entry = Craft::$app->getElements()->getElementById($elementId, Entry::class);

            if ($entry === null) {
                $fields = [];
            } else {
                $fields = $entry->getFieldValues();
            }

            if (count($fields) <= 0) {
                continue;
            }

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
                    foreach ($options as $option) {
                        if ($option->selected) {
                            $selectedOptions[] = '"'.$option->value.'"';
                        }
                    }

                    $value = '';

                    if (count($selectedOptions)) {
                        $value = implode(', ', $selectedOptions);
                    }
                } else {
                    $value = $field;
                }

                $fieldHandleKey = 'field_'.$handle;
                $rows[$key][$fieldHandleKey] = $value;
            }
        }

        return $rows;
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
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

        $dateRanges = SproutBaseReports::$app->reports->getDateRanges(false);

        $entryStatusOptions = [];
        $defaultSelectedEntryStatuses = [];

        $entryStatuses = SproutForms::$app->entryStatuses->getAllEntryStatuses();
        $spamStatusId = SproutForms::$app->entryStatuses->getSpamStatusId();

        foreach ($entryStatuses as $entryStatus) {
            $entryStatusOptions[$entryStatus->id]['label'] = $entryStatus->name;
            $entryStatusOptions[$entryStatus->id]['value'] = $entryStatus->id;

            if ($entryStatus->id !== $spamStatusId) {
                $defaultSelectedEntryStatuses[] = $entryStatus->id;
            }
        }

        return Craft::$app->getView()->renderTemplate('sprout-forms/_integrations/sproutreports/datasources/EntriesDataSource/settings', [
            'formOptions' => $formOptions,
            'defaultStartDate' => new DateTime($defaultStartDate),
            'defaultEndDate' => new DateTime($defaultEndDate),
            'dateRanges' => $dateRanges,
            'options' => $settings,
            'entryStatusOptions' => $entryStatusOptions,
            'defaultSelectedEntryStatuses' => $defaultSelectedEntryStatuses
        ]);
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function prepSettings(array $settings)
    {
        // Convert date strings to DateTime
        $settings['startDate'] = DateTimeHelper::toDateTime($settings['startDate']) ?: null;
        $settings['endDate'] = DateTimeHelper::toDateTime($settings['endDate']) ?: null;

        return $settings;
    }
}
