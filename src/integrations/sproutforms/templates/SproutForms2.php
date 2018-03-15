<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\templates;

use barrelstrength\sproutforms\contracts\BaseTemplate;
use Craft;

/**
 * Class SproutForms2
 */
class SproutForms2 extends BaseTemplate
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Sprout Forms 2.x';
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return Craft::getAlias('@barrelstrength/sproutforms/templates/_special/templates/sprout-forms-2');
    }
}



