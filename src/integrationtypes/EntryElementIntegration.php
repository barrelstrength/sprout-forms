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

    /** returns action that runs to update the targetIntegrationFieldColumns
     * This action should return an array of input fields that can be used to update the target columns
     */
    public function getUpdateTargetFieldsAction() {
        return 'sprout-forms/integrations/get-element-entry-fields';
    }

    /**
     * @inheritDoc
     */
    public static function displayName(): string
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
        $this->prepareFieldMapping();

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
    public function resolveFieldMapping(): array
    {
        $fields = [];
        $entry = $this->entry;

        if ($this->fieldMapping) {
            foreach ($this->fieldMapping as $fieldMap) {
                if (isset($entry->{$fieldMap['sourceFormField']}) && $fieldMap['targetIntegrationField']) {
                    $fields[$fieldMap['targetIntegrationField']] = $entry->{$fieldMap['sourceFormField']};
                } else if (empty($fieldMap['targetIntegrationField'])) {
                    // Leave default handle if the targetIntegrationField is blank
                    $fields[$fieldMap['sourceFormField']] = $entry->{$fieldMap['sourceFormField']};
                }
            }
        }

        unset($fields['id']);

        return $fields;
    }


    public function getElementIntegrationFieldOptions()
    {
        $sourceFormFields = $this->getSourceFormFields();

        $entryTypeId = $this->entryTypeId;

        // If no Entry ID has been selected, select the first one in the list.
        if ($entryTypeId === null || empty($entryTypeId)) {
            $sections = $this->getSectionsAsOptions();
            $entryTypeId = $sections[1]['value'] ?? null;
        }

        $targetElementFields = $this->getElementCustomFieldsAsOptions($entryTypeId);

        return $targetElementFields;

        \Craft::dd($sourceFormFields);
    }
    
    /**
     * @inheritDoc
     */
    public function getDefaultAttributes(): array
    {
        $targetElementFieldsData = [
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

        $defaultFields = [];
        foreach ($targetElementFieldsData as $targetElementFieldData) {
            $fieldInstance = new $targetElementFieldData['class']();
            $fieldInstance->name = $targetElementFieldData['label'];
            $fieldInstance->handle = $targetElementFieldData['value'];
            $defaultFields[] = $fieldInstance;
        }

        return $defaultFields;
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
            $options[] = $field;
//            $options[] = [
//                'label' => $field->name,
//                'value' => $field->handle,
//                'class' => get_class($field)
//            ];
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
                    $message = Craft::t('sprout-forms', 'Entry Element Integration created.');
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
            $this->logResponse(false, $e->getMessage());
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

