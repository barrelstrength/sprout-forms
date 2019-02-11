<?php

namespace barrelstrength\sproutforms\models;

use craft\base\Model;
use Craft;

class FormGroup extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var string|null Name
     */
    public $name;

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
    public function __toString()
    {
        return Craft::t('sprout-forms', $this->name);
    }
}