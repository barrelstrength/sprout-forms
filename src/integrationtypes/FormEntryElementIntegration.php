<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ElementIntegration;
use Craft;

class FormEntryElementIntegration extends ElementIntegration
{
    /**
     * @var boolean
     */
    public $hasFieldMapping = false;

    public function getName()
    {
        return Craft::t('sprout-forms', 'Sprout Forms (Default)');
    }

    // Any general customizations we need specifically for Element Integrations

    // We may want to consider extending the Form Field API and adding support for Form Fields to identify what Field Class/Classes they can be mapped to. In the Element Integration case, this method resolves the field mapping by matching Sprout Form fields classes to Craft Field classes.
//    public function resolveFieldMapping() {}

    public function submit()
    {
        Craft::dd('Submitting Default Form Entry Element!');


// Whatever we do here we should behave the same as it currently does:

//        if ($this->form->submitAction && !$request->getIsCpRequest()) {
//            if (!SproutForms::$app->entries->forwardEntry($entry)) {
//                return $this->redirectWithErrors($entry);
//            }
//        }
//
//        return $this->saveEntryInCraft($entry);
    }

    /**
     * Return Class name as Type
     *
     * @return string
     */
    public function getType()
    {
        return self::class;
    }
}

