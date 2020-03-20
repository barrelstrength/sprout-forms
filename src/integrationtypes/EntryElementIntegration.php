<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\integrationtypes;

use barrelstrength\sproutforms\base\ElementIntegration;
use Craft;
use craft\elements\Entry;
use craft\elements\User;
use craft\fields\Date;
use craft\fields\PlainText;
use craft\models\EntryType;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\web\IdentityInterface;

/**
 * Create a Craft Entry element
 *
 * @property string                            $userElementType
 * @property IdentityInterface|User|null|false $author
 * @property array                             $defaultAttributes
 * @property array                             $elementIntegrationFieldOptions
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
     * @throws Exception
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
     * @throws Throwable
     */
    public function submit(): bool
    {
        if (!$this->entryTypeId || Craft::$app->getRequest()->getIsCpRequest()) {
            return false;
        }

        $targetIntegrationFieldValues = $this->getTargetIntegrationFieldValues();

        /** @var EntryType $entryType */
        $entryType = Craft::$app->getSections()->getEntryTypeById($this->entryTypeId);

        $entryElement = new Entry();
        $entryElement->typeId = $entryType->id;
        $entryElement->sectionId = $entryType->sectionId;

        $author = $this->getAuthor();

        if ($author) {
            $entryElement->authorId = $author->id;
        }

        // @todo - why do we need to unset the id from the field mapping for this
        // Element Integration and not others? Consider refactoring the underlying
        // reason that causes the need to do this
        unset($targetIntegrationFieldValues['id']);

        $defaultAttributes = $this->getDefaultSourceMappingAttributes();

        $defaultAttributesHandles = [];
        foreach ($defaultAttributes as $defaultAttribute) {
            $defaultAttributesHandles[] = $defaultAttribute['handle'];
        }

        foreach ($targetIntegrationFieldValues as $fieldHandle => $fieldValue) {
            if (in_array($fieldHandle, $defaultAttributesHandles, true)) {
                $entryElement->{$fieldHandle} = $fieldValue;
            } else {
                $entryElement->setFieldValue($fieldHandle, $fieldValue);
            }
        }

        if ($entryElement->validate()) {
            $result = Craft::$app->getElements()->saveElement($entryElement);
            if ($result) {
                $this->successMessage = Craft::t('sprout-forms', 'Entry Element ID {id} created in {sectionName} Section', [
                    'id' => $entryElement->id,
                    'sectionName' => $entryElement->getSection()->name
                ]);

                return true;
            }

            $message = Craft::t('sprout-forms', 'Unable to create Entry via Entry Element Integration');
            $this->addError('global', $message);
            Craft::error($message, __METHOD__);
        } else {
            $errors = json_encode($entryElement->getErrors());
            $message = Craft::t('sprout-forms', 'Element Integration does not validate: '.$this->name.' - Errors: '.$errors);
            Craft::error($message, __METHOD__);
            $this->addError('global', $entryElement->getErrors());
        }


        return false;
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigException
     */
    public function getTargetIntegrationFieldsAsMappingOptions(): array
    {
        $entryFields = $this->getElementCustomFieldsAsOptions($this->entryTypeId);

        return $this->getFieldsAsOptionsByRow($entryFields);
    }

    /**
     * @param $entryFields
     *
     * @return array
     * @throws InvalidConfigException
     */
    public function getFieldsAsOptionsByRow($entryFields): array
    {
        $fieldMapping = $this->fieldMapping;
        $integrationSectionId = $this->entryTypeId ?? null;

        $formFields = $this->getSourceFormFieldsAsMappingOptions();
        $rowPosition = 0;
        $finalOptions = [];

        foreach ($formFields as $formField) {
            $optionsByRow = $this->getCompatibleFields($entryFields, $formField);
            // We have rows stored and are for the same sectionType
            if ($fieldMapping && ($integrationSectionId == $this->entryTypeId) &&
                isset($fieldMapping[$rowPosition])) {
                foreach ($optionsByRow as $key => $option) {
                    if ($option['value'] == $fieldMapping[$rowPosition]['targetIntegrationField'] &&
                        $fieldMapping[$rowPosition]['sourceFormField'] == $formField['value']) {
                        $optionsByRow[$key]['selected'] = true;
                    }
                }
            }

            $finalOptions[$rowPosition] = $optionsByRow;

            $rowPosition++;
        }

        return $finalOptions;
    }

    /**
     * @param array $entryFields
     * @param array $formField
     *
     * @return array
     */
    public function getCompatibleFields(array $entryFields, array $formField): array
    {
        $compatibleFields = $formField['compatibleCraftFields'] ?? '*';

        if (!is_array($compatibleFields)) {
            return [];
        }

        $finalOptions = [];

        foreach ($entryFields as $field) {
            if (!in_array(get_class($field), $compatibleFields, true)) {
                continue;
            }

            $finalOptions[] = [
                'label' => $field->name.' ('.$field->handle.')',
                'value' => $field->handle
            ];
        }

        return $finalOptions;
    }

    /**
     * @return array
     */
    public function getElementIntegrationFieldOptions(): array
    {
        $entryTypeId = $this->entryTypeId;

        // If no Entry ID has been selected, select the first one in the list.
        if ($entryTypeId === null || empty($entryTypeId)) {
            $sections = $this->getSectionsAsOptions();
            $entryTypeId = $sections[1]['value'] ?? null;
        }

        return $this->getElementCustomFieldsAsOptions($entryTypeId);
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

    /**
     * @return array
     */
    private function getSectionsAsOptions(): array
    {
        $sections = Craft::$app->getSections()->getAllSections();
        $options = [];

        foreach ($sections as $section) {
            // Don't show Singles
            if ($section->type === 'single') {
                continue;
            }

            $entryTypes = $section->getEntryTypes();

            $options[] = ['optgroup' => $section->name];

            foreach ($entryTypes as $entryType) {
                $options[] = [
                    'label' => $entryType->name,
                    'value' => $entryType->id
                ];
            }
        }

        return $options;
    }
}
