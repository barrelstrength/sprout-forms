<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutbase\jobs\PurgeElements;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutforms\base\Captcha;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\elements\Entry as EntryElement;
use barrelstrength\sproutforms\elements\Form as FormElement;
use barrelstrength\sproutforms\events\OnBeforeSaveEntryEvent;
use barrelstrength\sproutforms\events\OnSaveEntryEvent;
use barrelstrength\sproutforms\jobs\ResaveEntries;
use barrelstrength\sproutforms\models\EntryStatus;
use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\records\EntriesSpamLog as EntriesSpamLogRecord;
use barrelstrength\sproutforms\records\Entry as EntryRecord;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\helpers\Json;
use Throwable;
use yii\base\Component;
use yii\base\Exception;

/**
 * Class Entries
 *
 * @package barrelstrength\sproutforms\services
 *
 * @property FormElement $entry
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
     * @param FormElement $form
     * @param bool        $isSpam
     *
     * @return mixed
     */
    public function isSaveDataEnabled(FormElement $form, $isSpam = false): bool
    {
        /** @var SproutForms $plugin */
        $plugin = Craft::$app->getPlugins()->getPlugin('sprout-forms');
        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        // Get the global saveData setting
        $saveData = $settings->enableSaveData;

        if ($saveData) {
            // Allow Form to override global saveData setting
            $saveData = $form->saveData ?: $settings->enableSaveDataDefaultValue;
        }

        // Let the SPAM setting determine if we save data if we are:
        // 1. Saving data globally and/or at the form level
        // 2. Processing a site request (if it's a CP request Entries with spam status can always be updated)
        // 3. The entry being saved has been identified as spam
        if ($saveData &&
            Craft::$app->getRequest()->getIsSiteRequest() &&
            $isSpam === true
        ) {

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
     * @param EntryElement $entry
     *
     * @return bool
     */
    public function logEntriesSpam(Entry $entry): bool
    {
        foreach ($entry->getCaptchas() as $captcha) {
            if ($captcha->hasErrors()) {
                $entriesSpamLogRecord = new EntriesSpamLogRecord();
                $entriesSpamLogRecord->entryId = $entry->id;
                $entriesSpamLogRecord->type = get_class($captcha);
                $entriesSpamLogRecord->errors = Json::encode($captcha->getErrors(Captcha::CAPTCHA_ERRORS_KEY));
                $entriesSpamLogRecord->save();
            }
        }

        return true;
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
     * @param $formId
     */
    public function resaveElements($formId)
    {
        Craft::$app->getQueue()->push(new ResaveEntries([
            'formId' => $formId
        ]));
    }
}
