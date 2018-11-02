<?php

namespace barrelstrength\sproutforms\migrations;

use barrelstrength\sproutbase\app\email\migrations\m180927_080639_add_cc_bcc_columns as CcBccMigration;
use craft\db\Query;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\app\email\elements\NotificationEmail;
use craft\db\Migration;

/**
 * m181030_000000_sprout_email_add_cc_bcc_columns migration.
 */
class m181030_000000_sprout_email_add_cc_bcc_columns extends Migration
{
    /**
     * @return bool
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $ccBccMigration = new CcBccMigration();

        ob_start();
        $ccBccMigration->safeUp();
        ob_end_clean();

        // Only for Sprout Forms, migrate notifications from forms
        /*
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
            if (isset($form['notificationEnabled']) && $form['notificationEnabled']){
                $notificationEmail = new NotificationEmail();

                $settings = [
                    'whenNew' => "1",
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
                $notificationEmail->settings = json_encode($settings);
                $notificationEmail->eventId = 'barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents\SaveEntryEvent';

                SproutBase::$app->notifications->saveNotification($notificationEmail);
            }
        }

        foreach ($notificationColumns as $notificationColumn) {
            if ($this->db->columnExists($table, $notificationColumn)){
                $this->dropColumn($table, $notificationColumn);
            }
        }
        */

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181030_000000_sprout_email_add_cc_bcc_columns cannot be reverted.\n";
        return false;
    }
}
