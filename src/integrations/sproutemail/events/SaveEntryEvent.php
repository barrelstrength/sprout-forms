<?php

namespace barrelstrength\sproutforms\integrations\sproutemail\events;

use barrelstrength\sproutbase\contracts\sproutemail\BaseEvent;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\SproutForms;
use craft\services\Elements;
use craft\events\ElementEvent;
use Craft;
use yii\base\Event;

/**
 * Class SaveEntryEvent
 *
 * @package barrelstrength\sproutforms\integrations\sproutemail\events
 */
class SaveEntryEvent extends BaseEvent
{
    public function getEventParams()
    {
        return [
            'class' => Elements::class,
            'name' => Elements::EVENT_AFTER_SAVE_ELEMENT,
            'event' => ElementEvent::class
        ];
    }

    public function getTitle()
    {
        return Craft::t('sprout-forms', 'When a Sprout Forms entry is saved');
    }

    /**
     * @param array $context
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml($context = [])
    {
        if (!isset($context['availableForms'])) {
            $context['availableForms'] = $this->getAllForms();
        }

        return Craft::$app->getView()->renderTemplate('sprout-forms/_events/save-entry', $context);
    }

    public function prepareOptions()
    {
        $rules = Craft::$app->getRequest()->getBodyParam('rules.sproutForms');

        return [
            'sproutForms' => $rules,
        ];
    }

    /**
     * Returns whether or not the entry meets the criteria necessary to trigger the event
     *
     * @param mixed $options
     * @param Entry $entry
     * @param array $params
     *
     * @return bool
     */
    public function validateOptions($options, $entry, array $params = [])
    {
        $isNewEntry = isset($params['isNewEntry']) && $params['isNewEntry'];

        $whenNew = isset($options['sproutForms']['saveEntry']['whenNew']) &&
            $options['sproutForms']['saveEntry']['whenNew'];

        // If any section ids were checked
        // Make sure the entry belongs in one of them
        if (!empty($options['sproutForms']['saveEntry']['formIds']) &&
            count($options['sproutForms']['saveEntry']['formIds'])
        ) {
            if (!in_array($entry->getForm()->id, $options['sproutForms']['saveEntry']['formIds'])) {
                return false;
            }
        }

        // If only new entries was checked
        // Make sure the entry is new
        if (!$whenNew || ($whenNew && $isNewEntry)) {
            return true;
        }

        return false;
    }

    /**
     * @param Event $event
     *
     * @return array|bool|mixed
     * @throws \craft\errors\SiteNotFoundException
     */
    public function prepareParams(Event $event)
    {
        if ($this->_isElementEntry($event) == false) {
            return false;
        }

        return ['value' => $event->element, 'isNewEntry' => $event->isNew];
    }

    /**
     * @param $event
     *
     * @return bool
     * @throws \craft\errors\SiteNotFoundException
     */
    private function _isElementEntry($event)
    {
        $element = get_class($event->element);

        $primarySite = Craft::$app->getSites()->getPrimarySite();

        // Ensure that only User Element class get triggered.
        if ($element != Entry::class) {
            return false;
        }

        // This will ensure that the event will trigger only once
        if ($primarySite->id != $event->element->siteId) {
            return false;
        }

        return true;
    }

    /**
     * @return array|\craft\base\ElementInterface|null
     */
    public function getMockedParams()
    {
        $criteria = Entry::find();

        $formIds = $this->options['sproutForms']['saveEntry']['formIds'] ?? [];

        if (is_array($formIds) && count($formIds)) {
            $formId = array_shift($formIds);

            $criteria->formId = $formId;
        }

        return $criteria->one();
    }

    /**
     * Returns an array of forms suitable for use in checkbox field
     *
     * @return array
     */
    protected function getAllForms()
    {
        $forms = SproutForms::$app->forms->getAllForms();
        $options = [];

        if (count($forms)) {
            foreach ($forms as $key => $form) {
                $options[] = [
                    'label' => $form->name,
                    'value' => $form->id
                ];
            }
        }

        return $options;
    }
}
