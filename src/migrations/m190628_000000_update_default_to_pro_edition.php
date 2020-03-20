<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\db\Migration;
use Throwable;

/**
 * m190628_000000_update_default_to_pro_edition migration.
 */
class m190628_000000_update_default_to_pro_edition extends Migration
{
    /**
     * @return bool
     * @throws Throwable
     */
    public function safeUp(): bool
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.sprout-forms.schemaVersion', true);
        if (version_compare($schemaVersion, '3.2.1', '>=')) {
            return true;
        }

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
