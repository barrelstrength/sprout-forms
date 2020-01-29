<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\integrations\sproutimport\bundles;

use barrelstrength\sproutbaseimport\base\Bundle;
use Craft;

class SpecialFieldsBundle extends Bundle
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-forms', 'Example Form - Special Fields');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {

        return Craft::t('sprout-forms', 'An example form with all special fields.');
    }

    /**
     * The folder where this bundle's importable schema files are located
     *
     * @default plugin-handle/src/schema
     *
     * @return string
     */
    public function getSchemaFolder(): string
    {
        return $this->plugin->getBasePath().DIRECTORY_SEPARATOR.'templates/_integrations/sproutimport/bundles/specialfields/schema';
    }

    /**
     * The folder where this bundle's template files are located
     *
     * @default plugin-handle/src/templates
     *
     * @return string
     */
    public function getSourceTemplateFolder(): string
    {
        return $this->plugin->getBasePath().DIRECTORY_SEPARATOR.'templates/_integrations/sproutimport/bundles/specialfields/templates';
    }

}


