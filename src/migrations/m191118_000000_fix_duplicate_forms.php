<?php /** @noinspection ClassConstantCanBeUsedInspection */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use yii\base\Exception;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 *
 * @property null|int $emptyFieldLayoutId
 * @property null|int $fakeFieldLayoutId
 */
class m191118_000000_fix_duplicate_forms extends Migration
{
    /**
     * @return bool|void
     * @throws Exception
     */
    public function safeUp()
    {
        $forms = (new Query())
            ->select(['id', 'handle', 'fieldLayoutId'])
            ->from(['{{%sproutforms_forms}}'])
            ->all();

        $formFieldLayoutIds = [];

        foreach ($forms as $form) {
            $formFieldLayoutIds[] = $form['fieldLayoutId'];
        }

        $fieldLayoutFields = (new Query())
            ->select(['id', 'layoutId', 'fieldId'])
            ->from(['{{%fieldlayoutfields}}'])
            ->where(['in', 'layoutId', $formFieldLayoutIds])
            ->all();

        $fieldLayoutFieldsIndexedByLayoutId = [];

        foreach ($fieldLayoutFields as $fieldLayoutField) {
            $fieldLayoutFieldsIndexedByLayoutId[$fieldLayoutField['layoutId']][] = $fieldLayoutField['fieldId'];
        }

        foreach ($forms as $form) {
            $contentTable = '{{%sproutformscontent_'.$form['handle'].'}}';

            // Make sure we skip soft deleted Form Elements and any other funny stuff
            if (!$this->db->tableExists($contentTable)) {
                continue;
            }

            $possibleDuplicateForm = false;
            $possibleDuplicateFieldLayoutId = $form['fieldLayoutId'];

            // Copy our global fieldLayoutFields array so we can unset our current layout from the array
            $localFieldLayoutFieldsIndexedByLayoutId = $fieldLayoutFieldsIndexedByLayoutId;

            // Get an array all Field IDs associated with this Field Layout ID
            $possibleDuplicateFieldLayoutFieldIds = $localFieldLayoutFieldsIndexedByLayoutId[$possibleDuplicateFieldLayoutId] ?? [];

            // Remove our current Field Layout ID from the Field Layouts we want to check
            unset($localFieldLayoutFieldsIndexedByLayoutId[$possibleDuplicateFieldLayoutId]);

            // Check a list of our current Field Layout Field IDs against each other Field Layout
            // If we find any matches that suggests we Field IDs from this layout assigned to multiple
            // Field Layouts that we need to resolve.
            foreach ($localFieldLayoutFieldsIndexedByLayoutId as $fieldLayoutFieldIds) {
                $duplicatedFieldIds = array_intersect($possibleDuplicateFieldLayoutFieldIds, $fieldLayoutFieldIds);
                if (count($duplicatedFieldIds)) {
                    $possibleDuplicateForm = true;
                    break;
                }
            }

            if (!$possibleDuplicateForm) {
                continue;
            }

            // If we have a Form that is using duplicate fields, we don't know if it's the original
            // Form or the duplicated Form so we need to check if the number of Fields in the
            // craft_fields column that match this forms Field Layout ID are the same as the Fields
            // found for this form in the craft_fieldlayoutfields column. If we have a mismatch,
            // we conclude the Save As New Form bug created a duplicate without the correct fields
            // and any additional fields that were created did not fix the original problem where
            // fields may still be related to another form.
            $fieldIdsMatchedByFieldsTableContextColumn = (new Query())
                ->select(['id', 'handle', 'type', 'settings'])
                ->from(['{{%fields}}'])
                ->where(['context' => 'sproutForms:'.$form['id']])
                ->all();

            // If we have the right number of fields, everything's okay
            if (count($possibleDuplicateFieldLayoutFieldIds) === count($fieldIdsMatchedByFieldsTableContextColumn)) {
                continue;
            }

            // If we don't have the right number of fields:

            // Generate an empty Field Layout
            $emptyFieldLayoutId = $this->getEmptyFieldLayoutId();

            $formId = (int)$form['id'];

            // Assign a new, empty field layout to the corrupted duplicate form field layout
            $this->update('{{%sproutforms_forms}}', ['fieldLayoutId' => $emptyFieldLayoutId], ['id' => $formId], [], false);

            Craft::info('Updated corrupted duplicate form field layout id: '.$form['fieldLayoutId'].' to: '.$emptyFieldLayoutId, __METHOD__);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191113_000000_fix_duplicate_forms cannot be reverted.\n";

        return false;
    }

    /**
     * @return int|null
     * @throws Exception
     */
    private function getEmptyFieldLayoutId()
    {
        $tabs = [];
        $tab = new FieldLayoutTab();
        $tab->name = urldecode('Page 1');
        $tab->sortOrder = '888';
        $tab->setFields([]);

        $tabs[] = $tab;

        $layout = new FieldLayout();
        $layout->setTabs($tabs);
        $layout->setFields([]);
        $layout->type = 'barrelstrength\sproutforms\elements\Form';

        Craft::$app->getFields()->saveLayout($layout);

        return $layout->id;
    }
}
