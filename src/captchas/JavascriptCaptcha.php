<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\captchas;

use barrelstrength\sproutforms\base\Captcha;
use barrelstrength\sproutforms\events\OnBeforeValidateEntryEvent;
use Craft;
use craft\errors\MissingComponentException;
use craft\helpers\StringHelper;
use craft\web\View;
use yii\base\InvalidConfigException;

/**
 * Class InvisibleCaptcha
 *
 * @property string $name
 * @property string $description
 * @property string $captchaHtml
 */
class JavascriptCaptcha extends Captcha
{
    const JAVASCRIPT_CAPTCHA_INPUT_KEY = 'sprout-forms-jck';
    const JAVASCRIPT_CAPTCHA_VALUE_PREFIX = 'sprout-forms-jcv';

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Javascript Captcha';
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return Craft::t('sprout-forms', 'Prevent a form from being submmitted if a user does not have JavaScript enabled');
    }

    /**
     * @inheritdoc
     * @throws MissingComponentException
     */
    public function getCaptchaHtml(): string
    {
        $inputName = StringHelper::appendUniqueIdentifier(self::JAVASCRIPT_CAPTCHA_INPUT_KEY);
        $inputValue = StringHelper::appendUniqueIdentifier(self::JAVASCRIPT_CAPTCHA_VALUE_PREFIX);

        // Create session variable to retrieve a given forms js key/value
        Craft::$app->getSession()->set($inputName, $inputValue);

        // Create a second session variable so we can match the posted key
        // to what we expect it to be. If we can retrieve this second key
        // based on the submitted value, we know it's the same. And this lets
        // us support more than one form on a page.
        Craft::$app->getSession()->set($inputValue, true);

        // Set a hidden field with no value
        $output = '<input type="hidden" id="'.$inputName.'" name="'.$inputName.'" />';

        // Set the value of the hidden field using js
        $js = '(function(){ document.getElementById("'.$inputName.'").value = "'.$inputValue.'"; })();';

        Craft::$app->getView()->registerJs($js, View::POS_END);

        return $output;
    }

    /**
     * @param OnBeforeValidateEntryEvent $event
     *
     * @return bool
     * @throws MissingComponentException
     * @throws InvalidConfigException
     */
    public function verifySubmission(OnBeforeValidateEntryEvent $event): bool
    {
        $postedValues = Craft::$app->getRequest()->getBodyParams();

        // Filter out the posted JS Captcha Input.
        $jsCaptchaPostedInput = array_filter($postedValues, static function($key) {
            return strpos($key, self::JAVASCRIPT_CAPTCHA_INPUT_KEY) === 0;
        }, ARRAY_FILTER_USE_KEY);

        $inputValue = reset($jsCaptchaPostedInput);
        $inputKey = key($jsCaptchaPostedInput);

        if (Craft::$app->getSession()->get($inputValue) !== true) {
            $errorMessage = 'Javascript not enabled in browser or form page does not have a <body> tag.';
            Craft::error($errorMessage, __METHOD__);
            $this->addError(self::CAPTCHA_ERRORS_KEY, $errorMessage);

            return false;
        }

        // If there is a valid unique token set, unset it
        Craft::$app->getSession()->remove($inputKey);
        Craft::$app->getSession()->remove($inputValue);

        return true;
    }
}