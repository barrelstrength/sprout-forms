<?php

namespace barrelstrength\sproutforms\captchas;

use barrelstrength\sproutforms\base\Captcha;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\SproutForms;
use Craft;

/**
 * Class InvisibleCaptcha
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
     * @throws \craft\errors\MissingComponentException
     */
    public function getCaptchaHtml(): string
    {
        $uniqueId = uniqid('alojs', true);

        // Create session variable to test for javascript
        Craft::$app->getSession()->set($this->javascriptId, $uniqueId);

        // Set a hidden field with no value and use javascript to set it.
        $output = '
    <input type="hidden" id="'.$uniqueId.'" name="'.$uniqueId.'" />
    <script type="text/javascript">
        document.getElementById("'.$uniqueId.'").value = "'.$uniqueId.'";
    </script>';

        return $output;
    }

    /**
     * @inheritdoc
     * @throws \craft\errors\MissingComponentException
     */
    public function verifySubmission(OnBeforeSaveEntryEvent $event): bool
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

            SproutForms::error('A form submission failed because the user did not have Javascript enabled.');

            $event->isValid = false;
            $event->fakeIt = true;

            return false;
        }

        // If there is a valid unique token set, unset it
        Craft::$app->getSession()->remove($this->javascriptId);

        return true;
    }
}



