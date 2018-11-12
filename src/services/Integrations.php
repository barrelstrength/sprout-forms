<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\SproutForms;
use craft\base\Component;
use craft\events\RegisterComponentTypesEvent;

class Integrations extends Component
{
    const EVENT_REGISTER_INTEGRATIONS = 'registerIntegrations';

    public function getAllIntegrationTypes()
    {
        $event = new RegisterComponentTypesEvent([
            'types' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_INTEGRATIONS, $event);

        return $event->types;
    }

    public function getAllIntegrations()
    {
        $integrationTypes = SproutForms::$app->integrations->getAllIntegrationTypes();

        $integrations = [];

        foreach ($integrationTypes as $integrationType)
        {
            $integrations[] = new $integrationType();
        }

        return $integrations;
    }
}
