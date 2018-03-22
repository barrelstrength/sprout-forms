<?php

namespace barrelstrength\sproutforms\contracts;

use Craft;

/**
 * Class BaseFormTemplates
 */
abstract class BaseFormTemplates
{
    /**
     * @var string
     */
    public $templateId;

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getTemplateId()
    {
        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass(get_class($this));

        // Build $templateId: pluginname-captchaclassname
        $pluginHandleWithoutSpaces = str_replace('-', '', $pluginHandle);

        $captchaClass = (new \ReflectionClass($this))->getShortName();

        $templateId = $pluginHandleWithoutSpaces.'-'.$captchaClass;

        $this->templateId = strtolower($templateId);

        return $this->templateId;
    }

    /**
     * Add initial options for css classes for each field on Sprout Forms
     *
     * @return array
     */
    public function getCssClassDefaults()
    {
        return [];
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    abstract public function getPath();
}
