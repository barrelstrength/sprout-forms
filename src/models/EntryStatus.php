<?php
namespace barrelstrength\sproutforms\models;

use craft\base\Model;
use craft\helpers\UrlHelper;

use barrelstrength\sproutforms\SproutForms;

class EntryStatus extends Model
{
	/**
	 * @var int|null ID
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var string
	 */
	public $handle;

	/**
	 * @var string
	 */
	public $color;

	/**
	 * @var int
	 */
	public $sortOrder;

	/**
	 * @var int
	 */
	public $isDefault;

	/**
	 * @var string
	 */
	public $dateCreated;

	/**
	 * @var string
	 */
	public $dateUpdated;

	/**
	 * @var string
	 */
	public $uid;

	/**
	 * Use the translated section name as the string representation.
	 *
	 * @return string
	 */
	function __toString()
	{
		return SproutForms::t($this->name);
	}

	/**
	 * @return string
	 */
	public function getCpEditUrl()
	{
		return UrlHelper::cpUrl('sprout-Forms/settings/orders-tatuses/' . $this->id);
	}

	/**
	 * @return string
	 */
	public function htmlLabel()
	{
		return sprintf('<span class="sproutFormsStatusLabel"><span class="status %s"></span> %s</span>',
			$this->color, $this->name);
	}
}