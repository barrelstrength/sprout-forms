<?php

namespace barrelstrength\sproutforms\integrations\sproutforms\captchas;

use barrelstrength\sproutforms\contracts\BaseCaptcha;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\SproutForms;
use Craft;

/**
 * Class DuplicateCaptcha
 */
class DuplicateCaptcha extends BaseCaptcha
{
    /**
     * @var string
     */
    private $duplicateId = 'sprout-forms-duplicate-captcha';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Duplicate Submission Captcha';
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Craft::t('sprout-forms', 'Prevent duplicate submissions if a user hits submit more than once');
    }

    /**
     * @inheritdoc
     */
    public function getCaptchaHtml()
    {
        $uniqueId = uniqid('sprout', false);

        Craft::$app->getSession()->set($this->duplicateId, $uniqueId);

        return '';
    }

    /**
     * @inheritdoc
     */
    public function verifySubmission(OnBeforeSaveEntryEvent $event): bool
    {
        if (Craft::$app->getSession()->get($this->duplicateId)) {
            // If we have a duplicate, unset our test variable
            Craft::$app->getSession()->remove($this->duplicateId);

            return true;
        }

        SproutForms::error('A form submission failed the Duplicate Submission test.');

        $event->isValid = false;

        return false;
    }
}



