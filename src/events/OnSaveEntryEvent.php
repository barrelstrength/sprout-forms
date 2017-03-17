<?php
namespace barrelstrength\sproutforms\events;

use Craft;
use yii\base\Event;

/**
 * OnSaveEntryEvent class.
 */
class OnSaveEntryEvent extends Event
{
	// Properties
	// =========================================================================

	/**
	 * @var ElementEntry
	 */
	public $entry   = null;

	public $isNewEntry = true;
}
