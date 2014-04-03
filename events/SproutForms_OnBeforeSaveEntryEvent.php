<?php
namespace Craft;

/**
 * On Before Save Entry Form event
 */
class SproutForms_OnBeforeSaveEntryEvent extends Event
{
	/**
	 * @var bool Whether the submission is valid.
	 */
	public $isValid = true;
}
