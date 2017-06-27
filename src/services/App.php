<?php
namespace barrelstrength\sproutforms\services;

use craft\base\Component;

class App extends Component
{
	public $groups;
	public $forms;
	public $fields;
	public $entries;
	public $frontEndFields;

	public function init()
	{
		$this->groups            = new Groups();
		$this->forms             = new Forms();
		$this->fields            = new Fields();
		$this->entries           = new Entries();
		$this->frontEndFields    = new FrontEndFields();
	}

	/**
	 * @param $subject
	 *
	 * @return string
	 */
	public function encodeSubjectLine($subject)
	{
		return '=?UTF-8?B?' . base64_encode($subject) . '?=';
	}
}