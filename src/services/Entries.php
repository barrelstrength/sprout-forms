<?php

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutbase\jobs\PurgeElements;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\models\Settings;
use Craft;
use barrelstrength\sproutforms\SproutForms;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\events\OnSaveEntryEvent;
use barrelstrength\sproutforms\fields\formfields\BaseRelationFormField;
use barrelstrength\sproutforms\models\EntryStatus;
use barrelstrength\sproutforms\records\Entry as EntryRecord;
use barrelstrength\sproutforms\records\EntryStatus as EntryStatusRecord;
use craft\base\Element;
use Throwable;
use yii\base\Component;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;

/**
 *
 * @property null             $defaultEntryStatusId
 * @property FormElement      $entry
 * @property null|EntryStatus $defaultEntryStatus
 * @property array            $allEntryStatuses
 */
class Entries extends Component
{
    const SPAM_DEFAULT_LIMIT = 500;

    /**
     * @var EntryRecord
     */
    protected $entryRecord;

    /**
     * @param EntryRecord $entryRecord
     */
    public function __construct($entryRecord = null)
    {
        $this->entryRecord = $entryRecord;

        if ($this->entryRecord === null) {
            $this->entryRecord = EntryRecord::find();
        }

        parent::__construct($entryRecord);
    }

    /**
     * Returns an active or new entry element
     *
     * @param FormElement $form
     *
     * @return EntryElement
     */
    public function getEntry(FormElement $form): EntryElement
    {
        if (isset(SproutForms::$app->forms->activeEntries[$form->handle])) {
            return SproutForms::$app->forms->activeEntries[$form->handle];
        }

        $entry = new EntryElement();
        $entry->formId = $form->id;

        SproutForms::$app->forms->activeEntries[$form->handle] = $entry;

        return $entry;
    }

    /**
     * Set an activeEntry on the Forms Service
     *
     * One scenario this can be used is if you are allowing users
     * to edit Form Entries on the front-end and need to make the
     * displayTab or displayField tags aware of the active entry
     * without calling the displayForm tag.
     *
     * @param FormElement  $form
     * @param EntryElement $entry
     */
    public function setEntry(FormElement $form, EntryElement $entry)
    {
        SproutForms::$app->forms->activeEntries[$form->handle] = $entry;
    }

    /**
     * @param $entryStatusId
     *
     * @return EntryStatus
     */
    public function getEntryStatusById($entryStatusId): EntryStatus
    {
        $entryStatus = EntryStatusRecord::find()
            ->where(['id' => $entryStatusId])
            ->one();

        $entryStatusesModel = new EntryStatus();

        if ($entryStatus) {
            $entryStatusesModel->setAttributes($entryStatus->getAttributes(), false);
        }

        return $entryStatusesModel;
    }

    /**
     * @param $entryStatusHandle
     *
     * @return EntryStatus
     */
    public function getEntryStatusByHandle($entryStatusHandle): EntryStatus
    {
        $entryStatus = EntryStatusRecord::find()
            ->where(['handle' => $entryStatusHandle])
            ->one();

        $entryStatusesModel = new EntryStatus();

        if ($entryStatus) {
            $entryStatusesModel->setAttributes($entryStatus->getAttributes(), false);
        }

        return $entryStatusesModel;
    }

