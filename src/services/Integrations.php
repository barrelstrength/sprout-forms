<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use barrelstrength\sproutforms\elements\Form;
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

    /**
     * @return Integration[]
     */
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

    /**
     * @param $formId
     * @return IntegrationRecord[]
     */
    public function getFormIntegrations($formId)
    {
        $integrations = IntegrationRecord::findAll(['formId' => $formId]);

        return $integrations;
    }

    /**
     * Loads the sprout modal integration via ajax.
     *
     * @param      $form Form
     * @param null $integration
     * @param null $tabId
     *
     * @return array
     */
    public function getModalIntegrationTemplate($form, $integration = null, $tabId = null)
    {
        $data = [];
        $data['tabId'] = null;
        $data['field'] = $this->createInteration();

        if ($integration) {
            $data['interation'] = $integration;

            if ($integration->id != null) {
                $data['integrationId'] = $integration->id;
            }
        }

        $data['form'] = $form;
        $data['integrationClass'] = $integration->type;
        $view = Craft::$app->getView();

        $html = $view->renderTemplate('sprout-forms/forms/_editIntegrationModal', $data);
        $js = $view->getBodyHtml();
        $css = $view->getHeadHtml();

        return [
            'html' => $html,
            'js' => $js,
            'css' => $css
        ];
    }
}
