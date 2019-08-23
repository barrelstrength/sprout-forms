<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;
use barrelstrength\sproutforms\base\FormField;
use barrelstrength\sproutforms\fields\formfields\BaseOptionsFormField;

class IsCondition extends BaseCondition
{
	/** @var FormField */
    public $fieldRule;

    public function getLabel(): string
    {
        return 'is';
    }

	public function getTextInputHtml($name): string
	{
		$html = '<input class="text fullwidth" type="text" name="'.$name.'" value="">';

		if ($this->fieldRule instanceof BaseOptionsFormField){
			$html = "Is Dropdown";
		}

		return $html;
	}
}