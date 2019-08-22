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
    public $formField;

    /**
     * @inheritDoc
     */
    public static function getRules(): array
    {
        return [];
    }

    public function setFormField(FormField $formField)
    {
        $this->formField = $formField;
    }
}
