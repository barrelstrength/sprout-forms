<?php

namespace barrelstrength\sproutforms\migrations;

use Craft;
use craft\db\Migration;
use craft\services\Plugins;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 *
 * @property null|int $fakeFieldLayoutId
 */
class m191119_000000_set_default_recaptcha_css extends Migration
{

    /**
     * @return bool|void
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     * @throws \yii\base\NotSupportedException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function safeUp()
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $currentSettings = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings');
        $recaptchaSettings = $currentSettings['captchaSettings']['sproutformsgooglerecaptcha-googlerecaptcha'] ?? [];

        $recaptchaSettings['addRequiredHtml'] = 0;
        $currentSettings['captchaSettings']['sproutformsgooglerecaptcha-googlerecaptcha'] = $recaptchaSettings;

        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $currentSettings);
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191119_000000_set_default_recaptcha_css cannot be reverted.\n";

        return false;
    }
}
