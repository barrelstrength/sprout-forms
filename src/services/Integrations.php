<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\records\EntryIntegrationLog;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Component;
use craft\db\Query;
use craft\events\RegisterComponentTypesEvent;
use Craft;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\db\ActiveRecord;

/**
 *
 * @property Integration[] $allIntegrations
 * @property mixed         $allIntegrationTypes
 */
class Integrations extends Component
{
    const EVENT_REGISTER_INTEGRATIONS = 'registerIntegrations';

    /**
     * Returns all registered Integration Types
     *
     * @return array
     */
    public function getAllIntegrationTypes(): array
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
    public function getAllIntegrations(): array
    {
        $integrationTypes = SproutForms::$app->integrations->getAllIntegrationTypes();

        $integrations = [];

        foreach ($integrationTypes as $integrationType) {
            $integrations[] = new $integrationType();
        }

        return $integrations;
    }

    /**
     * @param $formId
     *
     * @return IntegrationRecord[]
     */
    public function getFormIntegrations($formId): array
    {
        return IntegrationRecord::findAll([
            'formId' => $formId
        ]);
    }

    /**
     * @param $integrationId
     *
     * @return IntegrationRecord|null
     */
    public function getFormIntegrationById($integrationId)
    {
        return IntegrationRecord::findOne(['id' => $integrationId]);
    }

    /**
     * @param $type
     * @param $form
     * @param $name
     *
     * @return IntegrationRecord|null
     */
    public function createIntegration($type, $form, $name = null)
    {
        $integration = null;
        $integrationRecord = new IntegrationRecord();
        $integrationRecord->type = $type;
        $integrationRecord->formId = $form->id;
        $integrationRecord->name = $name ?? $integrationRecord->getIntegrationApi()->getName();
        $integrationRecord->enabled = false;

        if ($integrationRecord->save()) {
            $integration = $integrationRecord;
        }

        return $integration;
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
    public function prepareIntegrationTypeSelection(): array
    {
        $integrations = $this->getAllIntegrations();
        $standardIntegrations = [];

        if (count($integrations)) {
            // Loop through registered integrations and add them to the standard group
            foreach ($integrations as $class => $integration) {
                $standardIntegrations[get_class($integration)] = $integration->getName();
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
     * @param                  $form
     * @param Integration|null $integration
     *
     * @return array
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getModalIntegrationTemplate($form, $integration = null): array
    {
        $data = [];

        /** @var IntegrationRecord $integration */
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

    /**
     * @param       $integrationId
     * @param       $entryId
     * @param       $isValid
     * @param array $message
     *
     * @return bool
     */
    public function saveEntryIntegrationLog($integrationId, $entryId, $isValid, $message = []): bool
    {
        $entryIntegration = new EntryIntegrationLog();
        $entryIntegration->entryId = $entryId;
        $entryIntegration->integrationId = $integrationId;
        $entryIntegration->isValid = $isValid;
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $entryIntegration->message = $message;
        return $entryIntegration->save();
    }

    /**
     * @param $entryId
     *
     * @return array|ActiveRecord[]
     */
    public function getEntryIntegrationLogsByEntryId($entryId): array
    {
        $entryIntegrations = (new Query())
            ->select(['*'])
            ->from(['{{%sproutforms_integrations_entries}}'])
            ->where(['entryId' => $entryId])
            ->all();

        return $entryIntegrations;
    }

    /**
     * Run all the integrations related to the Form Element.
     *
     * @param Entry $entry
     */
    public function runEntryIntegrations(Entry $entry)
    {
        $form = $entry->getForm();
        /** @noinspection NullPointerExceptionInspection */
        $integrations = $this->getFormIntegrations($form->id);

        foreach ($integrations as $integrationRecord) {
            $integration = $integrationRecord->getIntegrationApi();
            $integration->entry = $entry;
            Craft::info(Craft::t('sprout-forms', 'Running Sprout Forms Integration: {integrationName}', [
                'integrationName' => $integration->name
            ]), __METHOD__);

            try {
                if ($integration->enabled) {
                    $integration->submit();
                }
            } catch (\Exception $e) {
                $message = 'Submit Integration Api fails: '.$e->getMessage();
                $integration->logResponse($message, $e->getTrace());
                Craft::error($message, __METHOD__);
            }
        }
    }
}
