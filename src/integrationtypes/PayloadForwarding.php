<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ApiIntegration;
use Craft;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

/**
 * Route our request to Craft or a third-party endpoint
 *
 * Payload forwarding is only available on front-end requests. Any
 * data saved to the database after a forwarded request is editable
 * in Craft as normal, but will not trigger any further calls to
 * the third-party endpoint.
 */
class PayloadForwarding extends ApiIntegration
{
    public $submitAction;

    /**
     * @var boolean
     */
    public $hasFieldMapping = true;

    public function getName() {
        return Craft::t('sprout-forms', 'Custom (Payload Forwarding)');
    }
    // Any general customizations we need specifically for Element Integrations

    // We may want to consider extending the Form Field API and adding support for Form Fields to identify what Field Class/Classes they can be mapped to. In the Element Integration case, this method resolves the field mapping by matching Sprout Form fields classes to Craft Field classes.
    // public function resolveFieldMapping() {}

    public function getSettingsHtml() {
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/integrationtypes/payloadforwarding/settings',
            [
                'integration' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function submit() {
        if ($this->submitAction && !Craft::$app->getRequest()->getIsCpRequest()) {
            if (!$this->forwardEntry()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function resolveFieldMapping() {
        $fields = [];
        $entry = $this->entry;
        
        if ($this->fieldsMapped){
            foreach ($this->fieldsMapped as $fieldMapped) {
                if (isset($entry->{$fieldMapped['sproutFormField']}) && $fieldMapped['integrationField']){
                    $fields[$fieldMapped['integrationField']] = $entry->{$fieldMapped['sproutFormField']};
                }
            }
        }
        
        return $fields;
    }

    /**
     * @return bool
     */
    private function forwardEntry()
    {
        $entry = $this->entry;
        $fields = $this->resolveFieldMapping();
        $endpoint = $this->submitAction;

        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {

            Craft::error($entry->formName.' submit action is an invalid URL: '.$endpoint, __METHOD__);

            return false;
        }

        $client = new Client();

        try {
            Craft::info($fields, __METHOD__);

            $response = $client->post($endpoint, [
                RequestOptions::JSON => $fields
            ]);

            Craft::info($response->getBody()->getContents(), __METHOD__);
        } catch (\Exception $e) {
            $entry->addError('general', $e->getMessage());
            Craft::error('Error on submit payload: '.$e->getMessage(), __METHOD__);

            return false;
        }

        return true;
    }

    /**
     * Return Class name as Type
     *
     * @return string
     */
    public function getType()
    {
        return self::class;
    }
}

