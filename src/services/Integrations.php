<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Component;
use craft\events\RegisterComponentTypesEvent;
use Craft;

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
     * @param $type
     * @param $form
     * @param $name
     * @return IntegrationRecord|null
     */
    public function createIntegration($type, $form, $name = null)
    {
        $integration = null;
        $integrationRecord = new IntegrationRecord();
        $integrationRecord->type = $type;
        $integrationRecord->formId = $form->id;
        $integrationRecord->name = $name ?? $integrationRecord->getIntegrationApi()->getName();

        if ($integrationRecord->save()){
            $integration = $integrationRecord;
        }

        return $integration;
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
     * Returns a integration type selection array grouped by category
     *
     * Categories
     * - Standard integrations
     * - Custom integrations that need to be registered using the Sprout Forms Integrations API
     *
     * @return array
     */
    public function prepareIntegrationTypeSelection()
    {
        $integrations = $this->getAllIntegrations();
        $standardIntegrations = [];

        if (count($integrations)) {
            // Loop through registered integrations and add them to the standard group
            foreach ($integrations as $class => $integration) {
                $standardIntegrations[$class] = $integration->getName();
            }

            // Sort fields alphabetically by name
            asort($standardIntegrations);

            // Add the group label to the beginning of the standard group
            $standardIntegrations = SproutForms::$app->fields->prependKeyValue($standardIntegrations, 'standardIntegrationsGroup', ['optgroup' => Craft::t('sprout-forms', 'Standard Integrations')]);
        }

        return $standardIntegrations;
    }

    /**
     * Loads the sprout modal integration via ajax.
     *
     * @param $form
     * @param null $integration
     * @return array
     * @throws \yii\base\Exception
     */
    public function getModalIntegrationTemplate($form, $integration = null)
    {
        $data = [];
        $data['integration'] = $integration;
        $data['integrationId'] = $integration->id;

        $data['form'] = $form;
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
