<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\base\IntegrationInterface;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\events\OnAfterIntegrationSubmit;
use barrelstrength\sproutforms\integrationtypes\MissingIntegration;
use barrelstrength\sproutforms\models\EntryIntegration;
use barrelstrength\sproutforms\records\EntryIntegrationLog;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Component;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use Craft;
use craft\helpers\Component as ComponentHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;
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
     * @event OnAfterIntegrationSubmit The event that is triggered when the integration is submitted
     */
    const EVENT_AFTER_INTEGRATION_SUBMIT = 'afterIntegrationSubmit';

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
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function getFormIntegrations($formId): array
    {
        $results = (new Query())
            ->select([
                'integrations.id',
                'integrations.formId',
                'integrations.name',
                'integrations.type',
                'integrations.settings',
                'integrations.enabled'
            ])
            ->from(['{{%sproutforms_integrations}} integrations'])
            ->where(['integrations.formId' => $formId])
            ->all();

        $integrations = [];

        foreach ($results as $result) {
            $integration = ComponentHelper::createComponent($result, IntegrationInterface::class);
            $integrations[] = new $result['type']($integration);
        }

        return $integrations;
    }

    /**
     * @param $integrationId
     *
     * @return Integration|null
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function getIntegrationById($integrationId)
    {
        $result = (new Query())
            ->select([
                'integrations.id',
                'integrations.formId',
                'integrations.name',
                'integrations.type',
                'integrations.settings',
                'integrations.enabled'
            ])
            ->from(['{{%sproutforms_integrations}} integrations'])
            ->where(['integrations.id' => $integrationId])
            ->one();

        if (!$result) {
            return null;
        }

        $integration = ComponentHelper::createComponent($result, IntegrationInterface::class);

        return new $result['type']($integration);
    }

    /**
     * @param Integration $integration
     *
     * @return bool
     */
    public function saveIntegration(Integration $integration): bool
    {
        if ($integration->id) {
            $integrationRecord = IntegrationRecord::findOne($integration->id);
        } else {
            $integrationRecord = new IntegrationRecord();
        }

        $integrationRecord->type = get_class($integration);
        $integrationRecord->formId = $integration->formId;
        $integrationRecord->name = $integration->name ?? $integration::displayName();
        $integrationRecord->enabled = $integration->enabled;

        $integrationRecord->settings = $integration->getSettings();

        if ($integrationRecord->save()) {
            $integration->id = $integrationRecord->id;
            $integration->name = $integrationRecord->name;
            return true;
        }

        return false;
    }

    /**
     * @param $config
     *
     * @return IntegrationInterface
     * @throws InvalidConfigException
     */
    public function createIntegration($config): IntegrationInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        try {
            /** @var Integration $integration */
            $integration = ComponentHelper::createComponent($config, IntegrationInterface::class);
        } catch (MissingComponentException $e) {
            $config['errorMessage'] = $e->getMessage();
            $config['expectedType'] = $config['type'];
            unset($config['type']);

            $integration = new MissingIntegration($config);
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
                $standardIntegrations[get_class($integration)] = $integration::displayName();
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
     * @param Integration $integration
     *
     * @return array
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getModalIntegrationTemplate(Integration $integration): array
    {
        $view = Craft::$app->getView();

        $html = $view->renderTemplate('sprout-forms/forms/_editIntegrationModal', [
            'integration' => $integration,
        ]);

        $js = $view->getBodyHtml();
        $css = $view->getHeadHtml();

        return [
            'html' => $html,
            'js' => $js,
            'css' => $css
        ];
    }

    /**
     * @param $entryIntegrationModel EntryIntegration
     * @return EntryIntegration
     */
    public function saveEntryIntegrationLog($entryIntegrationModel)
    {
        $entryIntegration = new EntryIntegrationLog();
        $entryIntegration->entryId = $entryIntegrationModel->entryId;
        $entryIntegration->integrationId = $entryIntegrationModel->integrationId;
        $entryIntegration->isValid = $entryIntegrationModel->isValid;
        if (is_array($entryIntegrationModel->message)) {
            $entryIntegrationModel->message = json_encode($entryIntegrationModel->message);
        }
        $entryIntegration->message = $entryIntegrationModel->message;
        $entryIntegration->save();

        $entryIntegrationModel->setAttributes($entryIntegration->getAttributes(), false);

        return $entryIntegrationModel;
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
     *
     * @throws InvalidConfigException
     * @throws MissingComponentException
     */
    public function runEntryIntegrations(Entry $entry)
    {
        if (!Craft::$app->getRequest()->getIsSiteRequest() &&
            !$this->getSettings()->enableIntegrationsPerFormBasis) {
            return;
        }

        $form = $entry->getForm();
        $integrations = $this->getFormIntegrations($form->id);

        foreach ($integrations as $integration) {
            $integration->entry = $entry;
            $entryId = $entry->id ?? null;
            Craft::info(Craft::t('sprout-forms', 'Running Sprout Forms Integration: {integrationName}', [
                'integrationName' => $integration->name
            ]), __METHOD__);

            $entryIntegration = null;

            try {
                if ($integration->enabled) {
                    $result = $integration->submit();
                    // Success!
                    if ($result) {
                        $entryIntegrationModel = new EntryIntegration();

                        $entryIntegrationModel->setAttributes([
                            'integrationId' => $integration->id,
                            'entryId' => $entryId,
                            'isValid' => true,
                            'message' => $integration->getSuccessMessage()
                        ], false);

                        $entryIntegration = SproutForms::$app->integrations->saveEntryIntegrationLog($entryIntegrationModel);
                    }
                }
            } catch (\Exception $e) {
                $message = 'Submit Integration Api fails: '.$e->getMessage();
                $integration->addError('global', $message);
                Craft::error($message, __METHOD__);
            }

            $integrationErrors = $integration->getErrors();
            // Let's log errors
            if ($integrationErrors) {
                $errorMessages = [];
                foreach ($integrationErrors as $integrationLog) {
                    array_push($errorMessages, $integrationLog);
                }

                $entryIntegrationModel = new EntryIntegration();

                $entryIntegrationModel->setAttributes([
                    'integrationId' => $integration->id,
                    'entryId' => $entryId,
                    'isValid' => false,
                    'message' => $errorMessages
                ], false);

                $entryIntegration = SproutForms::$app->integrations->saveEntryIntegrationLog($entryIntegrationModel
                );
            }

            $event = new OnAfterIntegrationSubmit([
                'entryIntegration' => $entryIntegration
            ]);

            $this->trigger(self::EVENT_AFTER_INTEGRATION_SUBMIT, $event);
        }
    }
}
