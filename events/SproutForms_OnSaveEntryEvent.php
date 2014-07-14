<?php
namespace Craft;

/**
 * On Save Entry event
 */
class SproutForms_OnSaveEntryEvent extends Event
{
	/**
	 * @var bool Whether the submission is valid.
	 */
	public $isValid = true;
}
