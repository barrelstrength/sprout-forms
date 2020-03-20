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
    /**
     * @var string
     */
    private $javascriptId = 'sprout-forms-javascript-captcha';

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
        $uniqueId = StringHelper::appendUniqueIdentifier('alojs');

        // Create session variable to test for javascript
        Craft::$app->getSession()->set($this->javascriptId, $uniqueId);

        // Set a hidden field with no value and use javascript to set it.
        $output = '
<input type="hidden" id="'.$uniqueId.'" name="'.$uniqueId.'" />';

        $js = '(function(){ document.getElementById("'.$uniqueId.'").value = "'.$uniqueId.'"; })();';

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
        $uniqueId = Craft::$app->getSession()->get($this->javascriptId);
        $postedValues = Craft::$app->getRequest()->getBodyParams();

        // Filter out the JS Captcha Input
        $jsCaptchaInput = array_filter($postedValues, static function($key) {
            return strpos($key, 'alojs') === 0;
        }, ARRAY_FILTER_USE_KEY);

        $inputValue = $jsCaptchaInput[$uniqueId] ?? null;

        if ($inputValue !== $uniqueId) {
            $errorMessage = 'Javascript not enabled in browser or form page does not have a <body> tag.';
            Craft::error($errorMessage, __METHOD__);
            $this->addError(self::CAPTCHA_ERRORS_KEY, $errorMessage);

            return false;
        }

        // If there is a valid unique token set, unset it
        Craft::$app->getSession()->remove($this->javascriptId);

        return true;
    }
}