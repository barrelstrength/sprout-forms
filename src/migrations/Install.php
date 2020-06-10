<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutbase\SproutBase;
use craft\db\Migration;
use ReflectionException;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class Install extends Migration
{
    /**
     * @return bool
     * @throws ErrorException
     * @throws NotSupportedException
     * @throws ReflectionException
     * @throws ServerErrorHttpException
     * @throws Exception
     */
    public function safeUp(): bool
    {
        SproutBase::$app->config->runInstallMigrations(SproutForms::getInstance());
    }

    /**
     * @return bool
     * @throws ReflectionException
     */
    public function safeDown(): bool
    {
        SproutBase::$app->config->runUninstallMigrations(SproutForms::getInstance());
    }
}
