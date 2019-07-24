<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ConditionalLogic;
use Craft;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;

/**
 * Add a conditional logic to show or hide a Form field
 */
class FieldRule extends ConditionalLogic
{
    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Field Rule');
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml()
    {
        $this->prepareFieldMapping();

        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/conditionallogictypes/fieldrule/settings',
            [
                'integration' => $this
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function submit(): bool
    {
        if ($this->submitAction == '' || Craft::$app->getRequest()->getIsCpRequest()) {
            return false;
        }

        $entry = $this->entry;
        $fields = $this->resolveFieldMapping();
        $endpoint = $this->submitAction;

        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            $message = $entry->formName.' submit action is an invalid URL: '.$endpoint;
            $this->addError('global', $message);
            Craft::error($message, __METHOD__);

            return false;
        }

        $client = new Client();

        Craft::info($fields, __METHOD__);

        $response = $client->post($endpoint, [
            RequestOptions::JSON => $fields
        ]);

        $res = $response->getBody()->getContents();
        $resAsString = is_array($res) ? json_encode($res) : $res;
        $this->successMessage = $resAsString;
        Craft::info($res, __METHOD__);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function resolveFieldMapping(): array
    {
        $fields = [];
        $entry = $this->entry;

        if ($this->fieldMapping) {
            foreach ($this->fieldMapping as $fieldMap) {
                if (isset($entry->{$fieldMap['sourceFormField']}) && $fieldMap['targetIntegrationField']) {
                    $fields[$fieldMap['targetIntegrationField']] = $entry->{$fieldMap['sourceFormField']};
                }
            }
        }

        return $fields;
    }
}

