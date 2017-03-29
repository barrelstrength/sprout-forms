<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use craft\base\Component;
use barrelstrength\sproutforms\SproutForms;

class App extends Component
{
	public $groups;
	public $forms;
	public $fields;
	public $entries;
	public $frontEndFields;

	public function init()
	{
		$this->groups         = new Groups();
		$this->forms          = new Forms();
		$this->fields         = new Fields();
		$this->entries        = new Entries();
		$this->frontEndFields = new FrontEndFields();
	}
}