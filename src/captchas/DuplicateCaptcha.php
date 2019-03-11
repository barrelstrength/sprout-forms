<?php

namespace barrelstrength\sproutforms\captchas;

use barrelstrength\sproutforms\base\Captcha;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\SproutForms;
use Craft;

/**
 * Class DuplicateCaptcha
 */
class DuplicateCaptcha extends Captcha
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Duplicate Submission Captcha';
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return Craft::t('sprout-forms', 'Prevent duplicate submissions if a user hits submit more than once');
    }

    /**
     * @inheritdoc
     * @throws \craft\errors\MissingComponentException
     */
    public function getCaptchaHtml(): string
    {
        $inputName = uniqid('dupe', true);
        $uniqueKeyId = uniqid('dupe', true);

        // Set a session variable with a unique key. It doesn't matter what the value of this is
        // we'll save the unique key in a hidden field and check for and remove the session based
        // on the session key if it exists, so we can only validate a submission the first time
        // it is used.
        Craft::$app->getSession()->set($uniqueKeyId, true);

        return '<input type="hidden" name="'.$inputName.'" value="'.$uniqueKeyId.'" />';
    }

    /**
     * @inheritdoc
     * @throws \craft\errors\MissingComponentException
     * @throws \craft\errors\MissingComponentException
     */
    public function verifySubmission(OnBeforeSaveEntryEvent $event): bool
    {
        $uniqueid = null;

        foreach ($_POST as $key => $value) {
            // Fix issue on multiple forms on same page
            if (strpos($key, 'dupe') === 0) {
                $uniqueid = $_POST[$key];
                break;
            }
        }

        if (!Craft::$app->getSession()->get($uniqueid)) {
            SproutForms::error('A form submission failed the Duplicate Submission test.');

            $event->isValid = false;

            return false;
        }

        // If we have a duplicate key, unset our test variable so we don't have it on the next request
        Craft::$app->getSession()->remove($uniqueid);

        return true;
    }
}



