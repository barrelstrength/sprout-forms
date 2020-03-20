<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\base\IntegrationInterface;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\events\OnAfterIntegrationSubmit;
use barrelstrength\sproutforms\integrationtypes\MissingIntegration;
use barrelstrength\sproutforms\models\IntegrationLog;
use barrelstrength\sproutforms\records\Integration as IntegrationRecord;
use barrelstrength\sproutforms\records\IntegrationLog as IntegrationLogRecord;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\errors\MissingComponentException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\Component as ComponentHelper;
use Throwable;
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
    const ENTRY_INTEGRATION_NOT_SENT_STATUS = 'notsent';
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
    public function getIntegrationsByFormId($formId): array
    {
        $results = (new Query())
            ->select([
                'integrations.id',
                'integrations.formId',
                'integrations.name',
                'integrations.type',
                'integrations.sendRule',
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
                'integrations.sendRule',
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
        $integrationRecord->sendRule = $integration->sendRule;

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
     * Loads the sprout modal integration via ajax.
     *
     * @param Integration $integration
     *
     * @return array
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
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
     * @param $integrationLog IntegrationLog
     *
     * @return mixed
     * @throws Exception
     */
    public function logIntegration(IntegrationLog $integrationLog)
    {
        $integrationLogRecord = new IntegrationLogRecord();
        if ($integrationLog->id) {
            $integrationLogRecord = IntegrationLogRecord::findOne($integrationLog->id);
            if (!$integrationLogRecord) {
                throw new Exception('No integration entry exists with id '.$integrationLog->id);
            }
        }

        $integrationLogRecord->entryId = $integrationLog->entryId;
        $integrationLogRecord->integrationId = $integrationLog->integrationId;
        $integrationLogRecord->success = $integrationLog->success;
        if (is_array($integrationLog->message)) {
            $integrationLog->message = json_encode($integrationLog->message);
        }
        $integrationLogRecord->message = $integrationLog->message;
        $integrationLogRecord->status = $integrationLog->status;
        $integrationLogRecord->save();

        $integrationLog->setAttributes($integrationLogRecord->getAttributes(), false);

        return $integrationLog;
    }

    /**
     * @param $entryId
     *
     * @return array|ActiveRecord[]
     */
    public function getIntegrationLogsByEntryId($entryId): array
    {
        $integrationLogs = (new Query())
            ->select(['*'])
            ->from(['{{%sproutforms_integrations_log}}'])
            ->where(['entryId' => $entryId])
            ->all();

        return $integrationLogs;
    }

    /**
     * Run all the integrations related to the Form Element.
     *
     * @param Entry $entry
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws MissingComponentException
     * @throws Throwable
     */
    public function runFormIntegrations(Entry $entry)
    {
        if ($entry->hasCaptchaErrors()) {
            return;
        }

        $form = $entry->getForm();
        $integrations = $this->getIntegrationsByFormId($form->id);

        if (!Craft::$app->getRequest()->getIsSiteRequest() || empty($integrations)) {
            return;
        }

        $integrationLogs = [];
        $entryId = $entry->id ?? null;

        // Add all enabled Integrations to the log as 'Pending'
        foreach ($integrations as $integration) {
            if ($integration->enabled) {
                $integrationLog = new IntegrationLog();

                $integrationLog->setAttributes([
                    'integrationId' => $integration->id,
                    'entryId' => $entryId,
                    'success' => false,
                    'status' => self::ENTRY_INTEGRATION_PENDING_STATUS,
                    'message' => 'Pending'
                ], false);

                $integrationLog = SproutForms::$app->integrations->logIntegration($integrationLog);

                $integrationLogs[] = [
                    'integration' => $integration,
                    'integrationLog' => $integrationLog
                ];
            }
        }

        // Process and Send Integrations one by one
        foreach ($integrationLogs as $integrationLog) {
            /** @var Integration $integration */
            $integration = $integrationLog['integration'];
            /** @var IntegrationLog $integrationLog */
            $integrationLog = $integrationLog['integrationLog'];

            $integration->formEntry = $entry;

            Craft::info(Craft::t('sprout-forms', 'Running Integration: {integrationName} for Form Entry {entryId}', [
                'integrationName' => $integration->name,
                'entryId' => $entryId
            ]), __METHOD__);

            if (!$this->sendRuleIsTrue($integration, $entry)) {
                $integrationNotSentMessage = Craft::t('sprout-forms', 'Integration not sent. Send Rule setting did not evaluate to true.');

                Craft::info($integrationNotSentMessage, __METHOD__);

                $integrationLog->setAttributes([
                    'success' => true,
                    'status' => self::ENTRY_INTEGRATION_NOT_SENT_STATUS,
                    'message' => $integrationNotSentMessage
                ], false);

                SproutForms::$app->integrations->logIntegration($integrationLog);

                continue;
            }

            try {
                if ($integration->enabled) {
                    $result = $integration->submit();
                    // Success!
                    if ($result) {
                        $integrationLog->setAttributes([
                            'success' => true,
                            'status' => self::ENTRY_INTEGRATION_COMPLETED_STATUS,
                            'message' => $integration->getSuccessMessage()
                        ], false);

                        $integrationLog = SproutForms::$app->integrations->logIntegration($integrationLog);
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

                $integrationLog->setAttributes([
                    'success' => false,
                    'message' => $errorMessages,
                    'status' => self::ENTRY_INTEGRATION_COMPLETED_STATUS
                ], false);

                $integrationLog = SproutForms::$app->integrations->logIntegration($integrationLog
                );
            }

            $event = new OnAfterIntegrationSubmit([
                'integrationLog' => $integrationLog
            ]);

            $this->trigger(self::EVENT_AFTER_INTEGRATION_SUBMIT, $event);
        }
    }

    /**
     * @param $integration
     * @param $entry
     *
     * @return bool
     * @throws Throwable
     */
    private function sendRuleIsTrue(Integration $integration, $entry): bool
    {
        // Default setting: Always = *
        if ($integration->sendRule === '*') {
            return true;
        }

        // If the rule name matches an Opt-in field handle, see if the Opt-in field is checked
        if (isset($entry->{$integration->sendRule}) && $entry->{$integration->sendRule}) {
            return true;
        }

        // Custom Send Rule
        try {
            $resultTemplate = Craft::$app->view->renderObjectTemplate($integration->sendRule, $entry);
            $value = trim($resultTemplate);
            if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                return true;
            }
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        return false;
    }
}
