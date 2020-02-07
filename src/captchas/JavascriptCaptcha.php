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
use craft\web\View;

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
        $uniqueId = uniqid('alojs', true);

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
     * @inheritdoc
     * @throws MissingComponentException
     */
    public function verifySubmission(OnBeforeValidateEntryEvent $event): bool
    {
        $uniqueid = null;

        foreach ($_POST as $key => $value) {
            // Fix issue on multiple forms on same page
            if (strpos($key, 'alojs') === 0) {
                $uniqueid = $_POST[$key];
                break;
            }
        }

        if (empty($uniqueid)) {
            $errorMessage = 'Javascript was not enabled in browser.';
            Craft::error($errorMessage, __METHOD__);

            $this->addError(self::CAPTCHA_ERRORS_KEY, $errorMessage);

            return false;
        }

        // If there is a valid unique token set, unset it
        Craft::$app->getSession()->remove($this->javascriptId);

        return true;
    }
}



