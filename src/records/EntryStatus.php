<?php

namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;
use craft\helpers\UrlHelper;

/**
 * Class EntryStatus record
 *
 * @property int    $id    ID
 * @property string $cpEditUrl
 * @property string $name  Name
 * @property string $color
 * @property int    $sortOrder
 * @property bool   $isDefault
 */
class EntryStatus extends ActiveRecord
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutforms_entrystatuses}}';
    }

    /**
     * @return string
     */
    public function getCpEditUrl(): string
    {
        return UrlHelper::cpUrl('sprout-forms/settings/orders-statuses/'.$this->id);
    }

    /**
     * @return string
     */
    public function htmlLabel(): string
    {
        return '<span class="sproutFormsStatusLabel"><span class="status '.$this->color.'"></span> '.$this->name.'</span>';
    }

}