<?php

namespace barrelstrength\sproutforms\contracts;

use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use Craft;

/**
 * Class BaseCaptcha
 */
abstract class BaseCaptcha
{
    /**
     * @var string
     */
    public $captchaId;

    /**
     * @return string
     * @throws \ReflectionException
     */
    public function getCaptchaId()
    {
        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass(get_class($this));

        // Build $captchaId: pluginname-captchaclassname
        $pluginHandleWithoutSpaces = str_replace('-', '', $pluginHandle);

        $captchaClass = (new \ReflectionClass($this))->getShortName();

        $captchaId = $pluginHandleWithoutSpaces.'-'.$captchaClass;

        $this->captchaId = strtolower($captchaId);

        return $this->captchaId;
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    abstract public function getDescription();

    /**
     * @return null
     * @throws \ReflectionException
     */
    public function getSettings()
    {
        $sproutFormsSettings = Craft::$app->getPlugins()->getPlugin('sprout-forms')->getSettings();

        return $sproutFormsSettings->captchaSettings[$this->getCaptchaId()] ?? null;
    }

    /**
     * Return whatever is needed to get your captcha working in the form template
     *
     * Sprout Forms will loop through all enabled Captcha integrations and output
     * getCaptchaHtml when the template hook `sproutForms.modifyForm` in form.html
     * is triggered.
     *
     * @return string
     */
    public function getCaptchaHtml()
    {
        return '';
    }

    /**
     * Return any settings for your Captcha
     *
     * Sprout Forms will display all captcha settings on the Settings->Spam Prevention tab.
     * An option will be displayed to enable/disable each captcha. If your captcha's
     * settings are enabled, Sprout Forms will output getCaptchaSettingsHtml for users to
     * customize any additional settings your provide.
     *
     * @return string
     */
    public function getCaptchaSettingsHtml()
    {
        return '';
    }

    /**
     * Return if a form submission passes or fails your captcha.
     *
     * @param OnBeforeSaveEntryEvent $event
     *
     * @return bool
     */
    public function verifySubmission(OnBeforeSaveEntryEvent $event): bool
    {
        return true;
    }
}
