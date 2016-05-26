<?php
namespace Craft;

class SproutForms_EntryRecord extends BaseRecord
{
	/**
	 * Return table name
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutforms_entries';
	}

	/**
	 * Define attributes
	 *
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'statusId'  => AttributeType::Number,
			'ipAddress' => AttributeType::String,
			'userAgent' => AttributeType::Mixed,
		);
	}

	/**
	 * @return array
	 */
	public function defineRelations()
	{
		return array(
			'element' => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
			'form'    => array(static::BELONGS_TO, 'SproutForms_FormRecord', 'required' => true, 'onDelete' => static::CASCADE),
			'entryStatus' => array(static::BELONGS_TO, 'SproutForms_EntryStatusRecord', 'statusId', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}
}
