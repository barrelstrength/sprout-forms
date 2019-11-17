<?php

namespace barrelstrength\sproutforms\models;

use barrelstrength\sproutforms\base\Integration;
use barrelstrength\sproutforms\elements\Entry;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Model;
use Craft;
use craft\errors\MissingComponentException;
use yii\base\InvalidConfigException;

/**
 * @property null|Entry $entry
 */
class EntriesSpamLog extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int|null
     */
    public $entryId;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $errors;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;

    /**
     * Use the translated section name as the string representation.
     *
     * @inheritdoc
     */
    public function __toString()
    {
        return Craft::t('sprout-forms', $this->id);
    }

    /**
     * @return Entry|null
     */
    public function getEntry()
    {
        return SproutForms::$app->entries->getEntryById($this->entryId);
    }
}