<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\Integration;
use Craft;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 * Route our request to Craft or a third-party endpoint
 *
 * Payload forwarding is only available on front-end requests. Any
 * data saved to the database after a forwarded request is editable
 * in Craft as normal, but will not trigger any further calls to
 * the third-party endpoint.
 */
class CustomEndpoint extends Integration
{
    /**
     * The URL to use when submitting the Form payload
     *
     * @var string
     */
    public $submitAction;

    /**
     * @inheritDoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Custom Endpoint');
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml()
    {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/integrationtypes/customendpoint/settings',
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

        $entry = $this->formEntry;
        $targetIntegrationFieldValues = $this->getTargetIntegrationFieldValues();
        $endpoint = $this->submitAction;

        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            $message = $entry->formName.' submit action is an invalid URL: '.$endpoint;
            $this->addError('global', $message);
            Craft::error($message, __METHOD__);

            return false;
        }

        $client = new Client();

        Craft::info($targetIntegrationFieldValues, __METHOD__);

        $response = $client->post($endpoint, [
            RequestOptions::JSON => $targetIntegrationFieldValues
        ]);

        $res = $response->getBody()->getContents();
        $resAsString = is_array($res) ? json_encode($res) : $res;
        $this->successMessage = $resAsString;
        Craft::info($res, __METHOD__);

        return true;
    }
}

