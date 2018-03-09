<?php

namespace barrelstrength\sproutforms\contracts;

use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use Craft;
use yii\base\Model;

/**
 * Class BaseCaptcha
 *
 * @author    Barrel Strength Design LLC <sprout@barrelstrengthdesign.com>
 * @copyright Copyright (c) $today.year, Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 * @see       http://sprout.barrelstrengthdesign.com
 * @package   craft.plugins.basecaptcha
 * @since     2.0
 */
abstract class BaseCaptcha
{
    public $captchaId;

    /**
     * @return string
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
     * @return null|array
     */
    public function getSettings()
    {
        $sproutFormsSettings = Craft::$app->getPlugins()->getPlugin('sprout-forms')->getSettings();

        return $sproutFormsSettings->captchaSettings[$this->getCaptchaId()] ?? null;
    }

    /**
     * @return mixed
     */
    abstract public function getName();

    /**
     * @return mixed
     */
    abstract public function getDescription();

    /**
     * Return whatever is needed to your form template for your captcha
     */
    public function getCaptchaHtml()
    {
        return '';
    }

    /**
     * Return any settings your Captcha has and we'll display them in the Sprout Forms Captcha settings area
     */
    public function getCaptchaSettingsHtml()
    {
        return '';
    }

    /**
     * @param OnBeforeSaveEntryEvent $event
     *
     * @return bool
     */
    public function verifySubmission(OnBeforeSaveEntryEvent $event): bool
    {
        return true;
    }
}
