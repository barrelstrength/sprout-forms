<?php

namespace barrelstrength\sproutforms\controllers;

use Craft;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Checkboxes;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Dropdown;
use barrelstrength\sproutforms\integrations\sproutforms\fields\MultiSelect;
use barrelstrength\sproutforms\integrations\sproutforms\fields\Number;
use barrelstrength\sproutforms\integrations\sproutforms\fields\SingleLine;
use barrelstrength\sproutforms\integrations\sproutforms\fields\MultipleChoice;
use craft\base\Field;
use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;
use craft\web\Controller as BaseController;

class ExamplesController extends BaseController
{
    /**
     * Install examples
     *
     * @throws \Throwable
     */
    public function actionInstall()
    {
        $this->_installExampleTemplates();
        $this->_installExampleData();

        Craft::$app->session->setNotice(Craft::t('sprout-forms', 'Examples successfully installed.'));
        $this->redirect(UrlHelper::cpUrl('sprout-forms'));
    }

    /**
     * Install templates
     *
     * @return \yii\web\Response
     */
    private function _installExampleTemplates()
    {
        try {
            $sproutFormsFolder = Craft::$app->path->getSiteTemplatesPath().DIRECTORY_SEPARATOR.'sproutforms';

            /** @noinspection MkdirRaceConditionInspection */
            @mkdir($sproutFormsFolder);
            $sproutFormsPath = Craft::getAlias('@barrelstrength/sproutforms/templates/_special/examples/templates');

            FileHelper::copyDirectory($sproutFormsPath, $sproutFormsFolder);
        } catch (\Exception $e) {
            SproutForms::log($e->getMessage());

            Craft::$app->session->setError(Craft::t('sprout-forms', 'Unable to install the examples.'));

            return $this->redirect('sprout-forms/settings/examples');
        }
    }

