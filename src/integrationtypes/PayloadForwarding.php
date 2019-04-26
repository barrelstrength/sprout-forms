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
        return Craft::t('sprout-forms', 'Payload Forwarding');
    }

    /**
     * @inheritdoc
     */
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
            $message = $entry->formName.' submit action is an invalid URL: '.$endpoint;
            $this->logResponse($message);
            Craft::error($message, __METHOD__);

            return false;
        }

        $client = new Client();

        try {
            Craft::info($fields, __METHOD__);

            $response = $client->post($endpoint, [
                RequestOptions::JSON => $fields
            ]);
            $res = ['code' => $response->getCode()];
            $this->logResponse($res, $response->getBody()->getContents());
            Craft::info($response->getBody()->getContents(), __METHOD__);
        } catch (\Exception $e) {
            $this->addFormEntryError( $e->getMessage());
            $this->logResponse($e->getMessage(), $e->getTrace());
            return false;
        }

        return true;
    }

    /**
     * Returns a default field mapping html
     *
     * @return string
     */
    public function getFieldMappingSettingsHtml()
    {
        if (!$this->hasFieldMapping){
            return '';
        }

        $currentFields = $this->getFormFieldsAsOptions();

        if (empty($this->fieldsMapped)) {
            // show all the form fields
            foreach ($currentFields as $formField) {
                $this->fieldsMapped[] = [
                    'sproutFormField' => $formField['value'],
                    'integrationField' => ''
                ];
            }
        }else {
            $fieldsMappedSaved = $this->fieldsMapped;
            $this->fieldsMapped = [];
            foreach ($currentFields as $key => $formField) {

                $fieldMapped = [
                    'sproutFormField' => $formField['value'],
                    'integrationField' => ''
                ];

                foreach ($fieldsMappedSaved as $fieldMappedSaved) {
                    if ($fieldMappedSaved['sproutFormField'] == $formField['value']){
                        $fieldMapped['integrationField'] = $fieldMappedSaved['integrationField'];
                    }
                }

                $this->fieldsMapped[] = $fieldMapped;
            }
        }

        $rendered = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'editableTableField',
            [
                [
                    'label' => Craft::t('sprout-forms', 'Field Mapping'),
                    'instructions' => Craft::t('sprout-forms', 'Define your field mapping.'),
                    'id' => 'fieldsMapped',
                    'name' => 'fieldsMapped',
                    'addRowLabel' => Craft::t('sprout-forms', 'Add a field mapping'),
                    'static' => true,
                    'cols' => [
                        'sproutFormField' => [
                            'heading' => Craft::t('sprout-forms', 'Form Field'),
                            'type' => 'singleline',
                            'class' => 'code formField'
                        ],
                        'integrationField' => [
                            'heading' => Craft::t('sprout-forms', 'Api Field'),
                            'type' => 'singleline',
                            'class' => 'code payloadField',
                            'placeholder' => 'Leave blank to no mapping'
                        ]
                    ],
                    'rows' => $this->fieldsMapped
                ]
            ]);

        return $rendered;
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

