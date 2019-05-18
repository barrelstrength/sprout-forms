<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\Integration;
use Craft;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;

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
    public function getName(): string
    {
        return Craft::t('sprout-forms', 'Custom Endpoint');
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
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

        return $this->forwardEntry();
    }

    /**
     * @inheritDoc
     */
    public function resolveFieldMapping()
    {
        $fields = [];
        $entry = $this->entry;

        if ($this->fieldMapping) {
            foreach ($this->fieldMapping as $fieldMap) {
                if (isset($entry->{$fieldMap['sproutFormField']}) && $fieldMap['integrationField']) {
                    $fields[$fieldMap['integrationField']] = $entry->{$fieldMap['sproutFormField']};
                }
            }
        }

        return $fields;
    }

    /**
     * @return bool
     */
    private function forwardEntry(): bool
    {
        $entry = $this->entry;
        $fields = $this->resolveFieldMapping();
        $endpoint = $this->submitAction;

        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            $message = $entry->formName.' submit action is an invalid URL: '.$endpoint;
            $this->logResponse(false, $message);
            Craft::error($message, __METHOD__);

            return false;
        }

        $client = new Client();

        try {
            Craft::info($fields, __METHOD__);

            $response = $client->post($endpoint, [
                RequestOptions::JSON => $fields
            ]);

            $this->logResponse(true, $response->getBody()->getContents());
            Craft::info($response->getBody()->getContents(), __METHOD__);
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
            $this->logResponse(false, $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws InvalidConfigException
     */
    public function getFieldMappingSettingsHtml()
    {
        $currentFields = $this->getFormFieldsAsMappingOptions();

        if (empty($this->fieldMapping)) {
            // show all the form fields
            foreach ($currentFields as $formField) {
                $this->fieldMapping[] = [
                    'sproutFormField' => $formField['value'],
                    'integrationField' => ''
                ];
            }
        } else {
            $savedFieldMapping = $this->fieldMapping;
            $this->fieldMapping = [];
            foreach ($currentFields as $key => $formField) {

                $fieldMap = [
                    'sproutFormField' => $formField['value'],
                    'integrationField' => ''
                ];

                foreach ($savedFieldMapping as $savedFieldMap) {
                    if ($savedFieldMap['sproutFormField'] === $formField['value']) {
                        $fieldMap['integrationField'] = $savedFieldMap['integrationField'];
                    }
                }

                $this->fieldMapping[] = $fieldMap;
            }
        }

        $rendered = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'editableTableField',
            [
                [
                    'label' => Craft::t('sprout-forms', 'Field Mapping'),
                    'instructions' => Craft::t('sprout-forms', 'Define your field mapping.'),
                    'id' => 'fieldMapping',
                    'name' => 'fieldMapping',
                    'addRowLabel' => Craft::t('sprout-forms', 'Add a field mapping'),
                    'static' => true,
                    'cols' => [
                        'sproutFormField' => [
                            'heading' => Craft::t('sprout-forms', 'Form Field'),
                            'type' => 'select',
                            'class' => 'formField',
                            'options' => $currentFields
                        ],
                        'integrationField' => [
                            'heading' => Craft::t('sprout-forms', 'API Field'),
                            'type' => 'singleline',
                            'class' => 'code custom-endpoint',
                            'placeholder' => Craft::t('sprout-forms', 'Leave blank and no data will be mapped')
                        ]
                    ],
                    'rows' => $this->fieldMapping
                ]
            ]);

        return $rendered;
    }
}

