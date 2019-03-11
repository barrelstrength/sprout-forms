<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbaseemail\SproutBaseEmail;
use craft\db\Migration;
use craft\db\Query;

use Craft;
use craft\helpers\Json;
use craft\helpers\MigrationHelper;
use barrelstrength\sproutbaseemail\elements\NotificationEmail;
use barrelstrength\sproutbaseemail\migrations\m180927_080639_add_cc_bcc_columns as CcBccMigration;
use barrelstrength\sproutbaseemail\migrations\m180501_000002_rename_notification_options_column;
use barrelstrength\sproutbaseemail\migrations\m180501_000003_add_notification_columns;
use barrelstrength\sproutbaseemail\migrations\m180515_000000_rename_notification_pluginId_column;
use barrelstrength\sproutbaseemail\migrations\Install as SproutBaseNotificationInstall;

/**
 * m180314_161540_craft2_to_craft3 migration.
 */
class m180314_161540_craft2_to_craft3 extends Migration
{
    /**
     * @inheritdoc
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\NotSupportedException
     * @throws \yii\db\Exception
     */
    public function safeUp(): bool
    {
        $forms = (new Query())
            ->select(['id', 'handle'])
            ->from(['{{%sproutforms_forms}}'])
            ->all();

        foreach ($forms as $form) {
            $table = '{{%sproutformscontent_'.strtolower($form['handle']).'}}';
            $siteId = Craft::$app->getSites()->getPrimarySite()->id;
            $isNew = false;

            if (!$this->db->columnExists($table, 'siteId')) {

                $this->addColumn($table, 'siteId', $this->integer()->after('elementId')->notNull());
                $isNew = true;
            }

            $rows = (new Query())
                ->select(['id'])
                ->from([$table])
                ->all();

            foreach ($rows as $row) {
                $this->update($table, ['siteId' => $siteId], ['id' => $row['id']], [], false);
            }

            if ($isNew) {
                $this->createIndex($this->db->getIndexName($table, 'elementId,siteId'), $table, 'elementId,siteId', true);

                $this->addForeignKey($this->db->getForeignKeyName($table, 'siteId'), $table, 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE');
            }

            if ($this->db->columnExists($table, 'locale')) {
                MigrationHelper::dropIndexIfExists($table, ['elementId', 'locale'], true, $this);

                if (MigrationHelper::doesForeignKeyExist($table, ['locale'])) {
                    MigrationHelper::dropForeignKeyIfExists($table, ['locale'], $this);
                    MigrationHelper::dropIndexIfExists($table, ['locale'], false, $this);
                }

                $this->dropColumn($table, 'locale');
            }

            if ($this->db->columnExists($table, 'locale__siteId')) {
                MigrationHelper::dropIndexIfExists($table, ['elementId', 'locale__siteId'], true, $this);
                if (MigrationHelper::doesForeignKeyExist($table, ['locale__siteId'])) {
                    MigrationHelper::dropForeignKeyIfExists($table, ['locale__siteId'], $this);
                    MigrationHelper::dropIndexIfExists($table, ['locale__siteId'], false, $this);
                }
                $this->dropColumn($table, 'locale__siteId');
            }
        }

        if ($this->db->columnExists('{{%sproutforms_forms}}', 'enableTemplateOverrides')) {
            $this->dropColumn('{{%sproutforms_forms}}', 'enableTemplateOverrides');
        }

        $emailNotificationMigration = new SproutBaseNotificationInstall();

        ob_start();
        $emailNotificationMigration->safeUp();
        ob_end_clean();

        // Duplicate notification table updates
        $notificationOptionsMigration = new m180501_000002_rename_notification_options_column();
        ob_start();
        $notificationOptionsMigration->safeUp();
        ob_end_clean();

        $notificationAddMigration = new m180501_000003_add_notification_columns();
        ob_start();
        $notificationAddMigration->safeUp();
        ob_end_clean();

        $notificationPluginIdMigration = new m180515_000000_rename_notification_pluginId_column();
        ob_start();
        $notificationPluginIdMigration->safeUp();
        ob_end_clean();

        $ccBccMigration = new CcBccMigration();
        ob_start();
        $ccBccMigration->safeUp();
        ob_end_clean();

        // Only for Sprout Forms, migrate notifications from forms
        $table = '{{%sproutforms_forms}}';

        $notificationColumns = [
            'notificationEnabled',
            'notificationRecipients',
            'notificationSubject',
            'notificationSenderName',
            'notificationSenderEmail',
            'notificationReplyToEmail'
        ];

        // Migrate notifications.
        $forms = (new Query())
            ->select('*')
            ->from([$table])
            ->all();

        foreach ($forms as $form) {
            if (isset($form['notificationSubject'], $form['notificationSenderName'], $form['notificationSenderEmail']) && $form['notificationSubject'] && $form['notificationSenderName'] && $form['notificationSenderEmail']) {

                $notificationEmail = new NotificationEmail();

                $settings = [
                    'whenNew' => '1',
                    'formIds' => [
                        $form['id']
                    ]
                ];

                $notificationEmail->subjectLine = $form['notificationSubject'];
                $notificationEmail->fromName = $form['notificationSenderName'];
                $notificationEmail->fromEmail = $form['notificationSenderEmail'];
                $notificationEmail->replyToEmail = $form['notificationReplyToEmail'];
                $notificationEmail->recipients = $form['notificationRecipients'];
                $notificationEmail->title = $notificationEmail->subjectLine;
                $notificationEmail->pluginHandle = 'sprout-forms';
                $notificationEmail->enableFileAttachments = $form['enableFileAttachments'];
                $notificationEmail->settings = Json::encode($settings);
                $notificationEmail->enabled = $form['notificationEnabled'] ?? 0;
                $notificationEmail->eventId = 'barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents\SaveEntryEvent';

                SproutBaseEmail::$app->notifications->saveNotification($notificationEmail);
            }
        }

        foreach ($notificationColumns as $notificationColumn) {
            if ($this->db->columnExists($table, $notificationColumn)) {
                $this->dropColumn($table, $notificationColumn);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180314_161540_craft2_to_craft3 cannot be reverted.\n";
        return false;
    }
}