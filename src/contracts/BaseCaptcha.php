<?php

namespace barrelstrength\sproutforms\contracts;

use Craft;

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

    public function __construct()
    {
        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass(get_class($this));

        // Build $captchaId: pluginname-captchaclassname
        $pluginHandleWithoutSpaces = str_replace('-', '', $pluginHandle);

        $captchaClass = (new \ReflectionClass($this))->getShortName();

        $captchaId = $pluginHandleWithoutSpaces.'-'.$captchaClass;

        $this->captchaId = strtolower($captchaId);
    }

    /**
     * @return mixed
     */
    abstract public function getName();

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
}
