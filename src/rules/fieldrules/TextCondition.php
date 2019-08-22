<?php

namespace barrelstrength\sproutforms\conditionallogictypes\fieldrules;

use barrelstrength\sproutforms\conditionallogictypes\conditions\IsCondition;

class TextCondition extends BaseSomething
{
    public function getType(): string
    {
        return 'text';
    }

    public static function getRules(): array
    {
        return [
            ['value' => IsCondition::getValue(), 'label' => IsCondition::getLabel(), 'inputMethod' => 'getTextInputHtml'],
            ['value' => 'isNot', 'label' => 'is not', 'inputMethod' => 'getTextInputHtml'],
            ['value' => 'contains', 'label' => 'contains', 'inputMethod' => 'getTextInputHtml'],
            ['value' => 'doesNotContains', 'label' => 'does not contains', 'inputMethod' => 'getTextInputHtml'],
            ['value' => 'startsWith', 'label' => 'starts with', 'inputMethod' => 'getTextInputHtml'],
            ['value' => 'doesNotStartWith', 'label' => 'does not start with', 'inputMethod' => 'getTextInputHtml'],
            ['value' => 'endsWith', 'label' => 'ends with', 'inputMethod' => 'getTextInputHtml'],
            ['value' => 'doesNotEndsWith', 'label' => 'does not ends with', 'inputMethod' => 'getTextInputHtml'],
        ];
    }

    protected function getTextInputHtml($data)
    {
        return '<input class="text fullwidth" type="text" name="settings[barrelstrength\sproutforms\conditionallogictypes\FieldRule][conditionalRules][1][rules][0][2]" value="">';
    }
}