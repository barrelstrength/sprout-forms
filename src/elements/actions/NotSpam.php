<?php

namespace barrelstrength\sproutforms\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

use barrelstrength\sproutforms\SproutForms;
use Throwable;

/**
 *
 * @property string $triggerLabel
 */
class NotSpam extends ElementAction
{
    // Properties
    // =========================================================================

    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage;

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('sprout-forms', 'Not Spam');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return Craft::t('sprout-forms', 'Are you sure you want to not mark as spam the selected form entries?');
    }

    /**
     * @inheritdoc
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $message = null;

        $response = SproutForms::$app->entries->markAsNotSpam($query->all());

        if ($response) {
            $message = Craft::t('sprout-forms', 'Entries marked as Not Spam.');
        } else {
            $message = Craft::t('sprout-forms', 'Failed to mark entry as Not Spam');
        }

        $this->setMessage($message);

        return $response;
    }
}
