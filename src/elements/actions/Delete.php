<?php

namespace barrelstrength\sproutforms\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

use barrelstrength\sproutforms\SproutForms;

/**
 *
 * @property string $triggerLabel
 */
class Delete extends ElementAction
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
        return Craft::t('app', 'Delete…');
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return Craft::t('sprout-forms', 'Are you sure you want to delete the selected forms?');
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $message = null;

        $response = SproutForms::$app->forms->deleteForms($query->all());

        if ($response) {
            $message = Craft::t('sprout-forms', 'Forms Deleted.');
        } else {
            $message = Craft::t('sprout-forms', 'Failed to delete forms.');
        }

        $this->setMessage($message);

        return $response;
    }
}
