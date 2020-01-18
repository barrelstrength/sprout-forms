<?php

namespace barrelstrength\sproutforms\models;

use craft\base\Model;
use Craft;
use craft\helpers\UrlHelper;

/**
 * @property string $cpEditUrl
 */
class EntryStatus extends Model
{
    const SPAM_STATUS_HANDLE = 'spam';

    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $handle;

    /**
     * @var string
     */
    public $color = 'blue';

    /**
     * @var int
     */
    public $sortOrder;

    /**
     * @var int
     */
    public $isDefault;

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
        return Craft::t('sprout-forms', $this->name);
    }

    /**
     * @return string
     */
    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('sprout-forms/settings/orders-statuses/'.$this->id);
    }

    /**
     * @return string
     */
    public function htmlLabel(): string
    {
        return '<span class="sproutFormsStatusLabel"><span class="status '.$this->color.'"></span> '.$this->name.'</span>';
    }

    /**
     * @inheritdoc
     */
    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['name', 'handle'], 'required'];
        $rules[] = [['name', 'handle'], 'string', 'max' => 255];

        return $rules;
    }
}