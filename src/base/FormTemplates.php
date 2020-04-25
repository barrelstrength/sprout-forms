<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\base;

use craft\web\View;

/**
 * Class FormTemplates
 */
abstract class FormTemplates
{
    /**
     * The name of your Form Templates
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * The Template Mode to use when loading the email template
     *
     * @return string
     */
    public function getTemplateMode(): string
    {
        return View::TEMPLATE_MODE_CP;
    }

    public function getFullPath(): string
    {
        // @deprecate in v4.0. If getTemplateRoot is not implemented,
        // keep old behavior, for now.
        if ($this->getTemplateRoot() === null) {
            return $this->getPath();
        }

        return $this->getTemplateRoot().'/'.$this->getPath();
    }

    /**
     * The root folder where the Form Templates exist
     *
     * @return string|null
     */
    public function getTemplateRoot()
    {
        return null;
    }

    /**
     * The folder path where your Form Templates exist in relation to the folder defined in [[self::getTemplateRoot]]
     *
     * This value should also be a folder. Sprout Forms will look for any template files that you choose to override
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
