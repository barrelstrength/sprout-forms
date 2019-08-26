<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;
use barrelstrength\sproutforms\fields\formfields\BaseOptionsFormField;

class IsNotCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'is not';
    }

	/**
	 * @inheritDoc
	 */
	public function getValue(): string
	{
		return self::class;
	}

	public function getValueInputHtml($name , $value): string
	{
		$html = '<input class="text fullwidth" type="text" name="'.$name.'" value="'.$value.'">';

		if ($this->formField instanceof BaseOptionsFormField){
			$html = "Is Dropdown";
		}

		return $html;
	}
}