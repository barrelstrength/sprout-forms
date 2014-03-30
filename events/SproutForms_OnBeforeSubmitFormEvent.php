<?php
namespace Craft;

/**
 * On Before Submit Form event
 */
class SproutForms_OnBeforeSubmitFormEvent extends Event
{
	/**
	 * @var bool Whether the submission is valid.
	 */
	public $validates = true;
}
