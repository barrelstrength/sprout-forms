<?php

namespace barrelstrength\sproutforms\services;

use craft\base\Component;
use Craft;

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
     * @var FrontEndFields
     */
    public $frontEndFields;

    /**
     * @var Integrations
     */
    public $integrations;

    public function init()
    {
        $this->groups = new Groups();
        $this->forms = new Forms();
        $this->fields = new Fields();
        $this->entries = new Entries();
        $this->frontEndFields = new FrontEndFields();
        $this->integrations= new Integrations();
    }
}
