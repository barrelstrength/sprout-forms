<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields\formfields\base;

use barrelstrength\sproutforms\base\FormFieldTrait;
use craft\fields\Assets as CraftAssetsField;

abstract class BaseAssetsFormField extends CraftAssetsField
{
    use FormFieldTrait;
}
