<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\reports\migrations\Install as SproutBaseReportsInstall;
use barrelstrength\sproutbase\app\email\migrations\Install as SproutBaseNotificationInstall;
use barrelstrength\sproutforms\formtemplates\AccessibleTemplates;
use barrelstrength\sproutforms\integrations\sproutreports\datasources\EntriesDataSource;
use barrelstrength\sproutforms\models\Settings;
use craft\db\Migration;

/**
 * Installation Migration
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
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
     */
    public function safeDown()
    {
        SproutBase::$app->dataSources->deleteReportsByType(EntriesDataSource::class);

        $this->dropTable('{{%sproutforms_entries}}');
        $this->dropTable('{{%sproutforms_forms}}');
        $this->dropTable('{{%sproutforms_formgroups}}');
        $this->dropTable('{{%sproutforms_entrystatuses}}');
        $this->dropTable('{{%sproutforms_integrations}}');

        return true;
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
            'displaySectionTitles' => $this->boolean()->defaultValue(false),
            'redirectUri' => $this->string(),
            'submitAction' => $this->string(),
            'submitButtonText' => $this->string(),
            'saveData' => $this->boolean()->defaultValue(false),
            'templateOverridesFolder' => $this->string(),
            'enableFileAttachments' => $this->boolean()->defaultValue(false),
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
            'userAgent' => $this->longText(),
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
            'settings' => $this->text(),
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
            'fieldLayoutId',
            false
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_entries}}',
                'formId',
                false, true
            ),
            '{{%sproutforms_entries}}',
            'formId',
            false
        );

        $this->createIndex(
            $this->db->getIndexName(
                '{{%sproutforms_integrations}}',
                'formId',
                false, true
            ),
            '{{%sproutforms_integrations}}',
            'formId',
            false
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
            '{{%fieldlayouts}}', 'id', 'SET NULL', null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_forms}}', 'id'
            ),
            '{{%sproutforms_forms}}', 'id',
            '{{%elements}}', 'id', 'CASCADE', null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_entries}}', 'id'
            ),
            '{{%sproutforms_entries}}', 'id',
            '{{%elements}}', 'id', 'CASCADE', null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_entries}}', 'formId'
            ),
            '{{%sproutforms_entries}}', 'formId',
            '{{%sproutforms_forms}}', 'id', 'CASCADE', null
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                '{{%sproutforms_integrations}}', 'formId'
            ),
            '{{%sproutforms_integrations}}', 'formId',
            '{{%sproutforms_forms}}', 'id', 'CASCADE', null
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @throws \ReflectionException
     * @throws \yii\db\Exception
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

        $settings = new Settings();
        $accessible = new AccessibleTemplates();
        $settings->templateFolderOverride = $accessible->getTemplateId();

        $settings->captchaSettings = [
            'sproutforms-duplicatecaptcha' => [
                'enabled' => 1
            ],
            'sproutforms-javascriptcaptcha' => [
                'enabled' => 1
            ],
            'sproutforms-honeypotcaptcha' => [
                'enabled' => 1,
                'honeypotFieldName' => 'beesknees',
                'honeypotScreenReaderMessage' => 'Leave this field blank'
            ],
        ];

        $newSettings = json_encode($settings->getAttributes());

        $this->db->createCommand()->update('{{%plugins}}', [
            'settings' => $newSettings
        ], [
            'handle' => strtolower('sprout-forms')
        ])->execute();
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
}