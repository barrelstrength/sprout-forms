<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\formtemplates;

use barrelstrength\sproutforms\contracts\BaseFormTemplates;
use Craft;

/**
 * Class BasicTemplates
 */
class BasicTemplates extends BaseFormTemplates
{
    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-forms', 'Basic Templates (Sprout, Legacy)');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return Craft::getAlias('@barrelstrength/sproutforms/templates/_formtemplates/templates/basic');
    }
}



