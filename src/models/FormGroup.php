<?php
namespace barrelstrength\sproutforms\models;

use craft\base\Model;

use barrelstrength\sproutforms\SproutForms;

class FormGroup extends Model
{
	/**
	 * @var int|null ID
	 */
	public $id;

	/**
	 * @var string|null Name
	 */
	public $name;

	/**
	 * Use the translated section name as the string representation.
	 *
	 * @return string
	 */
	function __toString()
	{
		return SproutForms::t($this->name);
	}
}