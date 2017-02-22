<?php
namespace barrelstrength\sproutforms\services;

use Craft;
use craft\base\Component;
use barrelstrength\sproutforms\SproutForms;

class Api extends Component
{
	public $groups;
	public $forms;
	public $fields;

	public function init()
	{
		$this->groups = new Groups();
		$this->forms = new Forms();
		$this->fields = new Fields();
	}
}