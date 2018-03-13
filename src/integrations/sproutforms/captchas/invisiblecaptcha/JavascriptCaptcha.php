<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\captchas\invisiblecaptcha;

use barrelstrength\sproutforms\contracts\BaseCaptcha;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\SproutForms;
use Craft;

/**
 * Class InvisibleCaptcha
 */
class JavascriptCaptcha extends BaseCaptcha
{
    public function getDescription()
    {
        return Craft::t('sprout-forms','Prevent a form from being submmitted if a user does not have JavaScript enabled');
    }

    public function getName()
    {
        return 'Javascript Captcha';
    }

    public function getCaptchaHtml()
    {
        // Create the unique token
        $uniqueId = uniqid();

        // Create session variable to test for javascript
        Craft::$app->getSession()->set('invisibleCaptchaJavascriptId', $uniqueId);

        return $this->getField();
    }

    /**
     * Verify Submission
     *
     * @param $event
     *
     * @return boolean
     */
    public function verifySubmission(OnBeforeSaveEntryEvent $event): bool
    {
        // Only do this on the front-end
        if (Craft::$app->getRequest()->getIsCpRequest()) {
            return true;
        }

        $jsset = null;

        foreach ($_POST as $key => $value) {
            // Fix issue on multiple forms on same page
            if (strpos($key, '__JSCHK') === 0) {
                $jsset = $_POST[$key];
                break;
            }
        }

        if (empty($jsset)) {
            SproutForms::error('A form submission failed because the user did not have Javascript enabled.');
            $event->isValid = false;
            $event->fakeIt = true;

            if (Craft::$app->getRequest()->getBodyParam('redirectOnFailure') != "") {
                $_POST['redirect'] = Craft::$app->getRequest()->getBodyParam('redirectOnFailure');
            }

            return false;
        }

        // If there is a valid unique token set, unset it and return true.
        // This token was created and set by javascript.
        Craft::$app->getSession()->remove('invisibleCaptchaJavascriptId');
        return true;
    }

    /**
     * @return string
     */
    private function getField()
    {
        $jsCheck = Craft::$app->getSession()->get('invisibleCaptchaJavascriptId');

        // Set a hidden field with no value and use javascript to set it.
        $output = '';
        $output .= sprintf('<input type="hidden" id="__JSCHK_%s" name="__JSCHK_%s" />', $jsCheck, $jsCheck);
        $output .= sprintf('<script type="text/javascript">document.getElementById("__JSCHK_%s").value = "%s";</script>', $jsCheck, $jsCheck);

        return $output;
    }
}



