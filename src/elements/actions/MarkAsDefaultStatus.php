<?php

namespace barrelstrength\sproutforms\elements\actions;

use barrelstrength\sproutforms\models\EntryStatus;
use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

use barrelstrength\sproutforms\SproutForms;
use Throwable;

/**
 *
 * @property string $triggerLabel
 */
class MarkAsDefaultStatus extends ElementAction
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

    /**
     * @var EntryStatus
     */
    public $entryStatus;

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        $this->entryStatus = SproutForms::$app->entries->getDefaultEntryStatus();
    }

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('sprout-forms', 'Mark as '.$this->entryStatus->name);
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage()
    {
        return Craft::t('sprout-forms', 'Are you sure you want to mark the selected form entries as {statusName}', [
            'statusName' => $this->entryStatus->name
        ]);
    }

    /**
     * @inheritdoc
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $message = null;

        $response = SproutForms::$app->entries->markAsDefaultStatus($query->all());

        if ($response) {
            $message = Craft::t('sprout-forms', 'Entries marked as {statusName}.', [
                'statusName' => $this->entryStatus->name
            ]);
        } else {
            $message = Craft::t('sprout-forms', 'Unable to mark entries as {statusName}.', [
                'statusName' => $this->entryStatus->name
            ]);
        }

        $this->setMessage($message);

        return $response;
    }
}
