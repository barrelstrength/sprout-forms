<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\captchas\invisiblecaptcha;

use barrelstrength\sproutforms\contracts\BaseCaptcha;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\SproutForms;
use Craft;

/**
 * Class DuplicateCaptcha
 */
class DuplicateCaptcha extends BaseCaptcha
{
    private $duplicateId = 'invisibleCaptchaDuplicateId';

    public function getDescription()
    {
        return Craft::t('sprout-forms','Prevent duplicate submissions if a user hits submit more than once');
    }

    public function getName()
    {
        return 'Duplicate Submission Captcha';
    }

    public function getCaptchaHtml()
    {
        // Create the unique token
        $uniqueId = uniqid();

        // Create session variable
        Craft::$app->getSession()->set($this->duplicateId, $uniqueId);

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
        if(Craft::$app->getSession()->get($this->duplicateId))
        {
            // If there is a valid unique token set, unset it and return true.
            Craft::$app->getSession()->remove($this->duplicateId);
            return true;
        }

        $event->isValid = false;
        $event->fakeIt = true;

        if (Craft::$app->getRequest()->getBodyParam('redirectOnFailure') != "") {
            $_POST['redirect'] = Craft::$app->getRequest()->getBodyParam('redirectOnFailure');
        }

        SproutForms::error("A form submission failed the Duplicate Submission test.");

        return false;
    }

    /**
     * @return string
     */
    private function getField()
    {
        return '';
    }
}