    /**
     * Install example data
     *
     * @return \yii\web\Response
     * @throws \Throwable
     */
    private function _installExampleData()
    {
        try {
            // Create Example Forms
            // ------------------------------------------------------------

            $formSettings = [
                [
                    'name' => 'Contact Form',
                    'handle' => 'contact',
                    'titleFormat' => "{dateCreated|date('Y-m-d')} – {fullName} – {message|slice(0,22)}...",
                    'redirectUri' => 'sproutforms/examples/contact-form?message=thank-you',
                    'displaySectionTitles' => false
                ],
                [
                    'name' => 'Basic Fields Form',
                    'handle' => 'basic',
                    'titleFormat' => '{plainText} – {dropdown}{% if object.textarea %} – {{ object.textarea|slice(0,15) }}{% endif %}',
                    'redirectUri' => 'sproutforms/examples/basic-fields?message=thank-you',
                    'displaySectionTitles' => true
                ],
                // [
                // 	'name' => 'All Craft Fields',
                // 	'handle' => 'advanced',
                // 	'titleFormat' => "{dateCreated|date('Y-m-d')}"
                // )
            ];

            $fieldSettings = [
                'contact' => [
                    'Default' => [
                        [
                            'name' => 'Full Name',
                            'handle' => 'fullName',
                            'type' => SingleLine::class,
                            'required' => 1,
                            'settings' => [
                                'placeholder' => '',
                                'charLimit' => '',
                                'multiline' => '',
                                'initialRows' => 4,
                            ]
                        ],
                        [
                            'name' => 'Email',
                            'handle' => 'email',
                            'type' => SingleLine::class,
                            'required' => 1,
                            'settings' => [
                                'placeholder' => '',
                                'charLimit' => '',
                                'multiline' => '',
                                'initialRows' => 4,
                            ]
                        ],
                        [
                            'name' => 'Message',
                            'handle' => 'message',
                            'type' => SingleLine::class,
                            'required' => 1,
                            'settings' => [
                                'placeholder' => '',
                                'charLimit' => '',
                                'multiline' => 1,
                                'initialRows' => 4,
                            ]
                        ]
                    ]
                ],
                'basic' => [
                    'Section One' => [
                        [
                            'name' => 'Plain Text Field',
                            'handle' => 'plaintext',
                            'type' => SingleLine::class,
                            'required' => 1,
                            'settings' => [
                                'placeholder' => '',
                                'charLimit' => '',
                                'multiline' => 0,
                                'initialRows' => 4,
                            ]
                        ],
                        [
                            'name' => 'Dropdown Field',
                            'handle' => 'dropdown',
                            'type' => Dropdown::class,
                            'required' => 1,
                            'settings' => [
                                'options' => [
                                    [
                                        'label' => 'Option 1',
                                        'value' => 'option1',
                                        'default' => ''
                                    ],
                                    [
                                        'label' => 'Option 2',
                                        'value' => 'option2',
                                        'default' => ''
                                    ],
                                    [
                                        'label' => 'Option 3',
                                        'value' => 'option3',
                                        'default' => ''
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Number Field',
                            'handle' => 'number',
                            'type' => Number::class,
                            'required' => 0,
                            'settings' => [
                                'min' => 0,
                                'max' => null,
                                'decimals' => 0
                            ]
                        ]
                    ],
                    'Section Two' => [
                        [
                            'name' => 'Radio Buttons Field',
                            'handle' => 'radioButtons',
                            'type' => MultipleChoice::class,
                            'required' => 0,
                            'settings' => [
                                'options' => [
                                    [
                                        'label' => 'Option 1',
                                        'value' => 'option1',
                                        'default' => ''
                                    ],
                                    [
                                        'label' => 'Option 2',
                                        'value' => 'option2',
                                        'default' => ''
                                    ],
                                    [
                                        'label' => 'Option 3',
                                        'value' => 'option3',
                                        'default' => ''
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Checkboxes Field',
                            'handle' => 'checkboxes',
                            'type' => Checkboxes::class,
                            'required' => 0,
                            'settings' => [
                                'options' => [
                                    [
                                        'label' => 'Option 1',
                                        'value' => 'option1',
                                        'default' => ''
                                    ],
                                    [
                                        'label' => 'Option 2',
                                        'value' => 'option2',
                                        'default' => ''
                                    ],
                                    [
                                        'label' => 'Option 3',
                                        'value' => 'option3',
                                        'default' => ''
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Multi-select Field',
                            'handle' => 'multiSelect',
                            'type' => MultiSelect::class,
                            'required' => 0,
                            'settings' => [
                                'options' => [
                                    [
                                        'label' => 'Option 1',
                                        'value' => 'option1',
                                        'default' => ''
                                    ],
                                    [
                                        'label' => 'Option 2',
                                        'value' => 'option2',
                                        'default' => ''
                                    ],
                                    [
                                        'label' => 'Option 3',
                                        'value' => 'option3',
                                        'default' => ''
                                    ]
                                ]
                            ]
                        ],
                        [
                            'name' => 'Textarea Field',
                            'handle' => 'textarea',
                            'type' => SingleLine::class,
                            'required' => 0,
                            'settings' => [
                                'placeholder' => '',
                                'charLimit' => '',
                                'multiline' => 1,
                                'initialRows' => 4,
                            ]
                        ]
                    ]
                ],
            ];

            // Create Forms and their Content Tables
            foreach ($formSettings as $settings) {
                $form = new FormElement();

                // Assign our form settings
                $form->name = $settings['name'];
                $form->handle = $settings['handle'];
                $form->titleFormat = $settings['titleFormat'];
                $form->redirectUri = $settings['redirectUri'];
                $form->displaySectionTitles = $settings['displaySectionTitles'];

                // Create the Form
                SproutForms::$app->forms->saveForm($form);

                // Set our field context
                Craft::$app->content->fieldContext = $form->getFieldContext();
                Craft::$app->content->contentTable = $form->getContentTable();

                //------------------------------------------------------------

                // Do we have a new field that doesn't exist yet?
                // If so, save it and grab the id.

                $fieldLayout = [];
                $requiredFields = [];

                $tabs = $fieldSettings[$form->handle];

                foreach ($tabs as $tabName => $newFields) {
                    foreach ($newFields as $newField) {
                        $newField['settings'] = json_encode($newField['settings']);
                        $newField['translationMethod'] = Field::TRANSLATION_METHOD_NONE;

                        $field = Craft::$app->fields->createField($newField);

                        // Save our field
                        Craft::$app->fields->saveField($field);

                        $fieldLayout[$tabName][] = $field->id;

                        if ($field->required) {
                            $requiredFields[] = $field->id;
                        }
                    }
                }

                // Set the field layout
                $fieldLayout = Craft::$app->fields->assembleLayout($fieldLayout, $requiredFields);

                $fieldLayout->type = FormElement::class;
                $form->setFieldLayout($fieldLayout);

                // Save our form again with a layouts
                SproutForms::$app->forms->saveForm($form);
            }
        } catch (\Exception $e) {
            SproutForms::error($e->getMessage());

            Craft::$app->session->setError(Craft::t('sprout-forms', 'Unable to install the examples.'));

            return $this->redirect('sproutforms/settings/examples');
        }
    }
}