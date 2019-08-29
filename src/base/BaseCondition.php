<?php

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\SavableComponent;

/**
 * Class BaseCondition
 */
abstract class BaseCondition extends SavableComponent implements ConditionInterface
{
	/** @var FormField */
    public $formField;

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return "";
    }

	/**
	 * @inheritDoc
	 */
	public function getValue(): string
	{
		return "";
	}

	/**
	 * @inheritDoc
	 */
	public function getValueInputHtml($name, $value): string
	{
		$html = $this->formField->getValueConditionHtml($this, $name, $value);

		return $html;
	}
}
