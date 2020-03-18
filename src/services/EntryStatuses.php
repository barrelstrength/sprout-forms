<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\models\EntryStatus;
use barrelstrength\sproutforms\records\EntryStatus as EntryStatusRecord;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use Throwable;
use yii\base\Component;
use yii\base\Exception;
use yii\db\StaleObjectException;

/**
 *
 * @property null|int         $spamStatusId
 * @property null|EntryStatus $defaultEntryStatus
 * @property array            $allEntryStatuses
 */
class EntryStatuses extends Component
{
    /**
     * @return EntryStatus[]
     */
    public function getAllEntryStatuses(): array
    {
        $results = EntryStatusRecord::find()
            ->orderBy(['sortOrder' => 'asc'])
            ->all();

        $entryStatuses = [];
        foreach ($results as $result) {
            $entryStatuses[] = new EntryStatus($result);
        }

        return $entryStatuses;
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
        $existsStatusOnEntries = EntryElement::find()->where(['statusId' => $id])->exists();

        if ($existsStatusOnEntries) {
            return false;
        }

        $entryStatus = EntryStatusRecord::findOne($id);

        if (!$entryStatus || $entryStatus->isDefault || $entryStatus->handle === 'spam') {
            return false;
        }

        $entryStatus->delete();

        return true;
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
     * @return int|null
     */
    public function getSpamStatusId()
    {
        $spam = SproutForms::$app->entryStatuses->getEntryStatusByHandle(EntryStatus::SPAM_STATUS_HANDLE);

        if (!$spam->id) {
            return null;
        }

        return $spam->id;
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
        $spam = SproutForms::$app->entryStatuses->getEntryStatusByHandle(EntryStatus::SPAM_STATUS_HANDLE);

        if (!$spam->id) {
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
}
