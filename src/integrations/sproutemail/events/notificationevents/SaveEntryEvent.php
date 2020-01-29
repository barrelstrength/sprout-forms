<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents;

use barrelstrength\sproutbaseemail\base\NotificationEvent;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\events\OnSaveEntryEvent;
use barrelstrength\sproutforms\services\Entries;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\events\ElementEvent;
use craft\events\ModelEvent;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;


/**
 * Class SaveEntryEvent
 *
 * @package barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents
 *
 * @property string $eventHandlerClassName
 * @property array  $allForms
 * @property Entry  $mockEventObject
 * @property null   $eventObject
 * @property mixed  $name
 * @property mixed  $eventName
 * @property string $eventClassName
 */
class SaveEntryEvent extends NotificationEvent
{
    public $whenNew;

    public $whenUpdated;

    public $availableForms;

    public $formIds = [];

    public $viewContext = 'sprout-forms';

    /**
     * @inheritdoc
     */
    public function getEventClassName()
    {
        return Entries::class;
    }

    /**
     * @inheritdoc
     */
    public function getEventName()
    {
        return Entry::EVENT_AFTER_SAVE;
    }

    /**
     * @inheritdoc
     */
    public function getEventHandlerClassName()
    {
        return ModelEvent::class;
    }

    public function getName(): string
    {
        return Craft::t('sprout-forms', 'When a form entry is saved (Sprout Forms)');
    }


    /**
     * @param array $context
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml($context = []): string
    {
        if (!$this->availableForms) {
            $this->availableForms = $this->getAllForms();
        }

        return Craft::$app->getView()->renderTemplate('sprout-forms/_integrations/sproutemail/events/notificationevents/SaveEntryEvent/settings', [
            'event' => $this
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getEventObject()
    {
        /**
         * @var ElementEvent $event
         */
        $event = $this->event ?? null;

        return $event->entry ?? null;
    }

    /**
     * @todo fix bug where incorrect form can be selected.
     *
     * @inheritdoc
     */
    public function getMockEventObject()
    {
        $criteria = Entry::find();
        $criteria->orderBy(['id' => SORT_DESC]);

        if (!empty($this->formIds)) {

            if (count($this->formIds) == 1) {
                $formId = $this->formIds[0];
            } else {
                $formId = array_shift($this->formIds);
            }

            $criteria->formId = $formId;
        }

        $formEntry = $criteria->one();

        if ($formEntry) {
            return $formEntry;
        }

        Craft::warning('sprout-forms', 'Unable to generate a mock form Entry. Make sure you have at least one Entry submitted in your database.');

        return null;
    }

    /**
     * @return array
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [
            'whenNew', 'required', 'when' => function() {
                return $this->whenUpdated == false;
            }
        ];
        $rules[] = [
            'whenUpdated', 'required', 'when' => function() {
                return $this->whenNew == false;
            }
        ];
        $rules[] = [['whenNew', 'whenUpdated'], 'validateWhenTriggers'];
        $rules[] = [['event'], 'validateEvent'];
        $rules[] = [['event'], 'validateCaptchas'];
        $rules[] = [['formIds'], 'validateFormIds'];

        return $rules;
    }

    public function validateWhenTriggers()
    {
        /**
         * @var ElementEvent $event
         */
        $event = $this->event ?? null;

        $isNewEntry = $event->isNewEntry ?? false;

        $matchesWhenNew = $this->whenNew && $isNewEntry ?? false;
        $matchesWhenUpdated = $this->whenUpdated && !$isNewEntry ?? false;

        if (!$matchesWhenNew && !$matchesWhenUpdated) {
            $this->addError('event', Craft::t('sprout-forms', 'When a form entry is saved Event does not match any scenarios.'));
        }

        // Make sure new entries are new.
        if (($this->whenNew && !$isNewEntry) && !$this->whenUpdated) {
            $this->addError('event', Craft::t('sprout-forms', '"When an entry is created" is selected but the entry is being updated.'));
        }

        // Make sure updated entries are not new
        if (($this->whenUpdated && $isNewEntry) && !$this->whenNew) {
            $this->addError('event', Craft::t('sprout-forms', '"When an entry is updated" is selected but the entry is new.'));
        }
    }

    public function validateEvent()
    {
        /** @var OnSaveEntryEvent $event */
        $event = $this->event ?? null;

        if (!$event) {
            $this->addError('event', Craft::t('sprout-forms', 'ElementEvent does not exist.'));
        }

        if (get_class($event->entry) !== Entry::class) {
            $this->addError('event', Craft::t('sprout-forms', 'Event Element does not match barrelstrength\sproutforms\elements\Entry class.'));
        }
    }

    public function validateCaptchas()
    {
        $entry = $this->event->entry;

        if ($entry->hasCaptchaErrors()) {
            $this->addError('event', Craft::t('sprout-forms', 'Submitted entry has captcha errors.'));
        }
    }

    public function validateFormIds()
    {
        /** @var OnSaveEntryEvent $event */
        $event = $this->event ?? null;

        $elementId = null;

        if (get_class($event->entry) === Entry::class) {
            /** @var Form $form */
            $form = $event->entry->getForm();
            $elementId = $form->id;
        }

        // If any section ids were checked, make sure the entry belongs in one of them
        if (!in_array($elementId, $this->formIds, false)) {
            $this->addError('event', Craft::t('sprout-forms', 'The Form associated with the saved Form Entry Element does not match any selected Forms.'));
        }
    }

    /**
     * Returns an array of forms suitable for use in checkbox field
     *
     * @return array
     */
    protected function getAllForms(): array
    {
        $forms = SproutForms::$app->forms->getAllForms();
        $options = [];

        foreach ($forms as $key => $form) {
            $options[] = [
                'label' => $form->name,
                'value' => $form->id
            ];
        }

        return $options;
    }
}
