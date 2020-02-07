<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\events\OnBeforeValidateEntryEvent;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\Model;
use ReflectionClass;
use ReflectionException;

/**
 * Class Captcha
 *
 * @property null   $settings
 * @property string $captchaSettingsHtml
 * @property string $name
 * @property string $description
 * @property string $captchaHtml
 */
abstract class Captcha extends Model
{
    /**
     * Add errors to a Captcha using the error key
     * to support spam error logging and reporting
     */
    const CAPTCHA_ERRORS_KEY = 'captchaErrors';

    /**
     * A unique ID that is generated dynamically using the plugin
     * handle and the captcha class name {pluginhandle}-{captchaclassname}
     *
     * @example
     * pluginname-captchaclassname
     *
     * @var string
     */
    public $captchaId;

    /**
     * The form where the captcha is being output
     *
     * @var Form
     */
    public $form;

    /**
     * Generates the Captcha ID
     *
     * @return string
     * @throws ReflectionException
     */
    public function getCaptchaId(): string
    {
        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass(get_class($this));

        // Build $captchaId: pluginhandle-captchaclassname
        $pluginHandleWithoutSpaces = str_replace('-', '', $pluginHandle);

        $captchaClass = (new ReflectionClass($this))->getShortName();

        $captchaId = $pluginHandleWithoutSpaces.'-'.$captchaClass;

        $this->captchaId = strtolower($captchaId);

        return $this->captchaId;
    }

    /**
     * The name of the captcha
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * A description of the captcha behavior
     *
     * @return string
     */
    abstract public function getDescription(): string;

    /**
     * Returns any values saved as settings for this captcha
     *
     * @return null
     * @throws ReflectionException
     */
    public function getSettings()
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        $sproutFormsSettings = $plugin->getSettings();

        return $sproutFormsSettings->captchaSettings[$this->getCaptchaId()] ?? null;
    }

    /**
     * Returns html to display for your captcha settings.
     *
     * Sprout Forms will display all captcha settings on the Settings->Spam Prevention tab.
     * An option will be displayed to enable/disable each captcha. If your captcha's
     * settings are enabled, Sprout Forms will output getCaptchaSettingsHtml for users to
     * customize any additional settings your provide.
     *
     * @return string
     */
    public function getCaptchaSettingsHtml(): string
    {
        return '';
    }

    /**
     * Returns whatever is needed to get your captcha working in the front-end form template
     *
     * Sprout Forms will loop through all enabled Captcha integrations and output
     * getCaptchaHtml when the template hook `sproutForms.modifyForm` in form.html
     * is triggered.
     *
     * @return string
     */
    public function getCaptchaHtml(): string
    {
        return '';
    }

    /**
     * Returns if a form submission passes or fails your captcha validation.
     *
     * @param OnBeforeValidateEntryEvent $event
     *
     * @return bool
     */
    public function verifySubmission(OnBeforeValidateEntryEvent $event): bool
    {
        return true;
    }
}
