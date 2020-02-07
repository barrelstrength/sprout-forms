<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\base;

use Craft;
use ReflectionClass;
use ReflectionException;

/**
 * Class FormTemplates
 */
abstract class FormTemplates
{
    /**
     * The Template ID of the Form Templates in the format {pluginhandle}-{formtemplateclassname}
     *
     * @example
     * sproutforms-accessibletemplates
     * sproutforms-basictemplates
     *
     * @var string
     */
    public $templateId;

    /**
     * Generates the Template ID
     *
     * @return string
     * @throws ReflectionException
     */
    public function getTemplateId(): string
    {
        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass(get_class($this));

        // Build $templateId: pluginhandle-formtemplateclassname
        $pluginHandleWithoutSpaces = str_replace('-', '', $pluginHandle);

        $captchaClass = (new ReflectionClass($this))->getShortName();

        $templateId = $pluginHandleWithoutSpaces.'-'.$captchaClass;

        $this->templateId = strtolower($templateId);

        return $this->templateId;
    }

    /**
     * The name of your Form Templates
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * The folder path where your form templates exist
     *
     * @return string
     */
    abstract public function getPath(): string;

    /**
     * Adds pre-defined options for css classes.
     *
     * These classes will display in the CSS Classes dropdown list on the Field Edit modal
     * for Field Types that support the $cssClasses property.
     *
     * @return array
     */
    public function getCssClassDefaults(): array
    {
        return [];
    }
}
