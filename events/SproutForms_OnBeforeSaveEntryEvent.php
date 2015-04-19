<?php
namespace Craft;

/**
 * On Before Save Entry event
 */
class SproutForms_OnBeforeSaveEntryEvent extends Event
{
	/**
	 * @var bool Whether the submission is valid.
	 */
	public $isValid = true;
	public $fakeIt = false;
}