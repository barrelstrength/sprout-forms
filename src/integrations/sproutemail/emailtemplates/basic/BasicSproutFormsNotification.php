<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\integrations\sproutemail\emailtemplates\basic;

use barrelstrength\sproutbaseemail\base\EmailTemplates;
use Craft;

/**
 * Class BasicSproutFormsNotification
 */
class BasicSproutFormsNotification extends EmailTemplates
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-forms', 'Basic Notification (Sprout Forms)');
    }

    /**
     * @return string
     */
    public function getTemplateRoot(): string
    {
        return Craft::getAlias('@barrelstrength/sproutforms/templates');
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '_integrations/sproutemail/emailtemplates/basic';
    }
}



