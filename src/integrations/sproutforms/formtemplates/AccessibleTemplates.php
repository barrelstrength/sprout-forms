<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\formtemplates;

use barrelstrength\sproutforms\contracts\BaseFormTemplates;
use Craft;

/**
 * Class AccessibleTemplates
 */
class AccessibleTemplates extends BaseFormTemplates
{
    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-forms', 'Accessible Templates (Sprout, Default)');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return Craft::getAlias('@barrelstrength/sproutforms/templates/_formtemplates/templates/accessible');
    }

    public function getCssClassDefaults()
    {
        return [
            'left' => 'Left (left)',
            'right' => 'Right (right)'
        ];
    }
}



