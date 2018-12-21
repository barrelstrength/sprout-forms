<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ApiIntegration;
use GuzzleHttp\Exception\RequestException;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use GuzzleHttp\Client;

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
        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/integrationtypes/custom/settings',
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
                if (isset($entry->{$fieldMapped['label']}) && $fieldMapped['value']){
                    $fields[$fieldMapped['value']] = $entry->{$fieldMapped['label']};
                }else{
                    // Leave default handle is the value is blank
                    if (empty($fieldMapped['value'])){
                        $fields[$fieldMapped['label']] = $entry->{$fieldMapped['label']};
                    }
                }
            }
        }
        
        return $fields;
    }

    /**
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function forwardEntry()
    {
        $entry = $this->entry;
        #$fields = $entry->getPayloadFields();
        $fields = $this->resolveFieldMapping();
        $endpoint = $this->submitAction;

        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {

            SproutForms::error($entry->formName.' submit action is an invalid URL: '.$endpoint);

            return false;
        }

        $client = new Client();

        try {
            SproutForms::info($fields);

            $response = $client->request('POST', $endpoint, [
                'form_params' => $fields
            ]);

            SproutForms::info($response->getBody()->getContents());
        } catch (RequestException $e) {
            $entry->addError('general', $e->getMessage());

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

