<?php

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\SavableComponent;

/**
 * Class ConditionalType
 */
abstract class ConditionalType extends SavableComponent implements ConditionalTypeInterface
{
	public $formField;

	/**
	 * @inheritDoc
	 */
	public function getRules(): array
	{
		return [];
	}

	public function getRulesAsOptions(): array
	{
		/** @var BaseCondition[] $rules */
		$rules = $this->getRules();
		$options = [];

		if ($rules){
			foreach ($rules as $rule){
				$options[] = [
					'label' => $rule->getLabel(),
					'value' => get_class($rule)
				];
			}
		}

		return $options;
	}

	public function getType(): string
	{
		return "";
	}
}
