<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\services;

use barrelstrength\sproutforms\models\Settings;
use barrelstrength\sproutforms\SproutForms;
use craft\base\Component;

class App extends Component
{
    /**
     * @var Groups
     */
    public $groups;

    /**
     * @var Forms
     */
    public $forms;

    /**
     * @var Fields
     */
    public $fields;

    /**
     * @var Entries
     */
    public $entries;

    /**
     * @var EntryStatuses
     */
    public $entryStatuses;

    /**
     * @var FrontEndFields
     */
    public $frontEndFields;

    /**
     * @var Integrations
     */
    public $integrations;

    /**
     * @var Rules
     */
    public $rules;

    /**
     * @var Settings
     */
    protected $settings;

    public function init()
    {
        $this->groups = new Groups();
        $this->forms = new Forms();
        $this->fields = new Fields();
        $this->entries = new Entries();
        $this->entryStatuses = new EntryStatuses();
        $this->frontEndFields = new FrontEndFields();
        $this->integrations = new Integrations();
        $this->rules = new Rules();
    }

    /**
     * Returns plugin settings model.
     *
     * This method helps explicitly define what we're getting back so we can
     * avoid NullReferenceException warnings
     *
     * @return Settings
     */
    public function getSettings(): Settings
    {
        /** @var SproutForms $plugin */
        $plugin = SproutForms::getInstance();

        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        return $settings;
    }
}