    /**
     * @param EntryStatus $entryStatus
     *
     * @return bool
     * @throws Exception
     */
    public function saveEntryStatus(EntryStatus $entryStatus): bool
    {
        $isNew = !$entryStatus->id;

        $record = new EntryStatusRecord();

        if ($entryStatus->id) {
            $record = EntryStatusRecord::findOne($entryStatus->id);

            if (!$record) {
                throw new Exception('No Entry Status exists with the ID: '.$entryStatus->id);
            }
        }

        $attributes = $entryStatus->getAttributes();

        if ($isNew) {
            unset($attributes['id']);
        }

        $record->setAttributes($attributes, false);

        $record->sortOrder = $entryStatus->sortOrder ?: 999;

        $entryStatus->validate();

        if (!$entryStatus->hasErrors()) {
            $transaction = Craft::$app->db->beginTransaction();

            try {
                if ($record->isDefault) {
                    EntryStatusRecord::updateAll(['isDefault' => false]);
                }

                $record->save(false);

                if (!$entryStatus->id) {
                    $entryStatus->id = $record->id;
                }

                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();

                throw $e;
            }

            return true;
        }

        return false;
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     * @throws StaleObjectException
     */
    public function deleteEntryStatusById($id): bool
    {
        $statuses = $this->getAllEntryStatuses();

        $entry = EntryElement::find()->where(['statusId' => $id])->one();

        if ($entry) {
            return false;
        }

        if (count($statuses) >= 2) {
            $entryStatus = EntryStatusRecord::findOne($id);

            if ($entryStatus) {
                $entryStatus->delete();
                return true;
            }
        }

        return false;
    }

    /**
     * Reorders Entry Statuses
     *
     * @param $entryStatusIds
     *
     * @return bool
     * @throws Exception
     */
    public function reorderEntryStatuses($entryStatusIds): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            foreach ($entryStatusIds as $entryStatus => $entryStatusId) {
                $entryStatusRecord = $this->getEntryStatusRecordById($entryStatusId);

                if ($entryStatusRecord) {
                    $entryStatusRecord->sortOrder = $entryStatus + 1;
                    $entryStatusRecord->save();
                }
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getAllEntryStatuses(): array
    {
        $entryStatuses = EntryStatusRecord::find()
            ->orderBy(['sortOrder' => 'asc'])
            ->all();

        return $entryStatuses;
    }

    /**
     * Returns a form entry model if one is found in the database by id
     *
     * @param          $entryId
     * @param int|null $siteId
     *
     * @return EntryElement|null
     */
    public function getEntryById($entryId, int $siteId = null)
    {
        $query = EntryElement::find();
        $query->id($entryId);
        $query->siteId($siteId);

        // We are using custom statuses, so all are welcome
        $query->status(null);

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $query->one();
    }

    /**
     * @param EntryElement $entry
     *
     * @return bool
     * @throws Exception
     * @throws Throwable
     */
    public function saveEntry(EntryElement $entry): bool
    {
        $isNewEntry = !$entry->id;

        if ($entry->id) {
            $entryRecord = EntryRecord::findOne($entry->id);

            if (!$entryRecord) {
                throw new Exception('No entry exists with id '.$entry->id);
            }
        }

        $entry->validate();

        if ($entry->hasErrors()) {
            Craft::error($entry->getErrors(), __METHOD__);
            return false;
        }

        $event = new OnBeforeSaveEntryEvent([
            'entry' => $entry
        ]);

        $this->trigger(EntryElement::EVENT_BEFORE_SAVE, $event);

        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            if (!$event->isValid || !empty($event->errors)) {
                foreach ($event->errors as $key => $error) {
                    $entry->addError($key, $error);
                }

                Craft::error('OnBeforeSaveEntryEvent is not valid', __METHOD__);

                return false;
            }

            $success = Craft::$app->getElements()->saveElement($entry);

            if (!$success) {
                Craft::error('Couldn’t save Element on saveEntry service.', __METHOD__);
                $transaction->rollBack();
                return false;
            }

            Craft::info('Form Entry Element Saved.', __METHOD__);

            $transaction->commit();

            $this->callOnSaveEntryEvent($entry, $isNewEntry);
        } catch (\Exception $e) {
            Craft::error('Failed to save element: '.$e->getMessage(), __METHOD__);
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    /**
     * @return EntryStatus|null
     */
    public function getDefaultEntryStatus()
    {
        /** @var EntryStatusRecord $entryStatus */
        $entryStatus = EntryStatusRecord::find()
            ->orderBy(['isDefault' => SORT_DESC])
            ->one();

        return new EntryStatus($entryStatus) ?? null;
    }

    /**
     * Saves some relations for a field.
     *
     * @param BaseRelationFormField $field
     * @param Element               $source
     * @param array                 $targetIds
     *
     * @return void
     * @throws Throwable
     */
    public function saveRelations(BaseRelationFormField $field, Element $source, array $targetIds)
    {
        if (!is_array($targetIds)) {
            $targetIds = [];
        }

        // Prevent duplicate target IDs.
        $targetIds = array_unique($targetIds);

        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            // Delete the existing relations
            $oldRelationConditions = [
                'and',
                [
                    'fieldId' => $field->id,
                    'sourceId' => $source->id,
                ]
            ];

            if ($field->localizeRelations) {
                $oldRelationConditions[] = [
                    'or',
                    ['sourceSiteId' => null],
                    ['sourceSiteId' => $source->siteId]
                ];
            }

            Craft::$app->getDb()->createCommand()
                ->delete('{{%relations}}', $oldRelationConditions)
                ->execute();

            // Add the new ones
            if (!empty($targetIds)) {
                $values = [];

                if ($field->localizeRelations) {
                    $sourceSiteId = $source->siteId;
                } else {
                    $sourceSiteId = null;
                }

                foreach ($targetIds as $sortOrder => $targetId) {
                    $values[] = [
                        $field->id,
                        $source->id,
                        $sourceSiteId,
                        $targetId,
                        $sortOrder + 1
                    ];
                }

                $columns = [
                    'fieldId',
                    'sourceId',
                    'sourceSiteId',
                    'targetId',
                    'sortOrder'
                ];
                Craft::$app->getDb()->createCommand()
                    ->batchInsert('{{%relations}}', $columns, $values)
                    ->execute();
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    /**
     * @return int|null
     */
    public function getSpamStatusId()
    {
        $spam = SproutForms::$app->entries->getEntryStatusByHandle(EntryStatus::SPAM_STATUS_HANDLE);

        if (!$spam->id) {
            return null;
        }

        return $spam->id;
    }

    /**
     * Gets an Entry Status's record.
     *
     * @param null $entryStatusId
     *
     * @return EntryStatusRecord|null|static
     * @throws Exception
     */
    private function getEntryStatusRecordById($entryStatusId = null)
    {
        if ($entryStatusId) {
            $entryStatusRecord = EntryStatusRecord::findOne($entryStatusId);

            if (!$entryStatusRecord) {
                throw new Exception('No Entry Status exists with the ID: '.$entryStatusId);
            }
        } else {
            $entryStatusRecord = new EntryStatusRecord();
        }

        return $entryStatusRecord;
    }

    /**
     * @param                   $form
     * @param EntryElement|null $entry
     *
     * @return mixed
     */
    public function isSaveDataEnabled($form, $entry = null): bool
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        // Get the global saveData setting
        $saveData = $settings->enableSaveData;

        if ($saveData) {
            // Allow Form to override global saveData setting
            $saveData = $form->saveData;
        }

        if ($entry !== null && Craft::$app->getRequest()->getIsSiteRequest() && $entry->getIsSpam()) {
            // If we have a spam entry, use the spam saveData setting
            $saveData = $settings->saveSpamToDatabase;
        }

        return $saveData;
    }

    /**
     * @param bool $force
     *
     * @return void
     */
    public function runPurgeSpamElements($force = false)
    {
        /** @var Settings $settings */
        $settings = SproutForms::getInstance()->getSettings();

        $probability = (int)$settings->cleanupProbability;

        // See Craft Garbage collection treatment of probability
        // https://docs.craftcms.com/v3/gc.html
        /** @noinspection RandomApiMigrationInspection */
        if (!$force && mt_rand(0, 1000000) >= $probability) {
            return;
        }

        // Default to 5000 if no integer is found in settings
        $spamLimit = is_int((int)$settings->spamLimit)
            ? (int)$settings->spamLimit
            : static::SPAM_DEFAULT_LIMIT;

        if ($spamLimit <= 0) {
            return;
        }

        $ids = EntryElement::find()
            ->limit(null)
            ->offset($spamLimit)
            ->status(EntryStatus::SPAM_STATUS_HANDLE)
            ->orderBy(['sproutforms_entries.dateCreated' => SORT_DESC])
            ->ids();

        $purgeElements = new PurgeElements();
        $purgeElements->elementType = EntryElement::class;
        $purgeElements->idsToDelete = $ids;

        SproutBase::$app->utilities->purgeElements($purgeElements);
    }

    /**
     * @param $entry
     * @param $isNewEntry
     */
    public function callOnSaveEntryEvent($entry, $isNewEntry)
    {
        $event = new OnSaveEntryEvent([
            'entry' => $entry,
            'isNewEntry' => $isNewEntry,
        ]);

        $this->trigger(EntryElement::EVENT_AFTER_SAVE, $event);
    }

    /**
     * Mark entries as Spam
     *
     * @param $formEntryElements
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function markAsSpam($formEntryElements): bool
    {
        $spam = SproutForms::$app->entries->getEntryStatusByHandle(EntryStatus::SPAM_STATUS_HANDLE);

        if (!$spam->id){
            return false;
        }

        foreach ($formEntryElements as $key => $formEntryElement) {

            $success = Craft::$app->db->createCommand()->update(
                '{{%sproutforms_entries}}',
                ['statusId' => $spam->id],
                ['id' => $formEntryElement->id]
            )->execute();

            if (!$success) {
                Craft::error("Unable to mark entry as spam. Form Entry ID: {$formEntryElement->id}", __METHOD__);
            }
        }

        return true;
    }

    /**
     * Mark entries as Not Spam
     *
     * @param $formEntryElements
     *
     * @return bool
     * @throws \Exception
     * @throws Throwable
     */
    public function markAsDefaultStatus($formEntryElements): bool
    {
        /** @var EntryStatus $defaultStatus */
        $defaultStatus = $this->getDefaultEntryStatus();

        foreach ($formEntryElements as $key => $formEntryElement) {
            $success = Craft::$app->db->createCommand()->update(
                '{{%sproutforms_entries}}',
                ['statusId' => $defaultStatus->id],
                ['id' => $formEntryElement->id]
            )->execute();

            if (!$success) {
                Craft::error("Unable to mark entry as not spam. Form Entry ID: {$formEntryElement->id}", __METHOD__);
            }
        }

        return true;
    }
}
