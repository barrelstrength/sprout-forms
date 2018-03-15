<?php

namespace barrelstrength\sproutforms\migrations;

use craft\db\Migration;
use barrelstrength\sproutfields\fields\Notes;
use craft\db\Query;
use barrelstrength\sproutforms\integrations\sproutforms\fields\SingleLine;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Paragraph;
use craft\fields\PlainText;

/**
 * m180314_161522_sproutforms_plaintext_fields migration.
 */
class m180314_161522_sproutforms_plaintext_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // PlainText - Update to single line or paragraph
        $plainTextFields = (new Query())
            ->select(['id', 'handle', 'settings'])
            ->from(['{{%fields}}'])
            ->where(['type' => PlainText::class])
            ->andWhere('context LIKE "%sproutForms:%"')
            ->all();

        $newSettigns = [
            'placeholder' => '',
            'charLimit'=> null,
            'columnType' => 'string'
        ];

        foreach ($plainTextFields as $plainTextField) {
            $settings = json_decode($plainTextField['settings'], true);
            $newType = SingleLine::class;

            if ($settings['multiline']){
                $newType = Paragraph::class;
                $newSettigns['columnType'] = 'text';
                $newSettigns['initialRows'] = $settings['initialRows'];
            }

            $settingsAsJson = json_encode($newSettigns);

            $this->update('{{%fields}}', ['type' => $newType, 'settings' => $settingsAsJson], ['id' => $plainTextField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180314_161522_sproutforms_plaintext_fields cannot be reverted.\n";
        return false;
    }
}
