<?php
namespace barrelstrength\sproutforms\events;

use Craft;
use yii\base\Event;

/**
 * OnBeforeSaveEntryEvent class.
 */
class OnBeforeSaveEntryEvent extends Event
{
	// Properties
	// =========================================================================

	/**
	 * @var ElementEntry
	 */
	public $entry   = null;

	public $isValid = true;
	public $fakeIt  = false;
}
