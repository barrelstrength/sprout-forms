<?php
namespace barrelstrength\sproutforms\records;

use Craft;
use craft\db\ActiveRecord;
use yii\db\ActiveQueryInterface;

/**
 * Class FormGroup record.
 *
 * @property int    $id    ID
 * @property string $name  Name
 */
class FormGroup extends ActiveRecord
{
	/**
	 * @inheritdoc
	 *
	 * @return string
	 */
	public static function tableName(): string
	{
		return '{{%sproutforms_formgroups}}';
	}

}