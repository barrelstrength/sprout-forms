<?php

namespace barrelstrength\sproutforms\rules\conditions;

use barrelstrength\sproutforms\base\BaseCondition;

class DoesNotStartWithCondition extends BaseCondition
{
    public function getLabel(): string
    {
        return 'does not starts with';
    }

	/**
	 * @inheritDoc
	 */
	public function getValue(): string
	{
		return self::class;
	}

	public function getValueInputHtml($name, $value): string
	{
		$html = '<input class="text fullwidth" type="text" name="'.$name.'" value="'.$value.'">';

		return $html;
	}
}