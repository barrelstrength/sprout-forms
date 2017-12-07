<?php
namespace barrelstrength\sproutforms\services;

use craft\base\Component;
use Craft;

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

	/**
	 * Return wether or not the example template already exist
	 *
	 * @return bool
	 */
	public function hasExamples()
	{
		$path = Craft::$app->path->getSiteTemplatesPath() . 'sproutforms';

		if (file_exists($path))
		{
			return true;
		}

		return false;
	}

	/**
	 * Returns whether or not the templates directory is writable
	 *
	 * @return bool
	 */
	public function canCreateExamples()
	{
		return is_writable(Craft::$app->path->getSiteTemplatesPath());
	}
}