<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutforms\widgets;

use barrelstrength\sproutforms\SproutForms;
use craft\base\Widget;
use Craft;

class RecentEntries extends Widget
{
    /**
     * @var int
     */
    public $formId;

    /**
     * @var int
     */
    public $limit = 10;

    /**
     * @var string
     */
    public $showDate;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-forms', 'Recent Entries (Sprout Forms)');
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        // Concat form name if the user select a specific form
        if ($this->formId !== 0 && $this->formId !== null) {
            $form = SproutForms::$app->forms->getFormById($this->formId);

            if ($form) {
                return Craft::t('sprout-forms', 'Recent {formName} Entries', [
                    'formName' => $form->name
                ]);
            }
        }

        return static::displayName();
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias('@barrelstrength/sproutforms/icon-mask.svg');
    }

    /**
     * @inheritdoc
     */
    public function getBodyHtml()
    {
//        // Get the SproutForms_Entry Element type criteria
//        $criteria = Craft::$app->elements->getCriteria('SproutForms_Entry');
//
//        if ($this->formId != 0) {
//            $criteria->formId = $this->formId;
//        }
//        $criteria->limit = $this->limit;
//
//        return Craft::$app->getView()->renderTemplate('sprout-forms/_widgets/recententries/body', [
//            'entries' => $criteria->all(),
//            'widget' => $this
//        ]);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml()
    {
        $forms = [
            0 => Craft::t('sprout-forms', 'All forms')
        ];

        $sproutForms = SproutForms::$app->forms->getAllForms();

        if ($sproutForms) {
            foreach ($sproutForms as $form) {
                $forms[$form->id] = $form->name;
            }
        }

        return Craft::$app->getView()->renderTemplate('sprout-forms/_widgets/recententries/settings', [
            'sproutForms' => $forms,
            'widget' => $this
        ]);
    }
}
