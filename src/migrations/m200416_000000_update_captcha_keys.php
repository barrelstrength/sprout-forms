<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\ProjectConfig;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use craft\services\Plugins;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class m200416_000000_update_captcha_keys extends Migration
{
    /**
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function safeUp(): bool
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $schemaVersion = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.schemaVersion', true);
        if (version_compare($schemaVersion, '3.9.1', '>=')) {
            return true;
        }

        $pluginSettings = Craft::$app->getProjectConfig()->get(Plugins::CONFIG_PLUGINS_KEY.'.sprout-forms.settings');

        /** @noinspection ClassConstantCanBeUsedInspection */
        $captchaMap = [
            [
                'oldKey' => 'sproutforms-duplicatecaptcha',
                'newKey' => 'barrelstrength\sproutforms\captchas\DuplicateCaptcha'
            ],
            [
                'oldKey' => 'sproutforms-javascriptcaptcha',
                'newKey' => 'barrelstrength\sproutforms\captchas\JavascriptCaptcha'
            ],
            [
                'oldKey' => 'sproutforms-honeypotcaptcha',
                'newKey' => 'barrelstrength\sproutforms\captchas\HoneypotCaptcha'
            ],
            [
                'oldKey' => 'sproutformsgooglerecaptcha-googlerecaptcha',
                'newKey' => 'barrelstrength\sproutformsgooglerecaptcha\integrations\sproutforms\captchas\GoogleRecaptcha'
            ]
        ];

        $captchaSettings = ProjectConfig::unpackAssociativeArray($pluginSettings['captchaSettings'] ?? []);

        $newCaptchaSettings = [];

        foreach ($captchaMap as $captcha) {
            $oldCaptchaSettings = $captchaSettings[$captcha['oldKey']] ?? null;

            if (!$oldCaptchaSettings) {
                continue;
            }

            $newCaptchaSettings[$captcha['newKey']] = $oldCaptchaSettings;
        }

        $pluginSettings['captchaSettings'] = ProjectConfigHelper::packAssociativeArray($newCaptchaSettings) ?? [];

        Craft::$app->getProjectConfig()->set(Plugins::CONFIG_PLUGINS_KEY.'.sprout-forms.settings', $pluginSettings, 'Updated Sprout Forms Captcha settings.');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200416_000000_update_captcha_keys cannot be reverted.\n";

        return false;
    }
}
