<?php

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutforms\SproutForms;
use Craft;
use craft\base\SavableComponent;

/**
 * Class ConditionalLogic
 */
abstract class ConditionalLogic extends SavableComponent implements ConditionalInterface
{
    // Traits
    // =========================================================================

    use ConditionalTrait;

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return SproutForms::$app->forms->getFormById($this->formId);
    }

    /**
     * @inheritdoc
     */
    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'rules';

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public function validateRules($fields): bool
    {
        return false;
    }
}

