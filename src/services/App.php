<?php
namespace barrelstrength\sproutforms\services;

use craft\base\Component;
use barrelstrength\sproutcore\services\sproutfields\Email;
use barrelstrength\sproutcore\services\sproutfields\EmailSelect;
use barrelstrength\sproutcore\services\sproutfields\Utilities;
use barrelstrength\sproutcore\services\sproutfields\Link;
use barrelstrength\sproutcore\services\sproutfields\Phone;

class App extends Component
{
	public $groups;
	public $forms;
	public $fields;
	public $entries;
	public $frontEndFields;
	//Fields
	public $email;
	public $utilities;
	public $emailSelect;
	public $link;
	public $phone;

	public function init()
	{
		$this->groups         = new Groups();
		$this->forms          = new Forms();
		$this->fields         = new Fields();
		$this->entries        = new Entries();
		$this->email          = new Email();
		$this->utilities      = new Utilities();
		$this->frontEndFields = new FrontEndFields();
		$this->emailSelect    = new EmailSelect();
		$this->link           = new Link();
		$this->phone          = new Phone();
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