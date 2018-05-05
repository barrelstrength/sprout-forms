<?php

namespace barrelstrength\sproutforms\integrations\sproutemail\emailtemplates\basic;

use barrelstrength\sproutbase\sproutemail\contracts\BaseEmailTemplates;
use Craft;

/**
 * Class BasicSproutFormsNotification
 */
class BasicSproutFormsNotification extends BaseEmailTemplates
{
    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-base', 'Basic Notification (Sprout Forms)');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return Craft::getAlias('@barrelstrength/sproutforms/templates/_emailtemplates/basic');
    }
}



