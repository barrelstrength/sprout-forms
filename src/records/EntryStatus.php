<?php
namespace barrelstrength\sproutforms\records;

use Craft;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

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