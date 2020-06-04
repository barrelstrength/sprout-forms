<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbase\config\base\DependencyInterface;
use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbase\app\email\migrations\Install as SproutBaseEmailInstall;
use barrelstrength\sproutbase\app\fields\migrations\Install as SproutBaseFieldsInstall;
use barrelstrength\sproutbase\app\reports\migrations\Install as SproutBaseReportsInstall;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\forms\captchas\DuplicateCaptcha;
use barrelstrength\sproutbase\app\forms\captchas\HoneypotCaptcha;
use barrelstrength\sproutbase\app\forms\captchas\JavascriptCaptcha;
use barrelstrength\sproutbase\app\forms\elements\Entry;
use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\app\forms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\EntriesDataSource;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\IntegrationLogDataSource;
use barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources\SpamLogDataSource;
use barrelstrength\sproutbase\app\forms\models\Settings;
use barrelstrength\sproutbase\app\forms\records\EntriesSpamLog as EntriesSpamLogRecord;
use barrelstrength\sproutbase\app\forms\records\Entry as EntryRecord;
use barrelstrength\sproutbase\app\forms\records\EntryStatus as EntryStatusRecord;
use barrelstrength\sproutbase\app\forms\records\Form as FormRecord;
use barrelstrength\sproutbase\app\forms\records\FormGroup as FormGroupRecord;
use barrelstrength\sproutbase\app\forms\records\Integration as IntegrationRecord;
use barrelstrength\sproutbase\app\forms\records\IntegrationLog as IntegrationLogRecord;
use barrelstrength\sproutbase\app\forms\records\Rules as RulesRecord;
use barrelstrength\sproutbase\app\forms\SproutForms;
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
        SproutBase::$app->config->runInstallMigrations(SproutForms::getInstance());

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
        SproutBase::$app->dataSources->deleteReportsByType(EntriesDataSource::class);
        SproutBase::$app->dataSources->deleteReportsByType(IntegrationLogDataSource::class);
        SproutBase::$app->dataSources->deleteReportsByType(SpamLogDataSource::class);

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

        SproutBase::$app->config->runUninstallMigrations(SproutForms::getInstance());

        return true;
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
