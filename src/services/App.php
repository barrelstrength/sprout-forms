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

    public function init()
    {
        $this->groups = new Groups();
        $this->forms = new Forms();
        $this->fields = new Fields();
        $this->entries = new Entries();
        $this->frontEndFields = new FrontEndFields();
    }

    /**
     * Return whether or not the example template already exists
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function hasExamples()
    {
        $path = Craft::$app->path->getSiteTemplatesPath().DIRECTORY_SEPARATOR.'sproutforms';

        if (file_exists($path)) {
            return true;
        }

        return false;
    }

    /**
     * Returns whether or not the templates directory is writable
     *
     * @return bool
     * @throws \yii\base\Exception
     */
    public function canCreateExamples()
    {
        return is_writable(Craft::$app->path->getSiteTemplatesPath());
    }
}
