<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutforms\fields\formfields\SingleLine;
use barrelstrength\sproutforms\fields\formfields\Paragraph;
use craft\fields\PlainText;
use craft\helpers\Json;

/**
 * m180314_161522_sproutforms_plaintext_fields migration.
 */
class m180314_161522_sproutforms_plaintext_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // PlainText - Update to single line or paragraph
        $plainTextFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => PlainText::class])
            ->andWhere(['like', 'context', 'sproutForms:'])
            ->all();

        foreach ($plainTextFields as $plainTextField) {
            $newSettings = [
                'placeholder' => '',
                'charLimit' => null,
                'columnType' => 'string'
            ];

            $settings = Json::decode($plainTextField['settings']);
            $newType = SingleLine::class;

            if ($settings['multiline']) {
                $newType = Paragraph::class;
                $newSettings['columnType'] = 'text';
                $newSettings['initialRows'] = $settings['initialRows'];
            }

            $settingsAsJson = Json::encode($newSettings);

            $this->update('{{%fields}}', ['type' => $newType, 'settings' => $settingsAsJson], ['id' => $plainTextField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161522_sproutforms_plaintext_fields cannot be reverted.\n";
        return false;
    }
}
