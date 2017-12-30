<?php

namespace barrelstrength\sproutforms\models;

use craft\base\Model;
use craft\helpers\UrlHelper;

use barrelstrength\sproutforms\SproutForms;

class EntryStatus extends Model
{
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
     * @return string
     */
    function __toString()
    {
        return SproutForms::t($this->name);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 255]
        ];
    }
}