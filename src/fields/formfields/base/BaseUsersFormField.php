<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutforms\fields\formfields\base;

use barrelstrength\sproutforms\base\FormFieldTrait;
use craft\fields\Users as CraftUsersField;

abstract class BaseUsersFormField extends CraftUsersField
{
    use FormFieldTrait;

    /**
     * @var string Template to use for settings rendering
     */
    protected $settingsTemplate = 'sprout-forms/_components/fields/formfields/elementfieldsettings';
}
