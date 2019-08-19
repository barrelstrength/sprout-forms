<?php

namespace barrelstrength\sproutforms\conditionallogictypes\conditions;

class IsCondition extends BaseSomethingElse
{
    public $fieldRule; // Dropdown

    public static function getLabel(): string
    {
        return 'is';
    }

    public static function getValue(): string
    {
        return 'is';
    }

    public function getValueInputHtml()
    {
        $fieldRule = new $this->fieldRule();

        return $fieldRule->getValueInputHtml();
    }
}