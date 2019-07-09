<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\base\IntegrationInterface;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\events\OnAfterIntegrationSubmit;
use barrelstrength\sproutforms\integrationtypes\MissingIntegration;
use barrelstrength\sproutforms\models\SubmissionLog;
use barrelstrength\sproutforms\records\SubmissionLog as SubmissionLogRecord;
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
use yii\base\Exception;
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

    const ENTRY_INTEGRATION_PENDING_STATUS = 'pending';
    const ENTRY_INTEGRATION_COMPLETED_STATUS = 'completed';

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
                'integrations.confirmation',
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
                'integrations.enabled',
                'integrations.confirmation'
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
        $integrationRecord->confirmation = $integration->confirmation;

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
     * @param $submissionLog SubmissionLog
     *
     * @return mixed
     * @throws Exception
     */
    public function logSubmission(SubmissionLog $submissionLog)
    {
        $submissionLogRecord = new SubmissionLogRecord();
        if ($submissionLog->id) {
            $submissionLogRecord = SubmissionLogRecord::findOne($submissionLog->id);
            if (!$submissionLogRecord) {
                throw new Exception(Craft::t('sprout-forms', 'No integration entry exists with id '.$submissionLog->id));
            }
        }

        $submissionLogRecord->entryId = $submissionLog->entryId;
        $submissionLogRecord->integrationId = $submissionLog->integrationId;
        $submissionLogRecord->success = $submissionLog->success;
        if (is_array($submissionLog->message)) {
            $submissionLog->message = json_encode($submissionLog->message);
        }
        $submissionLogRecord->message = $submissionLog->message;
        $submissionLogRecord->status = $submissionLog->status;
        $submissionLogRecord->save();

        $submissionLog->setAttributes($submissionLogRecord->getAttributes(), false);

        return $submissionLog;
    }

    /**
     * @param $entryId
     *
     * @return array|ActiveRecord[]
     */
    public function getSubmissionLogsByEntryId($entryId): array
    {
        $submissionLogs = (new Query())
            ->select(['*'])
            ->from(['{{%sproutforms_log}}'])
            ->where(['entryId' => $entryId])
            ->all();

        return $submissionLogs;
    }

    /**
     * Run all the integrations related to the Form Element.
     *
     * @param Entry $entry
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws MissingComponentException
     * @throws \Throwable
     */
    public function runFormIntegrations(Entry $entry)
    {
        /** @var SproutForms $plugin */
        $plugin = SproutForms::getInstance();

        if (!Craft::$app->getRequest()->getIsSiteRequest() &&
            !$plugin->getSettings()->enableIntegrationsPerFormBasis) {
            return;
        }

        $form = $entry->getForm();
        $integrations = $this->getFormIntegrations($form->id);
        $submissionLogs = [];
        $entryId = $entry->id ?? null;

        foreach ($integrations as $integration) {
            if ($this->skipIntegration($integration, $entry)){
                continue;
            }

            if ($integration->enabled) {
                $submissionLog = new SubmissionLog();

                $submissionLog->setAttributes([
                    'integrationId' => $integration->id,
                    'entryId' => $entryId,
                    'success' => false,
                    'status' => self::ENTRY_INTEGRATION_PENDING_STATUS,
                    'message' => 'Pending'
                ], false);

                $submissionLog = SproutForms::$app->integrations->logSubmission($submissionLog
                );

                $submissionLogs[] = [
                    'integration' => $integration,
                    'submissionLog' => $submissionLog
                ];
            }
        }

        foreach ($submissionLogs as $submissionLog) {
            /** @var Integration $integration */
            $integration = $submissionLog['integration'];
            /** @var SubmissionLog $submissionLog */
            $submissionLog = $submissionLog['submissionLog'];

            $integration->entry = $entry;
            Craft::info(Craft::t('sprout-forms', 'Running Sprout Forms Integration: {integrationName}', [
                'integrationName' => $integration->name
            ]), __METHOD__);

            try {
                if ($integration->enabled) {
                    $result = $integration->submit();
                    // Success!
                    if ($result) {
                        $submissionLog->setAttributes([
                            'success' => true,
                            'status' => self::ENTRY_INTEGRATION_COMPLETED_STATUS,
                            'message' => $integration->getSuccessMessage()
                        ], false);

                        $submissionLog = SproutForms::$app->integrations->logSubmission($submissionLog);
                    }
                }
            } catch (\Exception $e) {
                $message = Craft::t('sprout-forms', 'Integration failed to submit: {message}', [
                    'message' => $e->getMessage()
                ]);
                $integration->addError('global', $message);
                Craft::error($message, __METHOD__);
            }

            $integrationErrors = $integration->getErrors();
            // Let's log errors
            if ($integrationErrors) {
                $errorMessages = [];
                foreach ($integrationErrors as $integrationError) {
                    $errorMessages[] = $integrationError;
                }

                $submissionLog->setAttributes([
                    'success' => false,
                    'message' => $errorMessages,
                    'status' => self::ENTRY_INTEGRATION_COMPLETED_STATUS
                ], false);

                $submissionLog = SproutForms::$app->integrations->logSubmission($submissionLog
                );
            }

            $event = new OnAfterIntegrationSubmit([
                'submissionLog' => $submissionLog
            ]);

            $this->trigger(self::EVENT_AFTER_INTEGRATION_SUBMIT, $event);
        }
    }

    /**
     * @param $integration
     * @param $entry
     * @return bool
     * @throws \Throwable
     */
    private function skipIntegration($integration, $entry)
    {
        // By default is always
        if (empty($integration->confirmation)){
            return false;
        }

        // it's a opt-in field
        if (isset($entry->{$integration->confirmation})){
            if (!$entry->{$integration->confirmation}){
                // skip this integration
                return true;
            }
        }else{// its a custom confirmation
            try {
                $value = trim(Craft::$app->view->renderObjectTemplate($integration->confirmation, $entry));
                if (!filter_var($value, FILTER_VALIDATE_BOOLEAN)){
                    return true;
                }
            } catch (\Exception $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }

        return false;
    }
}
