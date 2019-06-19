<?php

namespace barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents;

use barrelstrength\sproutbaseemail\base\NotificationEvent;

use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\events\OnAfterIntegrationSubmit;
use barrelstrength\sproutforms\events\OnSaveEntryEvent;
use barrelstrength\sproutforms\services\Integrations;
use barrelstrength\sproutforms\SproutForms;
use craft\events\ElementEvent;
use Craft;


/**
 * Class LogEvent
 *
 * @package barrelstrength\sproutforms\integrations\sproutemail\events\notificationevents
 *
 * @property string                                     $eventHandlerClassName
 * @property array                                      $allForms
 * @property \barrelstrength\sproutforms\elements\Entry $mockEventObject
 * @property null                                       $eventObject
 * @property mixed                                      $name
 * @property mixed                                      $eventName
 * @property string                                     $eventClassName
 */
class LogEvent extends NotificationEvent
{
    public $whenSubmit;

    public $availableForms;

    /**
     * @inheritdoc
     */
    public function getEventClassName()
    {
        return Integrations::class;
    }

    /**
     * @inheritdoc
     */
    public function getEventName()
    {
        return Integrations::EVENT_AFTER_INTEGRATION_SUBMIT;
    }

    /**
     * @inheritdoc
     */
    public function getEventHandlerClassName()
    {
        return OnAfterIntegrationSubmit::class;
    }

    public function getName(): string
    {
        return Craft::t('sprout-forms', 'When an integration is logged (Sprout Forms)');
    }

    /**
     * @inheritdoc
     *
     * @param array $context
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function getSettingsHtml($context = []): string
    {
        if (!$this->availableForms) {
            $this->availableForms = $this->getAllForms();
        }

        return Craft::$app->getView()->renderTemplate('sprout-forms/_integrations/sproutemail/events/notificationevents/LogEvent/settings', [
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

        return $event->submissionLog ?? null;
    }

    /**
     * @todo fix bug where incorrect form can be selected.
     *
     * @inheritdoc
     */
    public function getMockEventObject()
    {
        // @todo implement mock event
        /*
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
        */
        Craft::warning('sprout-forms', 'Unable to generate a mock form Entry. Make sure you have at least one Entry submitted in your database.');

        return null;
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules[] = [
            'whenSubmit', 'required'
        ];

        //@todo research about validateEvent
        //$rules[] = [['event'], 'validateEvent'];

        return $rules;
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
