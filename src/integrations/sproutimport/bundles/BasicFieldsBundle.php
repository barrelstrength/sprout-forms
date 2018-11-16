<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutforms\integrations\sproutimport\bundles;

use barrelstrength\sproutbase\app\import\base\Bundle;
use Craft;

class BasicFieldsBundle extends Bundle
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-forms', 'Example Form - Basic Fields');
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return Craft::t('sprout-forms', 'An example form with all basic fields.');
    }

    /**
     * The folder where this bundles's importable schema files are located
     *
     * @default plugin-handle/src/schema
     *
     * @return string
     */
    public function getSchemaFolder()
    {
        return $this->plugin->getBasePath().DIRECTORY_SEPARATOR.'templates/_integrations/sproutimport/bundles/basicfields/schema';
    }

    /**
     * The folder where this bundle's template files are located
     *
     * @default plugin-handle/src/templates
     *
     * @return string
     */
    public function getSourceTemplateFolder()
    {
        return $this->plugin->getBasePath().DIRECTORY_SEPARATOR.'templates/_integrations/sproutimport/bundles/basicfields/templates';
    }

}


