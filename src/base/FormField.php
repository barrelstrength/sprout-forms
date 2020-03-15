<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\base;

use barrelstrength\sproutforms\fields\formfields\base\BaseConditionalTrait;
use craft\base\Field;

abstract class FormField extends Field
{
    use FormFieldTrait;
    use BaseConditionalTrait;
}
