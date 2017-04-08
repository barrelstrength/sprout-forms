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

}