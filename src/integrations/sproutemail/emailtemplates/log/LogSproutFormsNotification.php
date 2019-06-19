<?php

namespace barrelstrength\sproutforms\integrations\sproutemail\emailtemplates\log;

use barrelstrength\sproutbaseemail\base\EmailTemplates;
use Craft;

/**
 * Class LogSproutFormsNotification
 */
class LogSproutFormsNotification extends EmailTemplates
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-forms', 'Log Notification (Sprout Forms)');
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return Craft::getAlias('@barrelstrength/sproutforms/templates/_integrations/sproutemail/emailtemplates/log');
    }
}



