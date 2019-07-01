<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\db\Migration;

/**
 * m190628_000000_update_default_to_pro_edition migration.
 */
class m190628_000000_update_default_to_pro_edition extends Migration
{
    /**
     * @return bool
     * @throws \Throwable
     */
    public function safeUp(): bool
    {
        Craft::$app->getPlugins()->switchEdition('sprout-forms', SproutForms::EDITION_PRO);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190628_000000_update_default_to_pro_edition cannot be reverted.\n";
        return false;
    }
}
