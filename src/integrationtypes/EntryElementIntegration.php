<?php

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ElementIntegration;
use Craft;
use craft\elements\Entry;
use craft\elements\User;
use craft\fields\Date;
use craft\fields\PlainText;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\web\IdentityInterface;

/**
 * Create a Craft Entry element
 *
 * @property string                            $userElementType
 * @property IdentityInterface|User|null|false $author
 * @property array                             $defaultAttributes
 * @property array                             $sectionsAsOptions
 */
class EntryElementIntegration extends ElementIntegration
{
    /**
     * The Entry Type ID where the Form Field values will be mapped to
     *
     * @var int
     */
    public $entryTypeId;

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return Craft::t('sprout-forms', 'Entry Element (Craft)');
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
        $sections = $this->getSectionsAsOptions();

        return Craft::$app->getView()->renderTemplate('sprout-forms/_components/integrationtypes/entryelement/settings',
            [
                'integration' => $this,
                'sectionsOptions' => $sections
            ]
        );
    }

    /**
     * @inheritDoc
     *
     * @throws \Throwable
     */
    public function submit(): bool
    {
        if (!$this->entryTypeId || Craft::$app->getRequest()->getIsCpRequest()) {
            return false;
        }

        return $this->createEntry();
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
                } else if (empty($fieldMap['integrationField'])) {
                    // Leave default handle is the integrationField is blank
                    $fields[$fieldMap['sproutFormField']] = $entry->{$fieldMap['sproutFormField']};
                }
            }
        }

        return $fields;
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getFieldMappingSettingsHtml()
    {
        $this->fieldMapping = [];

        $entryTypeId = $this->entryTypeId;

        if ($entryTypeId === null || empty($entryTypeId)) {
            $sections = $this->getSectionsAsOptions();
            $entryTypeId = $sections[1]['value'] ?? null;
        }

        if ($entryTypeId !== null) {
            foreach ($this->getElementCustomFieldsAsOptions($entryTypeId) as $elementFieldsAsOption) {
                $this->fieldMapping[] = [
                    'integrationField' => $elementFieldsAsOption['value'],
                    'sproutFormField' => ''
                ];
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
                        'integrationField' => [
                            'heading' => Craft::t('sprout-forms', 'Entry Field'),
                            'type' => 'select',
                            'class' => 'formField',
                            'options' => $this->getElementCustomFieldsAsOptions($entryTypeId)
                        ],
                        'sproutFormField' => [
                            'heading' => Craft::t('sprout-forms', 'Form Field'),
                            'type' => 'select',
                            'class' => 'formEntryFields',
                            'options' => []
                        ]
                    ],
                    'rows' => $this->fieldMapping
                ]
            ]);

        return $rendered;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultAttributes(): array
    {
        return [
            [
                'label' => Craft::t('sprout-forms', 'Title'),
                'value' => 'title',
                'class' => PlainText::class
            ],
            [
                'label' => Craft::t('sprout-forms', 'Slug'),
                'value' => 'slug',
                'class' => PlainText::class
            ],
            [
                'label' => Craft::t('sprout-forms', 'Post Date'),
                'value' => 'postDate',
                'class' => Date::class
            ]
        ];
    }

    /**
     * @param $elementGroupId
     *
     * @return array
     */
    public function getElementCustomFieldsAsOptions($elementGroupId): array
    {
        $entryType = Craft::$app->getSections()->getEntryTypeById($elementGroupId);
        $defaultEntryFields = $this->getDefaultElementFieldsAsOptions();
        $entryFields = $entryType->getFields();
        $options = $defaultEntryFields;

        foreach ($entryFields as $field) {
            $options[] = [
                'label' => $field->name,
                'value' => $field->handle,
                'class' => get_class($field)
            ];
        }

        return $options;
    }

    /**
     * @return bool
     * @throws \Throwable
     */
    private function createEntry(): bool
    {
        $fields = $this->resolveFieldMapping();
        $entryType = Craft::$app->getSections()->getEntryTypeById($this->entryTypeId);

        $entryElement = new Entry();
        $entryElement->typeId = $entryType->id;
        $entryElement->sectionId = $entryType->sectionId;

        $author = $this->getAuthor();

        if ($author) {
            $entryElement->authorId = $author->id;
        }

        $entryElement->setAttributes($fields, false);

        try {
            if ($entryElement->validate()) {
                $result = Craft::$app->getElements()->saveElement($entryElement);
                if ($result) {
                    $message = Craft::t('sprout-forms', 'Entry Element Integration created Entry ID: {id}.', [
                        'entryId' => $entryElement->id
                    ]);
                    $this->logResponse(true, $message);
                    Craft::info($message, __METHOD__);
                    return true;
                }

                $message = Craft::t('sprout-forms', 'Unable to create Entry via Entry Element Integration');
                $this->logResponse(false, $entryElement->getErrors());
                Craft::error($message, __METHOD__);
            } else {


                $errors = json_encode($entryElement->getErrors());
                $message = Craft::t('sprout-forms', 'Element Integration does not validate: '.$this->name.' - Errors: '.$errors);
                Craft::error($message, __METHOD__);
                $this->logResponse(false, $message);
            }
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
            $this->logResponse(false, $e->getTrace());
        }

        return false;
    }

    /**
     * @return array
     */
    private function getSectionsAsOptions(): array
    {
        $sections = Craft::$app->getSections()->getAllSections();
        $options = [];

        foreach ($sections as $section) {
            if ($section->type != 'single') {
                $entryTypes = $section->getEntryTypes();

                $options[] = ['optgroup' => $section->name];

                foreach ($entryTypes as $entryType) {
                    $options[] = [
                        'label' => $entryType->name,
                        'value' => $entryType->id
                    ];
                }
            }
        }

        return $options;
    }

    /**
     * @return string
     */
    public function getUserElementType(): string
    {
        return User::class;
    }

    /**
     * Returns the author who will be used when creating an Entry
     *
     * @return User|false|IdentityInterface|null
     */
    public function getAuthor()
    {
        $author = Craft::$app->getUser()->getIdentity();

        if ($this->setAuthorToLoggedInUser) {
            return $author;
        }

        if ($this->defaultAuthorId && is_array($this->defaultAuthorId)) {
            $user = Craft::$app->getUsers()->getUserById($this->defaultAuthorId[0]);
            if ($user) {
                $author = $user;
            }
        }

        return $author;
    }
}
