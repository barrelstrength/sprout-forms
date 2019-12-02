<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\fields\Entries;
use barrelstrength\sproutforms\fields\formfields\Categories;
use barrelstrength\sproutforms\fields\formfields\FileUpload;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 *
 * @property null|int $fakeFieldLayoutId
 */
class m191118_000000_fix_duplicate_forms extends Migration
{
    /**
     * @return bool|void
     * @throws NotSupportedException
     * @throws Exception
     * @throws InvalidConfigException
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

        $fieldLayoutMapped = [];

        foreach ($fieldLayoutFields as $fieldLayoutField) {
            $fieldLayoutMapped[$fieldLayoutField['layoutId']][] = $fieldLayoutField['fieldId'];
        }

        foreach ($forms as $form) {
            $contentTable = '{{%sproutformscontent_'.$form['handle'].'}}';

            if (!$this->db->tableExists($contentTable)){
                continue;
            }

            $possibleFieldLayoutId = $form['fieldLayoutId'];
            $candidate = false;
            $localFieldLayoutMapped = $fieldLayoutMapped;

            // Let's get all the fields that are used from other fieldLayout
            $possibleFieldLayoutFieldsIds = $localFieldLayoutMapped[$possibleFieldLayoutId] ?? [];
            unset($localFieldLayoutMapped[$possibleFieldLayoutId]);

            foreach ($localFieldLayoutMapped as $item) {
                $duplicatedFieldIds = array_intersect($possibleFieldLayoutFieldsIds, $item);
                if (count($duplicatedFieldIds)){
                    $candidate = true;
                    break;
                }
            }

            if (!$candidate){
                break;
            }

            $formFields = (new Query())
                ->select(['id', 'handle', 'type', 'settings'])
                ->from(['{{%fields}}'])
                ->where(['context' => 'sproutForms:'.$form['id']])
                ->all();

            if (count($possibleFieldLayoutFieldsIds) != count($formFields)){
                $fakeFieldLayoutId = $this->getFakeFieldLayoutId();
                Craft::info('Updating corrupted duplicated form field layout id: '.$form['fieldLayoutId'].' to: '.$fakeFieldLayoutId, __METHOD__);
                $this->update('{{%sproutforms_forms}}', ['fieldLayoutId' => $fakeFieldLayoutId], ['id' => $form['id']], [], false);
            }
        }
    }

    /**
     * @return int|null
     * @throws Exception
     */
    private function getFakeFieldLayoutId()
    {
        $tabs = [];
        $tab = new FieldLayoutTab();
        $tab->name = urldecode('Tab 1');
        $tab->sortOrder = '888';
        $tab->setFields([]);

        $tabs[] = $tab;

        $layout = new FieldLayout();
        $layout->setTabs($tabs);
        $layout->setFields([]);
        $layout->type = Form::class;

        Craft::$app->getFields()->saveLayout($layout);

        return $layout->id;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191113_000000_fix_duplicate_forms cannot be reverted.\n";

        return false;
    }
}
