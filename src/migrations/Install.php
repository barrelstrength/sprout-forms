<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbase\base\SproutDependencyInterface;
use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbaseemail\migrations\Install as SproutBaseEmailInstall;
use barrelstrength\sproutbasefields\migrations\Install as SproutBaseFieldsInstall;
use barrelstrength\sproutbasereports\migrations\Install as SproutBaseReportsInstall;
use barrelstrength\sproutbasereports\SproutBaseReports;
use barrelstrength\sproutforms\captchas\DuplicateCaptcha;
use barrelstrength\sproutforms\captchas\HoneypotCaptcha;
use barrelstrength\sproutforms\captchas\JavascriptCaptcha;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\integrations\sproutreports\datasources\EntriesDataSource;
use barrelstrength\sproutforms\integrations\sproutreports\datasources\IntegrationLogDataSource;
use barrelstrength\sproutforms\integrations\sproutreports\datasources\SpamLogDataSource;
use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\records\EntriesSpamLog as EntriesSpamLogRecord;
use barrelstrength\sproutforms\records\Entry as EntryRecord;
use barrelstrength\sproutforms\records\EntryStatus as EntryStatusRecord;
use barrelstrength\sproutforms\records\Form as FormRecord;
use barrelstrength\sproutforms\records\FormGroup as FormGroupRecord;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use barrelstrength\sproutforms\records\IntegrationLog as IntegrationLogRecord;
use barrelstrength\sproutforms\records\Rules as RulesRecord;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\db\Migration;
use craft\db\Table;
use craft\services\Plugins;
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
        /** @var SproutForms $plugin */
        $plugin = SproutForms::getInstance();

        $sproutBaseEmailInUse = $plugin->dependencyInUse(SproutDependencyInterface::SPROUT_BASE_EMAIL);
        $sproutBaseFieldsInUse = $plugin->dependencyInUse(SproutDependencyInterface::SPROUT_BASE_FIELDS);
        $sproutBaseReportsInUse = $plugin->dependencyInUse(SproutDependencyInterface::SPROUT_BASE_REPORTS);
        $sproutBaseInUse = $plugin->dependencyInUse(SproutDependencyInterface::SPROUT_BASE);

        SproutBaseReports::$app->dataSources->deleteReportsByType(EntriesDataSource::class);
        SproutBaseReports::$app->dataSources->deleteReportsByType(IntegrationLogDataSource::class);
        SproutBaseReports::$app->dataSources->deleteReportsByType(SpamLogDataSource::class);

        if (!$sproutBaseEmailInUse) {
            $migration = new SproutBaseEmailInstall();

            ob_start();
            $migration->safeDown();
            ob_end_clean();
        }

        if (!$sproutBaseFieldsInUse) {
            $migration = new SproutBaseFieldsInstall();

            ob_start();
            $migration->safeDown();
            ob_end_clean();
        }

        if (!$sproutBaseReportsInUse) {
            $migration = new SproutBaseReportsInstall();

            ob_start();
            $migration->safeDown();
            ob_end_clean();
        }

        if (!$sproutBaseInUse) {
            $migration = new SproutBaseInstall();

            ob_start();
            $migration->safeDown();
            ob_end_clean();
        }

        // Delete Form Entry Elements
        $this->delete(Table::ELEMENTS, ['type' => Entry::class]);

        // Delete Form Elements
        $this->delete(Table::ELEMENTS, ['type' => Form::class]);

        $this->dropTableIfExists(IntegrationLogRecord::tableName());
        $this->dropTableIfExists(IntegrationRecord::tableName());
        $this->dropTableIfExists(RulesRecord::tableName());
        $this->dropTableIfExists(EntriesSpamLogRecord::tableName());
        $this->dropTableIfExists(EntryRecord::tableName());
        $this->dropTableIfExists(EntryStatusRecord::tableName());
        $this->dropTableIfExists(FormRecord::tableName());
        $this->dropTableIfExists(FormGroupRecord::tableName());

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
        $migration = new SproutBaseEmailInstall();

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
        $this->createTable(FormRecord::tableName(), [
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
            'formTemplateId' => $this->string(),
            'enableCaptchas' => $this->boolean()->defaultValue(true)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(FormGroupRecord::tableName(), [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(EntryRecord::tableName(), [
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

        $this->createTable(EntriesSpamLogRecord::tableName(), [
            'id' => $this->primaryKey(),
            'entryId' => $this->integer()->notNull(),
            'type' => $this->string(),
            'errors' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(EntryStatusRecord::tableName(), [
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

        $this->createTable(IntegrationRecord::tableName(), [
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

        $this->createTable(RulesRecord::tableName(), [
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

        $this->createTable(IntegrationLogRecord::tableName(), [
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
                FormRecord::tableName(),
                'fieldLayoutId',
                false, true
            ),
            FormRecord::tableName(),
            'fieldLayoutId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                EntryRecord::tableName(),
                'formId',
                false, true
            ),
            EntryRecord::tableName(),
            'formId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                EntriesSpamLogRecord::tableName(),
                'entryId',
                false, true
            ),
            EntriesSpamLogRecord::tableName(),
            'entryId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                IntegrationRecord::tableName(),
                'formId',
                false, true
            ),
            IntegrationRecord::tableName(),
            'formId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                RulesRecord::tableName(),
                'formId',
                false, true
            ),
            RulesRecord::tableName(),
            'formId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                IntegrationLogRecord::tableName(),
                'entryId',
                false, true
            ),
            IntegrationLogRecord::tableName(),
            'entryId'
        );

        $this->createIndex(
            $this->db->getIndexName(
                IntegrationLogRecord::tableName(),
                'integrationId',
                false, true
            ),
            IntegrationLogRecord::tableName(),
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
                FormRecord::tableName(), 'fieldLayoutId'
            ),
            FormRecord::tableName(), 'fieldLayoutId',
            Table::FIELDLAYOUTS, 'id', 'SET NULL'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                FormRecord::tableName(), 'id'
            ),
            FormRecord::tableName(), 'id',
            Table::ELEMENTS, 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                EntryRecord::tableName(), 'id'
            ),
            EntryRecord::tableName(), 'id',
            Table::ELEMENTS, 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                EntriesSpamLogRecord::tableName(), 'entryId'
            ),
            EntriesSpamLogRecord::tableName(), 'entryId',
            EntryRecord::tableName(), 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                EntryRecord::tableName(), 'formId'
            ),
            EntryRecord::tableName(), 'formId',
            FormRecord::tableName(), 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                IntegrationRecord::tableName(), 'formId'
            ),
            IntegrationRecord::tableName(), 'formId',
            FormRecord::tableName(), 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                RulesRecord::tableName(), 'formId'
            ),
            RulesRecord::tableName(), 'formId',
            FormRecord::tableName(), 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                IntegrationLogRecord::tableName(), 'entryId'
            ),
            IntegrationLogRecord::tableName(), 'entryId',
            EntryRecord::tableName(), 'id', 'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                IntegrationLogRecord::tableName(), 'integrationId'
            ),
            IntegrationLogRecord::tableName(), 'integrationId',
            IntegrationRecord::tableName(), 'id', 'CASCADE'
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @throws ErrorException
     * @throws \yii\base\Exception
     * @throws NotSupportedException
     * @throws Exception
     * @throws ServerErrorHttpException
     * @throws \Exception
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
            $this->db->createCommand()->insert(EntryStatusRecord::tableName(), [
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
        $settings->formTemplateId = $currentSettings['formTemplateId'] ?? AccessibleTemplates::class;

        $settings->captchaSettings = $currentSettings['captchaSettings'] ?? [
                DuplicateCaptcha::class => [
                    'enabled' => 0
                ],
                JavascriptCaptcha::class => [
                    'enabled' => 1
                ],
                HoneypotCaptcha::class => [
                    'enabled' => 0,
                    'honeypotFieldName' => 'sprout-forms-hc',
                    'honeypotScreenReaderMessage' => 'Leave this field blank'
                ],
            ];

        $projectConfig->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $settings->toArray());
    }
}
