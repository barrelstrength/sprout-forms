<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\templates;

use barrelstrength\sproutforms\contracts\BaseTemplate;
use Craft;

/**
 * Class SproutForms3
 */
class SproutForms3 extends BaseTemplate
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Sprout Forms 3.x';
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return Craft::getAlias('@barrelstrength/sproutforms/templates/_special/templates/sprout-forms-3');
    }
}



