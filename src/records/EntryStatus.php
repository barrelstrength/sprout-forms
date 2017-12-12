<?php
namespace barrelstrength\sproutforms\records;

use craft\db\ActiveRecord;

/**
 * Class EntryStatus record.
 *
 * @property int    $id    ID
 * @property string $name  Name
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
	public function getCpEditUrl()
	{
		return UrlHelper::cpUrl('sprout-forms/settings/orders-tatuses/' . $this->id);
	}

	/**
	 * @return string
	 */
	public function htmlLabel()
	{
		return sprintf('<span class="sproutFormsStatusLabel"><span class="status %s"></span> %s</span>',
			$this->color, $this->name);
	}

}