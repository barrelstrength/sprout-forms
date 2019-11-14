<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\models\FieldLayout;
use craft\models\FieldLayoutTab;
use yii\base\NotSupportedException;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m191113_000000_fix_duplicate_forms extends Migration
{
    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function safeUp()
    {
        $forms = (new Query())
            ->select(['id'])
            ->from(['{{%sproutforms_forms}}'])
            ->all();

        $fakeFieldLayoutId = $this->getFakeFieldLayoutId();

        foreach ($forms as $form) {
            $formElement = SproutForms::$app->forms->getFormById($form['id']);
            if ($formElement === null){
                continue;
            }
            $contentTable = $formElement->getContentTable();
            $formFields = $formElement->getFields();
            // All the fields columns does not exists
            $missingFields = 0;
            foreach ($formFields as $formField) {
                $fieldColumn = 'field_'.$formField->handle;
                if (!$this->db->columnExists($contentTable, $fieldColumn)) {
                    $missingFields++;
                }
            }

            if ($missingFields === count($formFields) && $missingFields > 0){
                Craft::info("Updating corrupted duplicated form field layout id: ".$formElement->fieldLayoutId. " to: ".$fakeFieldLayoutId, __METHOD__);
                $this->update('{{%sproutforms_forms}}', ['fieldLayoutId' => $fakeFieldLayoutId], ['id' => $formElement->id], [], false);
            }
        }
    }

    /**
     * @return int|null
     * @throws \yii\base\Exception
     */
    private function getFakeFieldLayoutId()
    {
        $tabs = [];
        $tab = new FieldLayoutTab();
        $tab->name = urldecode("SproutFormsTabFake");
        $tab->sortOrder = "888";
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
