<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ApiIntegration;
use Craft;

class CustomIntegration extends ApiIntegration
{
    public $submitAction;

    public function getName() {
        return Craft::t('sprout-forms', 'Custom (Payload Forwarding)');
    }
    // Any general customizations we need specifically for Element Integrations

    // We may want to consider extending the Form Field API and adding support for Form Fields to identify what Field Class/Classes they can be mapped to. In the Element Integration case, this method resolves the field mapping by matching Sprout Form fields classes to Craft Field classes.
//    public function resolveFieldMapping() {}

    public function getSettingsHtml() {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/integrationtypes/custom/settings',
            [
                'integration' => $this
            ]
        );
    }

    public function submit() {
        // Send payload to $this->submitAction URL
        Craft::dd('Submitting Custom Integration!');
    }

    /**
     * Return Class name as Type
     *
     * @return string
     */
    public function getType() {
        return self::class;
    }
}

