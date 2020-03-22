<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbaseemail\migrations\Install as SproutBaseNotificationInstall;
use barrelstrength\sproutbasefields\migrations\Install as SproutBaseFieldsInstall;
use barrelstrength\sproutbasereports\migrations\Install as SproutBaseReportsInstall;
use barrelstrength\sproutbasereports\SproutBaseReports;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\integrations\sproutreports\datasources\EntriesDataSource;
use barrelstrength\sproutforms\models\Settings;
use Craft;
use craft\db\Migration;
use craft\services\Plugins;
use ReflectionException;
use yii\base\ErrorException;
use yii\base\NotSupportedException;
use yii\db\Exception;
use yii\web\ServerErrorHttpException;

/**
 * Installation Migration
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws ReflectionException
     * @throws ErrorException
     * @throws \yii\base\Exception
     * @throws NotSupportedException
     * @throws Exception
     * @throws ServerErrorHttpException
     */
    public function safeUp(): bool
    {
        // Make sure Sprout Fields is also configured
        $this->installSproutFields();
        // Make sure Sprout Reports is also configured
        $this->installSproutReports();
        // Install Sprout Notifications Table
        $this->installSproutEmail();

        // Install Sprout Forms
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
        $this->insertDefaultData();

        return true;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function safeDown(): bool
    {
        SproutBaseReports::$app->dataSources->deleteReportsByType(EntriesDataSource::class);

        $this->dropTableIfExists('{{%sproutforms_integrations_log}}');
        $this->dropTableIfExists('{{%sproutforms_integrations}}');
        $this->dropTableIfExists('{{%sproutforms_rules}}');
        $this->dropTableIfExists('{{%sproutforms_entries_spam_log}}');
        $this->dropTableIfExists('{{%sproutforms_entries}}');
        $this->dropTableIfExists('{{%sproutforms_entrystatuses}}');
        $this->dropTableIfExists('{{%sproutforms_forms}}');
        $this->dropTableIfExists('{{%sproutforms_formgroups}}');

        return true;
    }

    public function installSproutFields()
    {
        $migration = new SproutBaseFieldsInstall();

        ob_start();
        $migration->safeUp();
        ob_end_clean();
    }

    public function installSproutEmail()
    {
        $migration = new SproutBaseNotificationInstall();

        ob_start();
        $migration->safeUp();
        ob_end_clean();
    }

    public function installSproutReports()
    {
        $migration = new SproutBaseReportsInstall();

        ob_start();
        $migration->safeUp();
        ob_end_clean();
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable('{{%sproutforms_forms}}', [
            'id' => $this->primaryKey(),
            'fieldLayoutId' => $this->integer(),
            'groupId' => $this->integer(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'titleFormat' => $this->string()->notNull(),
            'displaySectionTitles' => $this->boolean()->defaultValue(false)->notNull(),
            'redirectUri' => $this->string(),
            'submissionMethod' => $this->string()->defaultValue('sync')->notNull(),
            'errorDisplayMethod' => $this->string()->defaultValue('inline')->notNull(),
            'successMessage' => $this->text(),
            'errorMessage' => $this->text(),
            'submitButtonText' => $this->string(),
            'saveData' => $this->boolean()->defaultValue(false)->notNull(),
            'formTemplate' => $this->string(),
            'enableCaptchas' => $this->boolean()->defaultValue(true)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%sproutforms_formgroups}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%sproutforms_entries}}', [
            'id' => $this->primaryKey(),
            'formId' => $this->integer()->notNull(),
            'statusId' => $this->integer(),
            'ipAddress' => $this->string(),
            'referrer' => $this->string(),
            'userAgent' => $this->longText(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%sproutforms_entries_spam_log}}', [
            'id' => $this->primaryKey(),
            'entryId' => $this->integer()->notNull(),
            'type' => $this->string(),
            'errors' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%sproutforms_entrystatuses}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'color' => $this->enum('color',
                [
                    'green', 'orange', 'red', 'blue',
                    'yellow', 'pink', 'purple', 'turquoise',
                    'light', 'grey', 'black'
                ])
                ->notNull()->defaultValue('blue'),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'isDefault' => $this->boolean(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%sproutforms_integrations}}', [
            'id' => $this->primaryKey(),
            'formId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'sendRule' => $this->text(),
            'settings' => $this->text(),
            'enabled' => $this->boolean()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%sproutforms_rules}}', [
            'id' => $this->primaryKey(),
            'formId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'type' => $this->string()->notNull(),
            'settings' => $this->text(),
            'enabled' => $this->boolean()->defaultValue(false),
            'behaviorAction' => $this->string(),
            'behaviorTarget' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%sproutforms_integrations_log}}', [
            'id' => $this->primaryKey(),
            'entryId' => $this->integer(),
            'integrationId' => $this->integer()->notNull(),
            'success' => $this->boolean()->defaultValue(false),
            'status' => $this->enum('status',
                [
                    'pending', 'notsent', 'completed'
                ])
                ->notNull()->defaultValue('pending'),
            'message' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    /**
     * Creates the indexes.
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_forms}}',
                'fieldLayoutId',
                false, true
            ),
            '{{%sproutforms_forms}}',
            'fieldLayoutId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_entries}}',
                'formId',
                false, true
            ),
            '{{%sproutforms_entries}}',
            'formId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_entries_spam_log}}',
                'entryId',
                false, true
            ),
            '{{%sproutforms_entries_spam_log}}',
            'entryId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_integrations}}',
                'formId',
                false, true
            ),
            '{{%sproutforms_integrations}}',
            'formId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_rules}}',
                'formId',
                false, true
            ),
            '{{%sproutforms_rules}}',
            'formId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_integrations_log}}',
                'entryId',
                false, true
            ),
            '{{%sproutforms_integrations_log}}',
            'entryId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_integrations_log}}',
                'integrationId',
                false, true
            ),
            '{{%sproutforms_integrations_log}}',
            'integrationId'
        );
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_forms}}', 'fieldLayoutId'
            ),
            '{{%sproutforms_forms}}', 'fieldLayoutId',
            '{{%fieldlayouts}}', 'id', 'SET NULL'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_forms}}', 'id'
            ),
            '{{%sproutforms_forms}}', 'id',
            '{{%elements}}', 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_entries}}', 'id'
            ),
            '{{%sproutforms_entries}}', 'id',
            '{{%elements}}', 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_entries_spam_log}}', 'entryId'
            ),
            '{{%sproutforms_entries_spam_log}}', 'entryId',
            '{{%sproutforms_entries}}', 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_entries}}', 'formId'
            ),
            '{{%sproutforms_entries}}', 'formId',
            '{{%sproutforms_forms}}', 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_integrations}}', 'formId'
            ),
            '{{%sproutforms_integrations}}', 'formId',
            '{{%sproutforms_forms}}', 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_rules}}', 'formId'
            ),
            '{{%sproutforms_rules}}', 'formId',
            '{{%sproutforms_forms}}', 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_integrations_log}}', 'entryId'
            ),
            '{{%sproutforms_integrations_log}}', 'entryId',
            '{{%sproutforms_entries}}', 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_integrations_log}}', 'integrationId'
            ),
            '{{%sproutforms_integrations_log}}', 'integrationId',
            '{{%sproutforms_integrations}}', 'id', 'CASCADE'
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @throws ReflectionException
     * @throws ErrorException
     * @throws \yii\base\Exception
     * @throws NotSupportedException
     * @throws Exception
     * @throws ServerErrorHttpException
     */
    protected function insertDefaultData()
    {
        // populate default Entry Statuses
        $defaultEntryStatuses = [
            0 => [
                'name' => 'Unread',
                'handle' => 'unread',
                'color' => 'blue',
                'sortOrder' => 1,
                'isDefault' => 1
            ],
            1 => [
                'name' => 'Read',
                'handle' => 'read',
                'color' => 'grey',
                'sortOrder' => 2,
                'isDefault' => 0
            ],
            2 => [
                'name' => 'Spam',
                'handle' => 'spam',
                'color' => 'black',
                'sortOrder' => 3,
                'isDefault' => 0
            ]
        ];

        foreach ($defaultEntryStatuses as $entryStatus) {
            $this->db->createCommand()->insert('{{%sproutforms_entrystatuses}}', [
                'name' => $entryStatus['name'],
                'handle' => $entryStatus['handle'],
                'color' => $entryStatus['color'],
                'sortOrder' => $entryStatus['sortOrder'],
                'isDefault' => $entryStatus['isDefault']
            ])->execute();
        }

        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-forms';
        $currentSettings = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings');

        $settings = new Settings();
        $settings->setAttributes($currentSettings);
        $accessible = new AccessibleTemplates();
        $settings->formTemplateDefaultValue = $currentSettings['formTemplateDefaultValue'] ?? $accessible->getTemplateId();

        $settings->captchaSettings = $currentSettings['captchaSettings'] ?? [
                'sproutforms-duplicatecaptcha' => [
                    'enabled' => 0
                ],
                'sproutforms-javascriptcaptcha' => [
                    'enabled' => 1
                ],
                'sproutforms-honeypotcaptcha' => [
                    'enabled' => 0,
                    'honeypotFieldName' => 'beesknees',
                    'honeypotScreenReaderMessage' => 'Leave this field blank'
                ],
            ];

        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $settings->toArray());
    }
}
