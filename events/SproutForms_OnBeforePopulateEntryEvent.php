<?php
namespace Craft;

/**
 * On Before Populate Entry event
 */
class SproutForms_OnBeforePopulateEntryEvent extends Event
{
	/**
	 * @var bool Whether the submission is valid.
	 */
	public $isValid = true;
	public $fakeIt = false;
}