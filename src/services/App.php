<?php

namespace barrelstrength\sproutforms\services;

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
     * @var FrontEndFields
     */
    public $frontEndFields;

    public function init()
    {
        $this->groups = new Groups();
        $this->forms = new Forms();
        $this->fields = new Fields();
        $this->entries = new Entries();
        $this->frontEndFields = new FrontEndFields();
    }
}
